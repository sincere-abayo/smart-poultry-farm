<?php
$products = $conn->query("SELECT p.*,b.name as bname FROM `products` p inner join brands b on p.brand_id = b.id where md5(p.id) = '{$_GET['id']}' ");
if ($products->num_rows > 0) {
    foreach ($products->fetch_assoc() as $k => $v) {
        $$k = stripslashes($v);
    }
    $upload_path = base_app . '/uploads/product_' . $id;
    $img = "";
    if (is_dir($upload_path)) {
        $fileO = scandir($upload_path);
        if (isset($fileO[2]))
            $img = "uploads/product_" . $id . "/" . $fileO[2];
    }
    $inventory = $conn->query("SELECT * FROM inventory where product_id = " . $id);
    $inv = array();
    while ($ir = $inventory->fetch_assoc()) {
        $inv[] = $ir;
    }
}
?>
<style>
    .product-detail {
        background: var(--white);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
        margin-bottom: var(--spacing-xxl);
    }

    .product-gallery {
        position: relative;
    }

    .main-image {
        width: 100%;
        height: 500px;
        object-fit: cover;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-md);
    }

    .thumbnail-gallery {
        display: flex;
        gap: var(--spacing-sm);
        margin-top: var(--spacing-md);
        overflow-x: auto;
        padding: var(--spacing-sm) 0;
    }

    .thumbnail {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: var(--radius-sm);
        cursor: pointer;
        border: 2px solid transparent;
        transition: all var(--transition-fast);
    }

    .thumbnail:hover,
    .thumbnail.active {
        border-color: var(--primary-color);
        transform: scale(1.05);
    }

    .product-info {
        padding: var(--spacing-xl);
    }

    .product-title {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--black);
        margin-bottom: var(--spacing-md);
        line-height: 1.2;
    }

    .product-breed {
        color: var(--medium-gray);
        font-size: 1.125rem;
        margin-bottom: var(--spacing-lg);
    }

    .price-section {
        background: var(--light-gray);
        padding: var(--spacing-lg);
        border-radius: var(--radius-md);
        margin-bottom: var(--spacing-lg);
    }

    .price-label {
        font-size: 0.875rem;
        color: var(--medium-gray);
        margin-bottom: var(--spacing-xs);
    }

    .price-value {
        font-size: 2rem;
        font-weight: 700;
        color: var(--primary-color);
    }

    .stock-info {
        display: flex;
        align-items: center;
        gap: var(--spacing-sm);
        margin-bottom: var(--spacing-lg);
        padding: var(--spacing-sm) var(--spacing-md);
        background: rgba(40, 167, 69, 0.1);
        border-radius: var(--radius-sm);
        color: var(--success);
    }

    .quantity-section {
        display: flex;
        align-items: center;
        gap: var(--spacing-md);
        margin-bottom: var(--spacing-lg);
    }

    .quantity-input {
        width: 80px;
        text-align: center;
        border: 2px solid var(--light-gray);
        border-radius: var(--radius-sm);
        padding: var(--spacing-sm);
        font-weight: 600;
    }

    .add-to-cart-btn {
        flex: 1;
        padding: var(--spacing-md) var(--spacing-lg);
        font-size: 1.125rem;
        font-weight: 600;
    }

    .product-description {
        background: var(--light-gray);
        padding: var(--spacing-lg);
        border-radius: var(--radius-md);
        margin-top: var(--spacing-lg);
    }

    .related-products {
        margin-top: var(--spacing-xxl);
    }

    @media (max-width: 768px) {
        .product-title {
            font-size: 2rem;
        }

        .main-image {
            height: 300px;
        }

        .product-info {
            padding: var(--spacing-lg);
        }

        .quantity-section {
            flex-direction: column;
            align-items: stretch;
        }

        .add-to-cart-btn {
            width: 100%;
        }
    }
</style>

<section class="py-5">
    <div class="container">
        <div class="product-detail">
            <div class="row g-0">
                <div class="col-lg-6">
                    <div class="product-gallery p-4">
                        <img class="main-image" id="display-img" src="<?php echo validate_image($img) ?>"
                            alt="<?php echo $name ?>" />
                        <div class="thumbnail-gallery">
                            <?php
                            foreach ($fileO as $k => $img):
                                if (in_array($img, array('.', '..')))
                                    continue;
                                ?>
                                <img src="<?php echo validate_image('uploads/product_' . $id . '/' . $img) ?>"
                                    class="thumbnail <?php echo $k == 2 ? "active" : '' ?>" alt="Product Image"
                                    onclick="changeMainImage(this.src)" />
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="product-info">
                        <h1 class="product-title"><?php echo $name ?></h1>
                        <p class="product-breed">Breed: <?php echo $bname ?></p>

                        <div class="price-section">
                            <div class="price-label">Unit Price</div>
                            <div class="price-value">Frw <span
                                    id="unit-price"><?php echo number_format($inv[0]['price']) ?></span></div>
                        </div>

                        <div class="price-section">
                            <div class="price-label">Total Price</div>
                            <div class="price-value">Frw <span
                                    id="total-price"><?php echo number_format($inv[0]['price']) ?></span></div>
                        </div>

                        <div class="stock-info">
                            <i class="fas fa-check-circle"></i>
                            <span><strong>Available Stock:</strong> <span
                                    id="avail"><?php echo $inv[0]['quantity'] ?></span> units</span>
                        </div>

                        <form action="" id="add-cart">
                            <div class="quantity-section">
                                <label for="inputQuantity" class="form-label">Quantity:</label>
                                <input type="hidden" name="price" value="<?php echo $inv[0]['price'] ?>">
                                <input type="hidden" name="inventory_id" value="<?php echo $inv[0]['id'] ?>">
                                <input class="quantity-input" id="inputQuantity" type="number" min="1"
                                    max="<?php echo $inv[0]['quantity'] ?>" value="1" name="quantity" />
                                <button class="btn btn-primary add-to-cart-btn" type="submit">
                                    <i class="fas fa-shopping-cart"></i> Add to Cart
                                </button>
                            </div>
                        </form>

                        <div class="product-description">
                            <h5>Product Description</h5>
                            <p><?php echo stripslashes(html_entity_decode($specs)) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Related items section-->
