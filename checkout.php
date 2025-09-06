<?php
require_once('inc/header.php');
?>
<?php
$total = 0;
$qry = $conn->query("SELECT c.*, p.name, i.price, p.id as pid 
    FROM `cart` c 
    INNER JOIN `inventory` i ON i.id = c.inventory_id 
    INNER JOIN products p ON p.id = i.product_id 
    WHERE c.client_id = " . $_settings->userdata('id'));
while ($row = $qry->fetch_assoc()):
    $total += $row['price'] * $row['quantity'];
endwhile;
?>
<style>
.checkout-container {
    max-width: 1000px;
    margin: 0 auto;
}

.checkout-header {
    text-align: center;
    margin-bottom: var(--spacing-xxl);
}

.checkout-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--black);
    margin-bottom: var(--spacing-md);
}

.checkout-subtitle {
    color: var(--medium-gray);
    font-size: 1.125rem;
}

.checkout-form {
    background: var(--white);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm);
    padding: var(--spacing-xxl);
    margin-bottom: var(--spacing-xl);
}

.form-section {
    margin-bottom: var(--spacing-xl);
}

.section-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--black);
    margin-bottom: var(--spacing-lg);
    padding-bottom: var(--spacing-sm);
    border-bottom: 2px solid var(--light-gray);
}

.order-type-options {
    display: flex;
    gap: var(--spacing-lg);
    margin-bottom: var(--spacing-lg);
}

.order-type-option {
    flex: 1;
    position: relative;
}

.order-type-option input[type="radio"] {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
}

.order-type-option label {
    display: block;
    padding: var(--spacing-lg);
    border: 2px solid var(--light-gray);
    border-radius: var(--radius-md);
    text-align: center;
    cursor: pointer;
    transition: all var(--transition-fast);
    background: var(--white);
}

.order-type-option input[type="radio"]:checked+label {
    border-color: var(--primary-color);
    background: rgba(46, 125, 50, 0.1);
    color: var(--primary-color);
}

.order-type-option label:hover {
    border-color: var(--primary-color);
}

.address-section {
    background: var(--light-gray);
    padding: var(--spacing-lg);
    border-radius: var(--radius-md);
    margin-top: var(--spacing-lg);
}

.payment-methods {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-md);
}

.payment-method {
    padding: var(--spacing-lg);
    border: 2px solid var(--light-gray);
    border-radius: var(--radius-md);
    cursor: pointer;
    transition: all var(--transition-fast);
    background: var(--white);
}

.payment-method:hover {
    border-color: var(--primary-color);
    background: rgba(46, 125, 50, 0.05);
}

.payment-method.selected {
    border-color: var(--primary-color);
    background: rgba(46, 125, 50, 0.1);
}

.payment-method-header {
    display: flex;
    align-items: center;
    gap: var(--spacing-md);
    margin-bottom: var(--spacing-sm);
}

.payment-icon {
    width: 40px;
    height: 40px;
    background: var(--primary-color);
    border-radius: var(--radius-sm);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--white);
    font-size: 1.25rem;
}

.payment-title {
    font-weight: 600;
    color: var(--black);
}

.payment-description {
    color: var(--medium-gray);
    font-size: 0.875rem;
}

.stripe-features {
    display: flex;
    justify-content: space-around;
    margin-top: 1rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 10px;
}

.stripe-features .feature {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}

.stripe-features .feature i {
    font-size: 1.5rem;
    color: #28a745;
    margin-bottom: 0.5rem;
}

.stripe-features .feature span {
    font-size: 0.9rem;
    color: #6c757d;
    font-weight: 500;
}

@media (max-width: 768px) {
    .stripe-features {
        flex-direction: column;
        gap: 1rem;
    }

    .stripe-features .feature {
        flex-direction: row;
        justify-content: flex-start;
    }

    .stripe-features .feature i {
        margin-right: 0.5rem;
        margin-bottom: 0;
    }
}

.momo-input {
    margin-top: var(--spacing-md);
    padding: var(--spacing-md);
    background: var(--light-gray);
    border-radius: var(--radius-sm);
}

.total-summary {
    background: var(--light-gray);
    border-radius: var(--radius-lg);
    padding: var(--spacing-xl);
    margin-top: var(--spacing-xl);
    position: sticky;
    top: var(--spacing-lg);
}

.total-amount {
    font-size: 2rem;
    font-weight: 700;
    color: var(--primary-color);
    text-align: center;
    margin-bottom: var(--spacing-lg);
}

@media (max-width: 768px) {
    .order-type-options {
        flex-direction: column;
    }

    .checkout-form {
        padding: var(--spacing-lg);
    }

    .checkout-title {
        font-size: 2rem;
    }
}
</style>

