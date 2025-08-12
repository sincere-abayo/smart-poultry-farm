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

    function place_order()
    {
        // Enable full error reporting
        error_reporting(E_ALL);
        ini_set('display_errors', 0); // Disable display_errors to prevent non-JSON output
        ini_set('log_errors', 1);
        ini_set('error_log', '/tmp/php_errors.log');

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
                error_log("Database connection lost at start of place_order");
                if (!$this->reconnect()) {
                    throw new Exception("Database connection failed. Please try again.");
                }
            }

            // Verify database connection before proceeding
            if (!$this->isConnected()) {
                error_log("Database connection lost before order placement");
                $this->reconnect();
            }
            header('Cache-Control: no-cache, must-revalidate');

            // Debug logging
            error_log("Starting place_order function");
            error_log("POST data: " . print_r($_POST, true));
            error_log("SESSION data: " . print_r($_SESSION, true));

            // Enable error reporting for debugging
            error_reporting(E_ALL);
            ini_set('display_errors', 0); // Disable display_errors to prevent non-JSON output

            // Error handler already set globally; do not redefine here

            // Log the incoming data
            error_log("[Place Order] Starting order placement...");
            error_log("[Place Order] POST data: " . print_r($_POST, true));
            error_log("[Place Order] SESSION data: " . print_r($_SESSION, true));

            // Validate session
            if (!isset($_SESSION)) {
                throw new Exception("Session not started");
            }

            if (!isset($_SESSION['userdata']['id'])) {
                throw new Exception("User not logged in");
            }

            if (
                !isset($_POST['amount']) || !isset($_POST['payment_method']) ||
                !isset($_POST['paid']) || !isset($_POST['order_type'])
            ) {
                throw new Exception("Missing required fields");
            }

            $client_id = $_SESSION['userdata']['id'];
            $amount = floatval($_POST['amount']);
            $payment_method = $_POST['payment_method'];
            $paid = intval($_POST['paid']);
            $order_type = intval($_POST['order_type']);
            $delivery_address = $_POST['delivery_address'] ?? '';
            $momo_number = $_POST['momo_number'] ?? '';

            // Start transaction
            error_log("[Place Order] Starting database transaction");
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

            // Check database connection
            if (!$this->conn || $this->conn->connect_errno !== 0) {
                throw new Exception("Database connection lost");
            }

            // Get cart items
            $cart_items = $this->conn->query("SELECT c.*, i.price, i.product_id 
                FROM cart c 
                INNER JOIN inventory i ON i.id = c.inventory_id 
                WHERE c.client_id = " . intval($client_id));

            if (!$cart_items) {
                throw new Exception("Failed to retrieve cart items: " . $this->conn->error);
            }

            if ($cart_items->num_rows === 0) {
                throw new Exception("Your cart is empty");
            }

            // Insert order items and update inventory
            while ($item = $cart_items->fetch_assoc()) {
                // Calculate total for this item
                $total = $item['quantity'] * $item['price'];

                // Check inventory availability
                $inventory_check = $this->conn->query("SELECT quantity FROM inventory WHERE id = " . intval($item['inventory_id']));
                if (!$inventory_check || $inventory_check->num_rows === 0) {
                    throw new Exception("Product not found in inventory");
                }

                $current_stock = $inventory_check->fetch_assoc()['quantity'];
                if ($current_stock < $item['quantity']) {
                    throw new Exception("Not enough stock available for " . $item['name']);
                }

                // Insert order item
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

                // Update inventory
                $stmt = $this->conn->prepare("UPDATE inventory SET quantity = quantity - ? WHERE id = ? AND quantity >= ?");
                if (!$stmt->bind_param("iii", $item['quantity'], $item['inventory_id'], $item['quantity'])) {
                    throw new Exception("Failed to bind inventory update parameters: " . $stmt->error);
                }

                if (!$stmt->execute()) {
                    throw new Exception("Failed to update inventory");
                }
            }

            // Handle MOMO payment
            if ($payment_method === "MoMoPay") {
                // Here you would integrate with actual MOMO API
                // For now, we'll simulate a successful payment
                $paid = 1;

                // Update order payment status
                $stmt = $this->conn->prepare("UPDATE orders SET paid = ? WHERE id = ?");
                $stmt->bind_param("ii", $paid, $order_id);

                if (!$stmt->execute()) {
                    throw new Exception("Failed to update payment status");
                }
            }

            // Clear cart
            $this->conn->query("DELETE FROM cart WHERE client_id = {$client_id}");

            // Verify database connection before commit
            if (!$this->isConnected()) {
                error_log("Database connection lost before commit");
                if (!$this->reconnect()) {
                    throw new Exception("Failed to reconnect to database before commit");
                }
            }

            // Commit transaction
            if (!$this->conn->commit()) {
                throw new Exception("Failed to commit transaction");
            }

            // Clear any buffered output
            if (ob_get_length())
                ob_clean();

            // Send success response
            echo json_encode([
                'status' => 'success',
                'order_id' => $order_id,
                'message' => 'Order placed successfully'
            ]);
            exit;

        } catch (Throwable $e) {
            error_log("Exception in place_order: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());

            // Rollback transaction on error
            try {
                if ($this->conn && $this->conn->connect_errno === 0) {
                    $this->conn->rollback();
                    error_log("Transaction rolled back successfully");
                }
            } catch (Exception $rollbackError) {
                error_log("Rollback failed: " . $rollbackError->getMessage());
            }

            // Log the error for debugging
            error_log("Order placement failed: " . $e->getMessage());
            error_log("Order details: " . print_r($_POST, true));
            error_log("Stack trace: " . $e->getTraceAsString());

            // Clear any buffered output
            if (ob_get_length())
                ob_clean();

            // Send error response with appropriate status code
            $statusCode = ($e instanceof mysqli_sql_exception) ? 503 : 400;
            http_response_code($statusCode);

            // Sanitize error message for production
            $publicMessage = ($e instanceof mysqli_sql_exception)
                ? "Database operation failed. Please try again later."
                : $e->getMessage();

            echo json_encode([
                'status' => 'failed',
                'error' => $publicMessage,
                'debug' => [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'file' => basename($e->getFile()),
                    'line' => $e->getLine()
                ]
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
            if (!$category) throw new Exception("Category name is required");
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
            if (!$name) throw new Exception("Brand name is required");
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
                return json_encode([
                    'status' => 'success',
                    'msg' => 'Order status updated'
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
}