<section class="related-products">
    <div class="container">
        <h2 class="section-title">Related Products</h2>
        <div class="products-grid">
            <?php
            $products = $conn->query("SELECT p.*,b.name as bname FROM `products` p inner join brands b on p.brand_id = b.id where p.status = 1 and (p.category_id = '{$category_id}' or p.sub_category_id = '{$sub_category_id}') and p.id !='{$id}' order by rand() limit 4 ");
            while ($row = $products->fetch_assoc()):
                $upload_path = base_app . '/uploads/product_' . $row['id'];
                $img = "";
                if (is_dir($upload_path)) {
                    $fileO = scandir($upload_path);
                    if (isset($fileO[2]))
                        $img = "uploads/product_" . $row['id'] . "/" . $fileO[2];
                }
                $inventory = $conn->query("SELECT * FROM inventory where product_id = " . $row['id']);
                $_inv = array();
                foreach ($row as $k => $v) {
                    $row[$k] = trim(stripslashes($v));
                }
                while ($ir = $inventory->fetch_assoc()) {
                    $_inv[] = number_format($ir['price']);
                }
                ?>
                <div class="product-card">
                    <a href=".?p=view_product&id=<?php echo md5($row['id']) ?>" class="text-decoration-none">
                        <img class="card-img-top" src="<?php echo validate_image($img) ?>"
                            alt="<?php echo $row['name'] ?>" />
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $row['name'] ?></h5>
                            <p class="card-text">Breed: <?php echo $row['bname'] ?></p>
                            <div class="price">
                                <?php foreach ($_inv as $k => $v): ?>
                                    Frw <?php echo $v ?>
                                <?php endforeach; ?>
                            </div>
                            <div class="btn btn-primary btn-sm w-100">
                                <i class="fas fa-eye"></i> View Details
                            </div>
                        </div>
                    </a>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<script>
    var inv = $.parseJSON('<?php echo json_encode($inv) ?>');

    // Helper function to format numbers with commas
    function number_format(number) {
        return number.toLocaleString('en-US');
    }

    // Function to change main image
    function changeMainImage(src) {
        $('#display-img').attr('src', src);
        $('.thumbnail').removeClass("active");
        event.target.classList.add("active");
    }

    $(function () {
        $('.view-image').click(function () {
            var _img = $(this).find('img').attr('src');
            $('#display-img').attr('src', _img);
            $('.view-image').removeClass("active")
            $(this).addClass("active")
        })

        $('.p-size').click(function () {
            var k = $(this).attr('data-id');
            $('.p-size').removeClass("active")
            $(this).addClass("active")
            $('#unit-price').text(Number(inv[k].price).toLocaleString())
            $('#total-price').text(Number(inv[k].price * ($('#inputQuantity').val() || 1)).toLocaleString())
            $('[name="price"]').val(inv[k].price)
            $('#avail').text(inv[k].quantity)
            $('[name="inventory_id"]').val(inv[k].id)
        })

        // Update price when quantity changes
        $('#inputQuantity').on('input', function () {
            var quantity = parseInt($(this).val()) || 0;
            var basePrice = parseFloat($('[name="price"]').val());
            var totalPrice = basePrice * quantity;
            $('#total-price').text(number_format(totalPrice));
        });

        $('#add-cart').submit(function (e) {
            e.preventDefault();

            // Check login status
            if (!'<?php echo isset($_SESSION['auth_user']) || isset($_SESSION['userdata']) ?>') {
                uni_modal("", "login.php");
                return false;
            }

            // Validate quantity
            var quantity = parseInt($('#inputQuantity').val());
            var available = parseInt($('#avail').text());

            if (isNaN(quantity) || quantity <= 0) {
                alert_toast("Please enter a valid quantity", 'error');
                return false;
            }

            if (quantity > available) {
                alert_toast("Sorry, only " + available + " item(s) are available", 'error');
                return false;
            }

            start_loader();
            $.ajax({
                url: _base_url_ + 'classes/handler.php?f=add_to_cart',
                data: $(this).serialize(),
                method: 'POST',
                dataType: "json",
                error: err => {
                    console.log(err)
                    alert_toast("an error occured", 'error')
                    end_loader()
                },
                success: function (resp) {
                    if (typeof resp == 'object' && resp.status == 'success') {
                        alert_toast("Product added to cart.", 'success')
                        $('#cart-count').text(resp.cart_count)
                    } else {
                        console.log(resp)
                        alert_toast("an error occured", 'error')
                    }
                    end_loader();
                }
            })
        })
    })
</script>