<section class="py-5">
    <div class="container checkout-container">
        <div class="checkout-header">
            <h1 class="checkout-title">Checkout</h1>
            <p class="checkout-subtitle">Complete your order with secure payment</p>
        </div>

        <div class="checkout-form">
            <form action="" id="place_order">
                <input type="hidden" name="amount" value="<?php echo $total ?>">
                <input type="hidden" name="payment_method" value="cod">
                <input type="hidden" name="paid" value="0">

                <div class="row">
                    <div class="col-lg-8">
                        <!-- Order Type Section -->
                        <div class="form-section">
                            <h3 class="section-title">Delivery Options</h3>
                            <div class="order-type-options">
                                <div class="order-type-option">
                                    <input type="radio" id="delivery" name="order_type" value="1" checked>
                                    <label for="delivery">
                                        <i class="fas fa-truck"></i><br>
                                        <strong>Home Delivery</strong><br>
                                        <small>Delivered to your address</small>
                                    </label>
                                </div>
                                <div class="order-type-option">
                                    <input type="radio" id="pickup" name="order_type" value="2">
                                    <label for="pickup">
                                        <i class="fas fa-store"></i><br>
                                        <strong>Store Pickup</strong><br>
                                        <small>Pick up at our location</small>
                                    </label>
                                </div>
                            </div>

                            <div class="address-section address-holder">
                                <label class="form-label">Delivery Address</label>
                                <textarea name="delivery_address" class="form-control" rows="3"
                                    placeholder="Enter your complete delivery address"><?php echo $_settings->userdata('default_delivery_address') ?></textarea>
                            </div>
                        </div>

                        <!-- Payment Methods Section -->
                        <div class="form-section">
                            <h3 class="section-title">Payment Methods</h3>
                            <div class="payment-methods">
                                <div class="payment-method" onclick="selectPaymentMethod('cod')">
                                    <div class="payment-method-header">
                                        <div class="payment-icon">
                                            <i class="fas fa-money-bill-wave"></i>
                                        </div>
                                        <div>
                                            <div class="payment-title">Cash on Delivery</div>
                                            <div class="payment-description">Pay when your order arrives</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="payment-method" onclick="selectPaymentMethod('mtn')">
                                    <div class="payment-method-header">
                                        <div class="payment-icon">
                                            <i class="fas fa-mobile-alt"></i>
                                        </div>
                                        <div>
                                            <div class="payment-title">MTN Mobile Money</div>
                                            <div class="payment-description">Pay with your MTN mobile money account
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mtn-input" style="display: none;">
                                        <label for="mtn_number" class="form-label">Enter MTN Number</label>
                                        <input type="text" name="mtn_number" id="mtn_number" class="form-control"
                                            placeholder="07XXXXXXXX" />
                                        <small class="text-muted">Enter your MTN number (e.g., 078XXXXXXX)</small>
                                    </div>
                                </div>

                                <div class="payment-method" onclick="selectPaymentMethod('stripe')">
                                    <div class="payment-method-header">
                                        <div class="payment-icon">
                                            <i class="fab fa-cc-stripe"></i>
                                        </div>
                                        <div>
                                            <div class="payment-title">Stripe Payment</div>
                                            <div class="payment-description">Secure checkout with Stripe (redirects to
                                                secure payment page)
                                            </div>
                                        </div>
                                    </div>
                                    <div class="stripe-payment-info" id="stripe-payment-info" style="display: none;">
                                        <div class="alert alert-info">
                                            <i class="fas fa-shield-alt"></i>
                                            <strong>Secure Payment:</strong> You will be redirected to Stripe's secure
                                            checkout page to complete your payment safely.
                                        </div>
                                        <div class="stripe-features">
                                            <div class="feature">
                                                <i class="fas fa-lock"></i>
                                                <span>256-bit SSL encryption</span>
                                            </div>
                                            <div class="feature">
                                                <i class="fas fa-credit-card"></i>
                                                <span>All major cards accepted</span>
                                            </div>
                                            <div class="feature">
                                                <i class="fas fa-mobile-alt"></i>
                                                <span>Mobile optimized</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="total-summary">
                            <h3 class="section-title">Order Summary</h3>
                            <div class="total-amount">Frw <?php echo number_format($total) ?></div>
                            <button type="button" class="btn btn-primary btn-lg w-100" onclick="submitOrder()">
                                <i class="fas fa-lock"></i> Complete Order
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    </div>
</section>

