<?php
// Enable error reporting at the top
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/tmp/php-error.log');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Global error handler
if (!function_exists('handleError')) {
    function handleError($errno, $errstr, $errfile, $errline)
    {
        error_log("PHP Error [$errno]: $errstr in $errfile on line $errline");
        if (error_reporting() & $errno) {
            // Clear any output that might have been sent
            if (ob_get_length())
                ob_clean();

            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'error' => 'Internal server error occurred',
                'debug' => [
                    'message' => $errstr,
                    'file' => basename($errfile),
                    'line' => $errline
                ]
            ]);
            exit(1);
        }
    }
    set_error_handler('handleError');
}

require_once(dirname(__FILE__) . '/DBConnection.php');

class Master extends DBConnection
{
    private $settings;

    public function __construct()
    {
        try {
            parent::__construct();
            global $_settings;
            if (!isset($_settings)) {
                require_once(dirname(__FILE__) . '/SystemSettings.php');
            }
            $this->settings = $_settings;

            // Verify database connection
            if (!$this->isConnected()) {
                error_log("Database connection lost during Master class initialization");
                $this->reconnect();
            }
        } catch (Exception $e) {
            error_log("Error in Master class initialization: " . $e->getMessage());
            throw new Exception("System initialization failed. Please try again later.");
        }
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    public function create_order_core($args)
    {
        try {
            // Validate required fields
            $client_id = $args['client_id'] ?? null;
            $amount = $args['amount'] ?? null;
            $payment_method = $args['payment_method'] ?? null;
            $paid = $args['paid'] ?? null;
            $order_type = $args['order_type'] ?? null;
            $delivery_address = $args['delivery_address'] ?? '';
            $momo_number = $args['momo_number'] ?? '';

            if (!$client_id || !$amount || !$payment_method || $paid === null || !$order_type) {
                throw new Exception("Missing required fields");
            }

            // Start transaction
            if (!$this->conn->begin_transaction()) {
                throw new Exception("Failed to start transaction");
            }

            // Create order record
            $stmt = $this->conn->prepare("INSERT INTO orders (client_id, delivery_address, payment_method, amount, paid, order_type, status) VALUES (?, ?, ?, ?, ?, ?, 0)");
            if (!$stmt) {
                throw new Exception("Failed to prepare order statement: " . $this->conn->error);
            }
            if (!$stmt->bind_param("issdis", $client_id, $delivery_address, $payment_method, $amount, $paid, $order_type)) {
                throw new Exception("Failed to bind order parameters: " . $stmt->error);
            }
            if (!$stmt->execute()) {
                throw new Exception("Failed to create order: " . $stmt->error);
            }
            $order_id = $this->conn->insert_id;

            // Get cart items
            $cart_items = $this->conn->query("SELECT c.*, i.price, i.product_id FROM cart c INNER JOIN inventory i ON i.id = c.inventory_id WHERE c.client_id = " . intval($client_id));
            if (!$cart_items) {
                throw new Exception("Failed to retrieve cart items: " . $this->conn->error);
            }
            if ($cart_items->num_rows === 0) {
                throw new Exception("Your cart is empty");
            }

            // Insert order items and update inventory
            while ($item = $cart_items->fetch_assoc()) {
                $total = $item['quantity'] * $item['price'];
                $inventory_check = $this->conn->query("SELECT quantity FROM inventory WHERE id = " . intval($item['inventory_id']));
                if (!$inventory_check || $inventory_check->num_rows === 0) {
                    throw new Exception("Product not found in inventory");
                }
                $current_stock = $inventory_check->fetch_assoc()['quantity'];
                if ($current_stock < $item['quantity']) {
                    throw new Exception("Not enough stock available for " . $item['name']);
                }
                $stmt = $this->conn->prepare("INSERT INTO order_list (order_id, product_id, quantity, price, total) VALUES (?, ?, ?, ?, ?)");
                if (!$stmt) {
                    throw new Exception("Failed to prepare order item statement: " . $this->conn->error);
                }
                if (!$stmt->bind_param("iiidd", $order_id, $item['product_id'], $item['quantity'], $item['price'], $total)) {
                    throw new Exception("Failed to bind order item parameters: " . $stmt->error);
                }
                if (!$stmt->execute()) {
                    throw new Exception("Failed to create order item: " . $stmt->error);
                }
                $stmt = $this->conn->prepare("UPDATE inventory SET quantity = quantity - ? WHERE id = ? AND quantity >= ?");
                if (!$stmt->bind_param("iii", $item['quantity'], $item['inventory_id'], $item['quantity'])) {
                    throw new Exception("Failed to bind inventory update parameters: " . $stmt->error);
                }
                if (!$stmt->execute()) {
                    throw new Exception("Failed to update inventory");
                }
            }

            // Handle MOMO payment
            if ($payment_method === "MoMoPay" || $payment_method === "momo") {
                require_once(__DIR__ . '/PaypackHandler.php');
                $paypack = new PaypackHandler();
                // Initiate payment
                $momoResult = $paypack->cashin($amount, $momo_number);
                if (!$momoResult['success']) {
                    throw new Exception("MTN Payment failed to initiate: " . $momoResult['error']);
                }
                $paypack_ref = $momoResult['ref'];
                // Save paypack_ref in the orders table
                $stmt = $this->conn->prepare("UPDATE orders SET paypack_ref = ? WHERE id = ?");
                $stmt->bind_param("si", $paypack_ref, $order_id);
                $stmt->execute();
                // Do not poll here; let frontend poll and call a new endpoint to check status
            }

            // Clear cart
            $this->conn->query("DELETE FROM cart WHERE client_id = {$client_id}");

            // Commit transaction
            if (!$this->conn->commit()) {
                throw new Exception("Failed to commit transaction");
            }

            // Send payment confirmation notifications (optional, can be called outside)
            try {
                $this->sendPaymentNotifications($order_id, $client_id, $amount, $payment_method);
            } catch (Exception $notificationError) {
                error_log("Payment notification error: " . $notificationError->getMessage());
            }

            return [
                'status' => 'success',
                'order_id' => $order_id,
                'message' => 'Order placed successfully! Payment confirmation sent to your email and phone.'
            ];
        } catch (Throwable $e) {
            error_log('Order Exception: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine() . "\nStack trace:\n" . $e->getTraceAsString());
            try {
                if ($this->conn && $this->conn->connect_errno === 0) {
                    $this->conn->rollback();
                }
            } catch (Exception $rollbackError) {
            }
            return [
                'status' => 'failed',
                'msg' => 'An error occurred while processing your order. Please try again.',
                'error' => $e->getMessage(), // Expose real error for debugging
                'trace' => $e->getTraceAsString() // Optionally include stack trace
            ];
        }
    }

    function place_order()
    {
        try {
            // Clear any previous output and start fresh
            while (ob_get_level()) {
                ob_end_clean();
            }
            ob_start();

            // Set proper headers
            header('Content-Type: application/json');
            header('Cache-Control: no-cache, must-revalidate');

            // Check database connection first
            if (!$this->isConnected()) {
                if (!$this->reconnect()) {
                    throw new Exception("Database connection failed. Please try again.");
                }
            }

            // Validate session
            if (!isset($_SESSION)) {
                throw new Exception("Session not started");
            }

            if (!isset($_SESSION['userdata']['id'])) {
                throw new Exception("User not logged in");
            }

            // Call the core order creation method
            $order_result = $this->create_order_core([
                'client_id' => $_SESSION['userdata']['id'],
                'amount' => floatval($_POST['amount']),
                'payment_method' => $_POST['payment_method'],
                'paid' => intval($_POST['paid']),
                'order_type' => intval($_POST['order_type']),
                'delivery_address' => $_POST['delivery_address'] ?? '',
                'momo_number' => $_POST['momo_number'] ?? ''
            ]);

            // Handle the result from create_order_core
            if ($order_result['status'] === 'success') {
                // Clear any buffered output
                if (ob_get_length())
                    ob_clean();

                // Send success response
                echo json_encode($order_result);
                exit;
            } else {
                // Clear any buffered output
                if (ob_get_length())
                    ob_clean();

                // Send error response
                echo json_encode($order_result);
                exit;
            }

        } catch (Throwable $e) {

            // Rollback transaction on error
            try {
                if ($this->conn && $this->conn->connect_errno === 0) {
                    $this->conn->rollback();
                }
            } catch (Exception $rollbackError) {
                // Log rollback failure but continue
            }

            // Clear any buffered output
            if (ob_get_length())
                ob_clean();

            // Send error response
            echo json_encode([
                'status' => 'failed',
                'msg' => 'An error occurred while processing your order. Please try again.'
            ]);

            // Ensure output buffer is cleaned
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            exit;
        }
    }

    function login()
    {
        try {
            if (!isset($_POST['email']) || !isset($_POST['password'])) {
                throw new Exception("Email and password are required");
            }

            $email = $_POST['email'];
            $password = md5($_POST['password']);

            $stmt = $this->conn->prepare("SELECT * FROM clients WHERE email = ? AND password = ?");
            $stmt->bind_param("ss", $email, $password);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $user_data = $result->fetch_assoc();
                $_SESSION['auth_user'] = $user_data;
                $_SESSION['userdata'] = $user_data;  // Also store in userdata for system compatibility

                // Load system settings
                $settings = new SystemSettings();
                $settings->load_system_info();

                error_log("User data in session: " . print_r($_SESSION, true));

                return json_encode([
                    'status' => 'success',
                    'msg' => 'Login successful'
                ]);
            } else {
                throw new Exception("Invalid email or password");
            }
        } catch (Exception $e) {
            return json_encode([
                'status' => 'failed',
                'msg' => $e->getMessage()
            ]);
        }
    }

    function register()
    {
        try {
            // Validate required fields
            if (
                !isset($_POST['firstname']) || !isset($_POST['lastname']) ||
                !isset($_POST['email']) || !isset($_POST['password']) ||
                !isset($_POST['contact']) || !isset($_POST['gender'])
            ) {
                throw new Exception("All required fields must be filled");
            }

            $firstname = trim($_POST['firstname']);
            $lastname = trim($_POST['lastname']);
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            $contact = trim($_POST['contact']);
            $gender = $_POST['gender'];
            $default_delivery_address = isset($_POST['default_delivery_address']) ? trim($_POST['default_delivery_address']) : '';

            // Validate email format
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Invalid email format");
            }

            // Validate password length
            if (strlen($password) < 6) {
                throw new Exception("Password must be at least 6 characters long");
            }

            // Check if email already exists
            $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM clients WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $count = $result->fetch_assoc()['count'];
            $stmt->close();

            if ($count > 0) {
                throw new Exception("Email already exists. Please use a different email address.");
            }

            // Hash password
            $hashed_password = md5($password);

            // Insert new user
            $stmt = $this->conn->prepare("INSERT INTO clients (firstname, lastname, email, password, contact, gender, default_delivery_address, date_created) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("sssssss", $firstname, $lastname, $email, $hashed_password, $contact, $gender, $default_delivery_address);

            if (!$stmt->execute()) {
                throw new Exception("Failed to create account. Please try again.");
            }

            $user_id = $this->conn->insert_id;
            $stmt->close();

            // Send welcome email notification
            try {
                // Load NotificationManager
                if (!class_exists('NotificationManager')) {
                    require_once(__DIR__ . '/NotificationManager.php');
                }

                $notificationManager = new NotificationManager();

                // Prepare user data for welcome email
                $userData = [
                    'firstname' => $firstname,
                    'lastname' => $lastname,
                    'email' => $email,
                    'contact' => $contact
                ];

                // Send welcome email using NotificationManager
                $emailResult = $notificationManager->sendWelcomeEmail($userData);

                // Log only failures for monitoring
                if (!$emailResult['success']) {
                    error_log("Welcome email failed for: " . $email . " - " . $emailResult['message']);
                }

            } catch (Exception $emailError) {
                // Don't fail registration if email fails
                // Log error for monitoring
                error_log("Welcome email error: " . $emailError->getMessage());
            }

            return json_encode([
                'status' => 'success',
                'msg' => 'Account successfully created! Welcome email has been sent.',
                'user_id' => $user_id
            ]);

        } catch (Exception $e) {
            return json_encode([
                'status' => 'failed',
                'msg' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send payment confirmation notifications
     * 
     * @param int $order_id Order ID
     * @param int $client_id Client ID
     * @param float $amount Payment amount
     * @param string $payment_method Payment method
     * @return void
     */
    private function sendPaymentNotifications($order_id, $client_id, $amount, $payment_method)
    {
        try {
            // Load NotificationManager
            if (!class_exists('NotificationManager')) {
                require_once(__DIR__ . '/NotificationManager.php');
            }

            $notificationManager = new NotificationManager();

            // Get user details from database
            $stmt = $this->conn->prepare("SELECT firstname, lastname, email, contact FROM clients WHERE id = ?");
            $stmt->bind_param("i", $client_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();

            if (!$user) {
                throw new Exception("User not found for payment notification");
            }

            // Prepare payment data for notifications
            $paymentData = [
                'user_email' => $user['email'],
                'user_phone' => $user['contact'],
                'user_firstname' => $user['firstname'],
                'user_lastname' => $user['lastname'],
                'amount' => $amount,
                'currency' => 'RWF',
                'order_id' => $order_id,
                'payment_method' => $payment_method
            ];

            // Send payment confirmation notifications
            $result = $notificationManager->sendPaymentConfirmation($paymentData);

            // Log only failures for monitoring
            if (!$result['success']) {
                error_log("Payment notifications failed for order #{$order_id}: " . $result['message']);
            }

        } catch (Exception $e) {
            // Log error for monitoring
            error_log("Payment notification error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Send order status update notifications
     * 
     * @param int $order_id Order ID
     * @param int $status Status code (0=Pending, 1=Packed, 2=Out for Delivery, 3=Picked Up, 4=Delivered, 5=Cancelled)
     * @return void
     */
    private function sendStatusUpdateNotifications($order_id, $status)
    {
        try {
            // Load NotificationManager
            if (!class_exists('NotificationManager')) {
                require_once(__DIR__ . '/NotificationManager.php');
            }

            $notificationManager = new NotificationManager();

            // Get order and user details from database
            $stmt = $this->conn->prepare("
                SELECT o.*, c.firstname, c.lastname, c.email, c.contact 
                FROM orders o 
                INNER JOIN clients c ON c.id = o.client_id 
                WHERE o.id = ?
            ");
            $stmt->bind_param("i", $order_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $order = $result->fetch_assoc();
            $stmt->close();

            if (!$order) {
                throw new Exception("Order not found for status update notification");
            }

            // Map status codes to status names
            $statusNames = [
                0 => 'Pending',
                1 => 'Packed',
                2 => 'Out for Delivery',
                3 => 'Picked Up',
                4 => 'Delivered',
                5 => 'Cancelled'
            ];

            $statusName = $statusNames[$status] ?? 'Unknown';

            // Prepare order data for notifications
            $orderData = [
                'user_email' => $order['email'],
                'user_phone' => $order['contact'],
                'user_firstname' => $order['firstname'],
                'user_lastname' => $order['lastname'],
                'order_id' => $order_id,
                'new_status' => $statusName
            ];

            // Send order status update notifications
            $result = $notificationManager->sendOrderStatusUpdate($orderData);

            // Log only failures for monitoring
            if (!$result['success']) {
                error_log("Order status update notifications failed for order #{$order_id}: " . $result['message']);
            }

        } catch (Exception $e) {
            // Log error for monitoring
            error_log("Order status update notification error: " . $e->getMessage());
            throw $e;
        }
    }

    function update_account()
    {
        try {
            error_log("Session data in update_account: " . print_r($_SESSION, true));
            error_log("POST data in update_account: " . print_r($_POST, true));

            // Check both auth_user and userdata for compatibility
            if (
                (!isset($_SESSION['auth_user']) || !isset($_SESSION['auth_user']['id'])) &&
                (!isset($_SESSION['userdata']) || !isset($_SESSION['userdata']['id']))
            ) {
                throw new Exception("User not logged in");
            }

            // Ensure we have a valid user ID from either session variable
            $session_user_id = $_SESSION['auth_user']['id'] ?? $_SESSION['userdata']['id'] ?? null;

            if (!isset($_POST['id']) || !isset($_POST['email'])) {
                throw new Exception("Required fields are missing");
            }

            $id = $_POST['id'];
            $email = $_POST['email'];
            $firstname = $_POST['firstname'] ?? '';
            $lastname = $_POST['lastname'] ?? '';
            $gender = $_POST['gender'] ?? '';
            $contact = $_POST['contact'] ?? '';
            $default_delivery_address = $_POST['default_delivery_address'] ?? '';

            if ($session_user_id != $id) {
                throw new Exception("Unauthorized access");
            }

            $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM clients WHERE email = ? AND id != ?");
            $stmt->bind_param("si", $email, $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $count = $result->fetch_assoc()['count'];
            $stmt->close();

            if ($count > 0) {
                throw new Exception("Email already exists");
            }

            $update_password = false;
            $new_password = '';

            if (!empty($_POST['password'])) {
                if (empty($_POST['cpassword'])) {
                    throw new Exception("Please provide current password");
                }

                $stmt = $this->conn->prepare("SELECT password FROM clients WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $current_pwd = $stmt->get_result()->fetch_assoc()['password'];
                $stmt->close();

                if (md5($_POST['cpassword']) != $current_pwd) {
                    throw new Exception("Current Password is incorrect");
                }

                $update_password = true;
                $new_password = md5($_POST['password']);
            }

            if ($update_password) {
                $sql = "UPDATE clients SET firstname=?, lastname=?, gender=?, contact=?, email=?, default_delivery_address=?, password=? WHERE id=?";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("sssssssi", $firstname, $lastname, $gender, $contact, $email, $default_delivery_address, $new_password, $id);
            } else {
                $sql = "UPDATE clients SET firstname=?, lastname=?, gender=?, contact=?, email=?, default_delivery_address=? WHERE id=?";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("ssssssi", $firstname, $lastname, $gender, $contact, $email, $default_delivery_address, $id);
            }

            $save = $stmt->execute();
            $stmt->close();

            if ($save) {
                // Get fresh data from database
                $stmt = $this->conn->prepare("SELECT * FROM clients WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                $user_data = $result->fetch_assoc();
                $stmt->close();

                // Update both session variables with fresh data
                $_SESSION['auth_user'] = $user_data;
                $_SESSION['userdata'] = $user_data;

                // Update system settings
                require_once('SystemSettings.php');
                $sys_settings = new SystemSettings();
                $sys_settings->load_system_info();

                return json_encode([
                    'status' => 'success',
                    'msg' => 'User details successfully updated'
                ]);
            } else {
                throw new Exception($this->conn->error);
            }
        } catch (Exception $e) {
            return json_encode([
                'status' => 'failed',
                'msg' => $e->getMessage()
            ]);
        }
    }

    function save_category()
    {
        try {
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            $category = isset($_POST['category']) ? trim($_POST['category']) : '';
            $description = isset($_POST['description']) ? trim($_POST['description']) : '';
            $status = isset($_POST['status']) ? intval($_POST['status']) : 1;
            if (!$category)
                throw new Exception("Category name is required");
            if ($id > 0) {
                $stmt = $this->conn->prepare("UPDATE categories SET category=?, description=?, status=? WHERE id=?");
                $stmt->bind_param("ssii", $category, $description, $status, $id);
                $save = $stmt->execute();
                $stmt->close();
            } else {
                $stmt = $this->conn->prepare("INSERT INTO categories (category, description, status) VALUES (?, ?, ?)");
                $stmt->bind_param("ssi", $category, $description, $status);
                $save = $stmt->execute();
                $stmt->close();
            }
            if ($save) {
                return json_encode([
                    'status' => 'success',
                    'msg' => 'Category saved successfully'
                ]);
            } else {
                throw new Exception($this->conn->error);
            }
        } catch (Exception $e) {
            return json_encode([
                'status' => 'failed',
                'msg' => $e->getMessage()
            ]);
        }
    }

    function delete_category()
    {
        try {
            if (!isset($_POST['id'])) {
                throw new Exception("Missing category ID");
            }
            $id = intval($_POST['id']);
            $stmt = $this->conn->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->bind_param("i", $id);
            $save = $stmt->execute();
            $stmt->close();
            if ($save) {
                return json_encode([
                    'status' => 'success',
                    'msg' => 'Category deleted successfully'
                ]);
            } else {
                throw new Exception($this->conn->error);
            }
        } catch (Exception $e) {
            return json_encode([
                'status' => 'failed',
                'msg' => $e->getMessage()
            ]);
        }
    }

    function delete_brand()
    {
        try {
            if (!isset($_POST['id'])) {
                throw new Exception("Missing brand ID");
            }
            $id = intval($_POST['id']);
            $stmt = $this->conn->prepare("DELETE FROM brands WHERE id = ?");
            $stmt->bind_param("i", $id);
            $save = $stmt->execute();
            $stmt->close();
            if ($save) {
                return json_encode([
                    'status' => 'success',
                    'msg' => 'Brand deleted successfully'
                ]);
            } else {
                throw new Exception($this->conn->error);
            }
        } catch (Exception $e) {
            return json_encode([
                'status' => 'failed',
                'msg' => $e->getMessage()
            ]);
        }
    }

    function save_brand()
    {
        try {
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            $name = isset($_POST['name']) ? trim($_POST['name']) : '';
            $description = isset($_POST['description']) ? trim($_POST['description']) : '';
            $status = isset($_POST['status']) ? intval($_POST['status']) : 1;
            if (!$name)
                throw new Exception("Brand name is required");
            if ($id > 0) {
                $stmt = $this->conn->prepare("UPDATE brands SET name=?, description=?, status=? WHERE id=?");
                $stmt->bind_param("ssii", $name, $description, $status, $id);
                $save = $stmt->execute();
                $stmt->close();
            } else {
                $stmt = $this->conn->prepare("INSERT INTO brands (name, description, status) VALUES (?, ?, ?)");
                $stmt->bind_param("ssi", $name, $description, $status);
                $save = $stmt->execute();
                $stmt->close();
            }
            if ($save) {
                return json_encode([
                    'status' => 'success',
                    'msg' => 'Brand saved successfully'
                ]);
            } else {
                throw new Exception($this->conn->error);
            }
        } catch (Exception $e) {
            return json_encode([
                'status' => 'failed',
                'msg' => $e->getMessage()
            ]);
        }
    }

    function delete_inventory()
    {
        try {
            if (!isset($_POST['id'])) {
                throw new Exception("Missing inventory ID");
            }
            $id = intval($_POST['id']);
            $stmt = $this->conn->prepare("DELETE FROM inventory WHERE id = ?");
            $stmt->bind_param("i", $id);
            $save = $stmt->execute();
            $stmt->close();
            if ($save) {
                return json_encode([
                    'status' => 'success',
                    'msg' => 'Inventory deleted successfully'
                ]);
            } else {
                throw new Exception($this->conn->error);
            }
        } catch (Exception $e) {
            return json_encode([
                'status' => 'failed',
                'msg' => $e->getMessage()
            ]);
        }
    }

    function save_inventory()
    {
        try {
            if (!isset($_POST['product_id']) || !isset($_POST['price']) || !isset($_POST['quantity'])) {
                throw new Exception("Missing required fields");
            }
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            $product_id = intval($_POST['product_id']);
            $price = floatval($_POST['price']);
            $quantity = intval($_POST['quantity']);
            if ($id > 0) {
                $stmt = $this->conn->prepare("UPDATE inventory SET product_id=?, price=?, quantity=? WHERE id=?");
                $stmt->bind_param("idii", $product_id, $price, $quantity, $id);
                $save = $stmt->execute();
                $stmt->close();
            } else {
                $stmt = $this->conn->prepare("INSERT INTO inventory (product_id, price, quantity) VALUES (?, ?, ?)");
                $stmt->bind_param("idi", $product_id, $price, $quantity);
                $save = $stmt->execute();
                $stmt->close();
            }
            if ($save) {
                return json_encode([
                    'status' => 'success',
                    'msg' => 'Inventory saved successfully'
                ]);
            } else {
                throw new Exception($this->conn->error);
            }
        } catch (Exception $e) {
            return json_encode([
                'status' => 'failed',
                'msg' => $e->getMessage()
            ]);
        }
    }

    function delete_product()
    {
        try {
            if (!isset($_POST['id'])) {
                throw new Exception("Missing product ID");
            }
            $id = intval($_POST['id']);
            // Delete inventory
            $stmt = $this->conn->prepare("DELETE FROM inventory WHERE product_id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            // Delete product
            $stmt = $this->conn->prepare("DELETE FROM products WHERE id = ?");
            $stmt->bind_param("i", $id);
            $save = $stmt->execute();
            $stmt->close();
            // Delete images
            $upload_path = dirname(__DIR__) . '/uploads/product_' . $id . '/';
            if (is_dir($upload_path)) {
                $files = scandir($upload_path);
                foreach ($files as $file) {
                    if ($file !== '.' && $file !== '..') {
                        @unlink($upload_path . $file);
                    }
                }
                @rmdir($upload_path);
            }
            if ($save) {
                return json_encode([
                    'status' => 'success',
                    'msg' => 'Product deleted successfully'
                ]);
            } else {
                throw new Exception($this->conn->error);
            }
        } catch (Exception $e) {
            return json_encode([
                'status' => 'failed',
                'msg' => $e->getMessage()
            ]);
        }
    }

    function save_product()
    {
        try {
            // Validate required fields
            if (!isset($_POST['name']) || !isset($_POST['brand_id']) || !isset($_POST['category_id'])) {
                throw new Exception("Missing required fields");
            }
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            $name = trim($_POST['name']);
            $brand_id = intval($_POST['brand_id']);
            $category_id = intval($_POST['category_id']);
            $specs = isset($_POST['specs']) ? trim($_POST['specs']) : '';
            $status = isset($_POST['status']) ? intval($_POST['status']) : 1;
            $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
            $quantity = isset($_POST['quantity']) ? floatval($_POST['quantity']) : 0;
            $sub_category_id = isset($_POST['sub_category_id']) ? intval($_POST['sub_category_id']) : null;

            // If updating
            if ($id > 0) {
                $stmt = $this->conn->prepare("UPDATE products SET name=?, brand_id=?, category_id=?, sub_category_id=?, specs=?, status=? WHERE id=?");
                $stmt->bind_param("siiisii", $name, $brand_id, $category_id, $sub_category_id, $specs, $status, $id);
                $save = $stmt->execute();
                $stmt->close();
                // Update price in inventory
                $stmt = $this->conn->prepare("UPDATE inventory SET price=? WHERE product_id=?");
                $stmt->bind_param("di", $price, $id);
                $stmt->execute();
                $stmt->close();
            } else {
                // Insert new product
                $stmt = $this->conn->prepare("INSERT INTO products (name, brand_id, category_id, sub_category_id, specs, status) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("siiisi", $name, $brand_id, $category_id, $sub_category_id, $specs, $status);
                $save = $stmt->execute();
                $new_id = $this->conn->insert_id;
                $stmt->close();
                // Insert inventory
                $stmt = $this->conn->prepare("INSERT INTO inventory (product_id, price, quantity) VALUES (?, ?, ?)");
                $stmt->bind_param("idd", $new_id, $price, $quantity);
                $stmt->execute();
                $stmt->close();
                $id = $new_id;
            }

            // Handle image uploads
            if (isset($_FILES['img']) && count($_FILES['img']['name']) > 0) {
                $upload_path = dirname(__DIR__) . '/uploads/product_' . $id . '/';
                if (!is_dir($upload_path)) {
                    mkdir($upload_path, 0777, true);
                }
                foreach ($_FILES['img']['tmp_name'] as $k => $tmp_name) {
                    if (!empty($tmp_name)) {
                        $filename = basename($_FILES['img']['name'][$k]);
                        $target = $upload_path . $filename;
                        move_uploaded_file($tmp_name, $target);
                    }
                }
            }

            if ($save) {
                return json_encode([
                    'status' => 'success',
                    'msg' => 'Product saved successfully'
                ]);
            } else {
                throw new Exception($this->conn->error);
            }
        } catch (Exception $e) {
            return json_encode([
                'status' => 'failed',
                'msg' => $e->getMessage()
            ]);
        }
    }

    function update_order_status()
    {
        try {
            if (!isset($_POST['id']) || !isset($_POST['status'])) {
                throw new Exception("Missing required fields");
            }
            $id = intval($_POST['id']);
            $status = intval($_POST['status']);
            $stmt = $this->conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->bind_param("ii", $status, $id);
            $save = $stmt->execute();
            $stmt->close();
            if ($save) {
                // Send status update notifications
                try {
                    $this->sendStatusUpdateNotifications($id, $status);
                } catch (Exception $notificationError) {
                    // Don't fail the status update if notifications fail
                    error_log("Status update notification error: " . $notificationError->getMessage());
                }

                return json_encode([
                    'status' => 'success',
                    'msg' => 'Order status updated successfully! Customer has been notified.'
                ]);
            } else {
                throw new Exception($this->conn->error);
            }
        } catch (Exception $e) {
            return json_encode([
                'status' => 'failed',
                'msg' => $e->getMessage()
            ]);
        }
    }

    function update_cart_qty()
    {
        try {
            if (!isset($_POST['id']) || !isset($_POST['quantity'])) {
                throw new Exception("Missing required fields");
            }
            $id = intval($_POST['id']);
            $quantity = intval($_POST['quantity']);
            if ($quantity < 1) {
                throw new Exception("Quantity must be at least 1");
            }
            // Check if cart item exists
            $stmt = $this->conn->prepare("SELECT inventory_id FROM cart WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows === 0) {
                throw new Exception("Cart item not found");
            }
            $cart = $result->fetch_assoc();
            $stmt->close();
            // Check inventory
            $stmt = $this->conn->prepare("SELECT quantity FROM inventory WHERE id = ?");
            $stmt->bind_param("i", $cart['inventory_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows === 0) {
                throw new Exception("Inventory item not found");
            }
            $inv = $result->fetch_assoc();
            $stmt->close();
            if ($quantity > $inv['quantity']) {
                throw new Exception("Not enough stock available");
            }
            // Update cart
            $stmt = $this->conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
            $stmt->bind_param("ii", $quantity, $id);
            $save = $stmt->execute();
            $stmt->close();
            if ($save) {
                return json_encode([
                    'status' => 'success',
                    'msg' => 'Cart quantity updated'
                ]);
            } else {
                throw new Exception($this->conn->error);
            }
        } catch (Exception $e) {
            return json_encode([
                'status' => 'failed',
                'msg' => $e->getMessage()
            ]);
        }
    }

    function add_to_cart()
    {
        try {
            error_log("Add to cart called with POST data: " . print_r($_POST, true));
            error_log("Session data: " . print_r($_SESSION, true));

            if (!isset($_SESSION['auth_user']) && !isset($_SESSION['userdata'])) {
                throw new Exception("Please login first");
            }

            if (!isset($_POST['inventory_id']) || !isset($_POST['quantity'])) {
                throw new Exception("Missing required fields");
            }

            $inventory_id = $_POST['inventory_id'];
            $quantity = $_POST['quantity'];
            $price = $_POST['price'];
            $user_id = $_SESSION['auth_user']['id'] ?? $_SESSION['userdata']['id'];

            // Validate inventory
            $stmt = $this->conn->prepare("SELECT i.*, p.name FROM inventory i 
                                        INNER JOIN products p ON p.id = i.product_id 
                                        WHERE i.id = ?");
            $stmt->bind_param("i", $inventory_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 0) {
                throw new Exception("Product not found");
            }

            $inv = $result->fetch_assoc();
            $stmt->close();

            // Check if quantity is available
            if ($inv['quantity'] < $quantity) {
                throw new Exception("Sorry, only {$inv['quantity']} item(s) are available");
            }

            // Check if item already exists in cart
            $stmt = $this->conn->prepare("SELECT id, quantity FROM cart WHERE inventory_id = ? AND client_id = ?");
            $stmt->bind_param("ii", $inventory_id, $user_id);
            $stmt->execute();
            $cart_result = $stmt->get_result();
            $stmt->close();

            if ($cart_result->num_rows > 0) {
                // Update existing cart item
                $cart_item = $cart_result->fetch_assoc();
                $new_quantity = $cart_item['quantity'] + $quantity;

                if ($new_quantity > $inv['quantity']) {
                    throw new Exception("Sorry, only {$inv['quantity']} item(s) are available");
                }

                $stmt = $this->conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
                $stmt->bind_param("ii", $new_quantity, $cart_item['id']);
            } else {
                // Insert new cart item
                $stmt = $this->conn->prepare("INSERT INTO cart (client_id, inventory_id, price, quantity) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iidi", $user_id, $inventory_id, $price, $quantity);
            }

            $save = $stmt->execute();
            $stmt->close();

            if ($save) {
                // Get updated cart count
                $stmt = $this->conn->prepare("SELECT SUM(quantity) as cart_count FROM cart WHERE client_id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $cart_count = $result->fetch_assoc()['cart_count'] ?? 0;
                $stmt->close();

                return json_encode([
                    'status' => 'success',
                    'msg' => 'Item added to cart successfully',
                    'cart_count' => $cart_count
                ]);
            } else {
                throw new Exception($this->conn->error);
            }

        } catch (Exception $e) {
            return json_encode([
                'status' => 'failed',
                'msg' => $e->getMessage()
            ]);
        }
    }

    // Add new method for async payment status check
    public function check_paypack_status()
    {
        try {
            if (!isset($_POST['order_id']) || !isset($_POST['paypack_ref']) || !isset($_POST['momo_number'])) {
                throw new Exception("Missing required fields");
            }
            $order_id = intval($_POST['order_id']);
            $paypack_ref = $_POST['paypack_ref'];
            $momo_number = $_POST['momo_number'];
            require_once(__DIR__ . '/PaypackHandler.php');
            $paypack = new PaypackHandler();
            $poll = $paypack->pollStatus($paypack_ref, $momo_number);
            if ($poll['success'] && $poll['status'] === 'successful') {
                // Only mark as paid if not already paid
                $stmt = $this->conn->prepare("SELECT paid FROM orders WHERE id = ?");
                $stmt->bind_param("i", $order_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                $stmt->close();
                if ($row && $row['paid'] != 1) {
                    $paid = 1;
                    $stmt = $this->conn->prepare("UPDATE orders SET paid = ? WHERE id = ?");
                    $stmt->bind_param("ii", $paid, $order_id);
                    $stmt->execute();
                }
                return json_encode(['status' => 'success', 'msg' => 'Payment successful']);
            } else if ($poll['success'] && $poll['status'] === 'failed') {
                return json_encode(['status' => 'failed', 'msg' => 'Payment failed']);
            } else {
                // Do not update order if still pending
                return json_encode(['status' => 'pending', 'msg' => 'Payment pending']);
            }
        } catch (Throwable $e) {
            return json_encode(['status' => 'error', 'msg' => $e->getMessage()]);
        }
    }
}
