<style>
    .cart-container {
        max-width: 1200px;
        margin: 0 auto;
    }

    .cart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: var(--spacing-xl);
        padding-bottom: var(--spacing-lg);
        border-bottom: 2px solid var(--light-gray);
    }

    .cart-title {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--black);
        margin: 0;
    }

    .cart-actions {
        display: flex;
        gap: var(--spacing-md);
    }

    .cart-item {
        background: var(--white);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        padding: var(--spacing-lg);
        margin-bottom: var(--spacing-lg);
        transition: all var(--transition-normal);
    }

    .cart-item:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-2px);
    }

    .cart-item-content {
        display: flex;
        align-items: center;
        gap: var(--spacing-lg);
    }

    .cart-item-image {
        width: 120px;
        height: 120px;
        object-fit: cover;
        border-radius: var(--radius-md);
        box-shadow: var(--shadow-sm);
    }

    .cart-item-details {
        flex: 1;
    }

    .cart-item-name {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--black);
        margin-bottom: var(--spacing-sm);
    }

    .cart-item-breed {
        color: var(--medium-gray);
        margin-bottom: var(--spacing-sm);
    }

    .cart-item-price {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--primary-color);
        margin-bottom: var(--spacing-md);
    }

    .quantity-controls {
        display: flex;
        align-items: center;
        gap: var(--spacing-sm);
    }

    .quantity-btn {
        width: 40px;
        height: 40px;
        border: 2px solid var(--light-gray);
        background: var(--white);
        border-radius: var(--radius-sm);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all var(--transition-fast);
    }

    .quantity-btn:hover {
        border-color: var(--primary-color);
        background: var(--primary-color);
        color: var(--white);
    }

    .quantity-input {
        width: 60px;
        text-align: center;
        border: 2px solid var(--light-gray);
        border-radius: var(--radius-sm);
        padding: var(--spacing-sm);
        font-weight: 600;
    }

    .cart-item-total {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--primary-color);
        text-align: right;
    }

    .cart-summary {
        background: var(--light-gray);
        border-radius: var(--radius-lg);
        padding: var(--spacing-xl);
        margin-top: var(--spacing-xl);
        position: sticky;
        top: var(--spacing-lg);
    }

    .summary-title {
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: var(--spacing-lg);
        color: var(--black);
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: var(--spacing-sm) 0;
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
    }

    .summary-row:last-child {
        border-bottom: none;
        font-weight: 700;
        font-size: 1.25rem;
        color: var(--primary-color);
    }

    .checkout-btn {
        width: 100%;
        padding: var(--spacing-md) var(--spacing-lg);
        font-size: 1.125rem;
        font-weight: 600;
        margin-top: var(--spacing-lg);
    }

    .empty-cart-btn {
        background: var(--danger);
        color: var(--white);
        border: none;
    }

    .empty-cart-btn:hover {
        background: #c82333;
        transform: translateY(-2px);
    }

    @media (max-width: 768px) {
        .cart-item-content {
            flex-direction: column;
            text-align: center;
        }

        .cart-item-image {
            width: 100px;
            height: 100px;
        }

        .cart-item-total {
            text-align: center;
            margin-top: var(--spacing-md);
        }

        .cart-header {
            flex-direction: column;
            gap: var(--spacing-md);
            text-align: center;
        }

        .cart-title {
            font-size: 2rem;
        }
    }
</style>