<script>
// Handle form submission - the main function that processes the order
function processOrder(paymentMethod, paid) {
    var form = $('#place_order');
    if (form.data('submitting')) {
        return false;
    }

    // Validate required fields
    var amount = $('[name="amount"]').val();
    var orderType = $('[name="order_type"]:checked').val();
    var deliveryAddress = $('[name="delivery_address"]').val().trim();

    if (!amount || amount <= 0) {
        alert_toast("Invalid order amount", "error");
        return false;
    }

    if (!orderType) {
        alert_toast("Please select an order type", "error");
        return false;
    }

    // For delivery orders, validate address
    if (orderType == '1' && !deliveryAddress) {
        alert_toast("Please enter delivery address", "error");
        return false;
    }

    // Validate payment method selection
    if (!$('.payment-method.selected').length) {
        alert_toast("Please select a payment method", "error");
        return false;
    }

    form.data('submitting', true);
    start_loader();

    $('[name="payment_method"]').val(paymentMethod);
    $('[name="paid"]').val(paid);

    $.ajax({
        url: 'classes/handler.php?f=place_order',
        method: 'POST',
        data: form.serialize(),
        dataType: "json",
        timeout: 30000,
        error: function(xhr, status, error) {
            var errorMessage = "An error occurred while processing your order";

            if (xhr.responseText) {
                try {
                    var response = JSON.parse(xhr.responseText);
                    if (response && response.error) {
                        errorMessage = response.error;
                    }
                } catch (e) {
                    if (xhr.responseText.includes("Fatal error") ||
                        xhr.responseText.includes("Parse error") ||
                        xhr.responseText.includes("Warning")) {
                        errorMessage = "A server error occurred. Please try again later.";
                    }
                }
            } else if (status === "timeout") {
                errorMessage = "Request timed out. Please try again.";
            } else if (status === "error" && !xhr.responseText) {
                errorMessage = "Could not connect to the server. Please check your connection.";
            }

            alert_toast(errorMessage, "error");
            end_loader();
            form.data('submitting', false);
        },
        success: function(resp) {
            if (resp.status === 'success') {
                alert_toast("Order placed successfully! Payment confirmation sent to your email and phone.",
                    "success");
                setTimeout(function() {
                    location.replace('./');
                }, 3000);
            } else {
                alert_toast(resp.msg || resp.error || "Failed to place order", "error");
                end_loader();
                form.data('submitting', false);
            }
        }
    });

    return false;
}

// Payment method selection
function selectPaymentMethod(method) {
    // Remove selected class from all payment methods
    $('.payment-method').removeClass('selected');

    // Add selected class to clicked method
    event.currentTarget.classList.add('selected');

    // Hide all input sections
    $('.momo-input').hide();
    $('.mtn-input').hide();

    // Show relevant input section
    if (method === 'momo') {
        $('.momo-input').show();
        $('#stripe-payment-info').hide();
    } else if (method === 'mtn') {
        $('.mtn-input').show();
        $('#stripe-payment-info').hide();
    } else if (method === 'stripe') {
        $('#stripe-payment-info').show();
        $('.momo-input').hide();
        $('.mtn-input').hide();
    } else {
        $('#stripe-payment-info').hide();
        $('.momo-input').hide();
        $('.mtn-input').hide();
    }

    // Update hidden payment method field
    $('[name="payment_method"]').val(method);
}

// Cash on Delivery handler
function submitWithCOD() {
    processOrder("cod", 0);
}

// MOMO Payment handler
function payWithMomo() {
    var momo_number = $('#momo_number').val().trim();
    if (!momo_number || !/^07\d{8}$/.test(momo_number)) {
        alert_toast("Please enter a valid MTN number (e.g., 07XXXXXXXX)", "warning");
        return;
    }

    processOrder("momo", 1);
}

// MTN Payment handler
function payWithMTN() {
    var mtn_number = $('#mtn_number').val().trim();
    if (!mtn_number || !/^07\d{8}$/.test(mtn_number)) {
        alert_toast("Please enter a valid MTN number (e.g., 07XXXXXXXX)", "warning");
        return;
    }

    processOrder("mtn", 1);
}

// Stripe Payment handler
function payWithStripe() {
    var amount = $('[name="amount"]').val();
    var orderType = $('[name="order_type"]:checked').val();
    var deliveryAddress = $('[name="delivery_address"]').val().trim();

    if (!amount || amount <= 0) {
        alert_toast("Invalid order amount", "error");
        return;
    }

    if (!orderType) {
        alert_toast("Please select an order type", "error");
        return;
    }

    // For delivery orders, validate address
    if (orderType == '1' && !deliveryAddress) {
        alert_toast("Please enter delivery address", "error");
        return;
    }

    // Redirect to Stripe checkout page
    var stripeUrl = 'stripe_checkout.php?amount=' + encodeURIComponent(amount) +
        '&order_type=' + encodeURIComponent(orderType) +
        '&delivery_address=' + encodeURIComponent(deliveryAddress);

    window.location.href = stripeUrl;
}

// Main submit function - determines which payment method to use
function submitOrder() {
    // Check if a payment method is selected
    if (!$('.payment-method.selected').length) {
        alert_toast("Please select a payment method", "error");
        return false;
    }

    var selectedMethod = $('.payment-method.selected').attr('onclick').match(/'([^']+)'/)[1];

    if (selectedMethod === 'momo') {
        payWithMomo();
    } else if (selectedMethod === 'mtn') {
        payWithMTN();
    } else if (selectedMethod === 'stripe') {
        payWithStripe();
    } else {
        submitWithCOD();
    }
}

// Order type change handler
$(function() {
    // Set default payment method selection
    $('.payment-method').first().addClass('selected');

    $('[name="order_type"]').change(function() {
        if ($(this).val() == 2) {
            $('.address-holder').hide('slow');
        } else {
            $('.address-holder').show('slow');
        }
    });
});
</script>
<?php
require_once('inc/footer.php');
?>