<section class="py-5">
    <div class="container cart-container">
        <div class="cart-header">
            <h1 class="cart-title">Shopping Cart</h1>
            <div class="cart-actions">
                <button class="btn empty-cart-btn" type="button" id="empty_cart">
                    <i class="fas fa-trash"></i> Empty Cart
                </button>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="cart-items">
                    <?php
                    $qry = $conn->query("SELECT c.*,p.name,i.price,p.id as pid from `cart` c inner join `inventory` i on i.id=c.inventory_id inner join products p on p.id = i.product_id where c.client_id = " . $_settings->userdata('id'));
                    while ($row = $qry->fetch_assoc()):
                        $upload_path = base_app . '/uploads/product_' . $row['pid'];
                        $img = "";
                        foreach ($row as $k => $v) {
                            $row[$k] = trim(stripslashes($v));
                        }
                        if (is_dir($upload_path)) {
                            $fileO = scandir($upload_path);
                            if (isset($fileO[2]))
                                $img = "uploads/product_" . $row['pid'] . "/" . $fileO[2];
                            // var_dump($fileO);
                        }
                        ?>
                        <div class="cart-item">
                            <div class="cart-item-content">
                                <button class="btn btn-outline-danger btn-sm rem_item" data-id="<?php echo $row['id'] ?>"
                                    style="position: absolute; top: 10px; right: 10px;">
                                    <i class="fas fa-trash"></i>
                                </button>

                                <img src="<?php echo validate_image($img) ?>" loading="lazy" class="cart-item-image"
                                    alt="<?php echo $row['name'] ?>">

                                <div class="cart-item-details">
                                    <h5 class="cart-item-name"><?php echo $row['name'] ?></h5>
                                    <p class="cart-item-breed">Breed: <?php echo $row['bname'] ?? 'N/A' ?></p>
                                    <p class="cart-item-price">Price: Frw <span
                                            class="price"><?php echo number_format($row['price']) ?></span></p>

                                    <div class="quantity-controls">
                                        <button class="quantity-btn min-qty" type="button">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <input type="number" class="quantity-input cart-qty"
                                            value="<?php echo $row['quantity'] ?>" data-id="<?php echo $row['id'] ?>"
                                            readonly>
                                        <button class="quantity-btn plus-qty" type="button">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="cart-item-total">
                                    <span class="total-amount">Frw
                                        <?php echo number_format($row['price'] * $row['quantity']) ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="cart-summary">
                    <h3 class="summary-title">Order Summary</h3>

                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span id="grand-total">-</span>
                    </div>

                    <div class="summary-row">
                        <span>Shipping</span>
                        <span>Free</span>
                    </div>

                    <div class="summary-row">
                        <span>Total</span>
                        <span id="grand-total-final">-</span>
                    </div>

                    <a href="./?p=checkout" class="btn btn-primary checkout-btn">
                        <i class="fas fa-credit-card"></i> Proceed to Checkout
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
    function calc_total() {
        var total = 0

        $('.total-amount').each(function () {
            var amount = $(this).text();
            amount = amount.replace('Frw', '').replace(/\,/g, '').trim(); // Remove 'Frw' and commas
            amount = parseFloat(amount);
            total += amount;
        })
        $('#grand-total').text('Frw ' + parseFloat(total).toLocaleString('en-US'))
        $('#grand-total-final').text('Frw ' + parseFloat(total).toLocaleString('en-US'))
    }

    function qty_change($type, _this) {
        var qty = _this.closest('.cart-item').find('.cart-qty').val()
        var price = _this.closest('.cart-item').find('.price').text()
        price = price.replace(/,/g, '')
        console.log(price)
        var cart_id = _this.closest('.cart-item').find('.cart-qty').attr('data-id')
        var new_total = 0
        start_loader();
        if ($type == 'minus') {
            qty = parseInt(qty) - 1
        } else {
            qty = parseInt(qty) + 1
        }
        price = parseFloat(price)
        // console.log(qty,price)
        new_total = 'Frw ' + parseFloat(qty * price).toLocaleString('en-US')
        _this.closest('.cart-item').find('.cart-qty').val(qty)
        _this.closest('.cart-item').find('.total-amount').text(new_total)
        calc_total()

        $.ajax({
            url: 'classes/handler.php?f=update_cart_qty',
            method: 'POST',
            data: {
                id: cart_id,
                quantity: qty
            },
            dataType: 'json',
            error: err => {
                console.log(err)
                alert_toast("an error occured", 'error');
                end_loader()
            },
            success: function (resp) {
                if (!!resp.status && resp.status == 'success') {
                    end_loader()
                } else {
                    alert_toast("an error occured", 'error');
                    end_loader()
                }
            }

        })
    }

    function rem_item(id) {
        $('.modal').modal('hide')
        var _this = $('.rem_item[data-id="' + id + '"]')
        var id = _this.attr('data-id')
        var item = _this.closest('.cart-item')
        start_loader();
        $.ajax({
            url: 'classes/Master.php?f=delete_cart',
            method: 'POST',
            data: {
                id: id
            },
            dataType: 'json',
            error: err => {
                console.log(err)
                alert_toast("an error occured", 'error');
                end_loader()
            },
            success: function (resp) {
                if (!!resp.status && resp.status == 'success') {
                    item.hide('slow', function () {
                        item.remove()
                    })
                    calc_total()
                    end_loader()
                } else {
                    alert_toast("an error occured", 'error');
                    end_loader()
                }
            }

        })
    }

    function empty_cart() {
        start_loader();
        $.ajax({
            url: 'classes/Master.php?f=empty_cart',
            method: 'POST',
            data: {},
            dataType: 'json',
            error: err => {
                console.log(err)
                alert_toast("an error occured", 'error');
                end_loader()
            },
            success: function (resp) {
                if (!!resp.status && resp.status == 'success') {
                    location.reload()
                } else {
                    alert_toast("an error occured", 'error');
                    end_loader()
                }
            }

        })
    }
    $(function () {
        calc_total()
        $('.min-qty').click(function () {
            qty_change('minus', $(this))
        })
        $('.plus-qty').click(function () {
            qty_change('plus', $(this))
        })
        $('#empty_cart').click(function () {
            // empty_cart()
            _conf("Are you sure to empty your cart list?", 'empty_cart', [])
        })
        $('.rem_item').click(function () {
            _conf("Are you sure to remove the item in cart list?", 'rem_item', [$(this).attr('data-id')])
        })
    })
</script>

<?php include 'inc/footer.php' ?>