<style>
/* Modern Homepage Styles */
.hero-section {
    position: relative;
    height: 50vh;
    min-height: 300px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
}

.hero-carousel {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1;
}

.hero-carousel .carousel-item {
    height: 50vh;
    min-height: 300px;
}

.hero-carousel .carousel-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center top;
    background-color: var(--light-gray);
}

/* Alternative: If you want to show the full image without cropping */
.hero-carousel .carousel-item img.full-image {
    object-fit: contain;
    object-position: center;
}

.hero-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, rgba(46, 125, 50, 0.7) 0%, rgba(76, 175, 80, 0.5) 100%);
    z-index: 2;
}

.hero-content {
    position: relative;
    z-index: 3;
    text-align: center;
    color: var(--white);
    max-width: 800px;
    padding: 0 var(--spacing-lg);
}

.hero-title {
    font-size: 4rem;
    font-weight: 700;
    margin-bottom: var(--spacing-lg);
    text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.5);
    line-height: 1.2;
}

.hero-subtitle {
    font-size: 1.5rem;
    margin-bottom: var(--spacing-xl);
    opacity: 0.95;
    text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.5);
    font-weight: 300;
}

.hero-cta {
    display: flex;
    gap: var(--spacing-lg);
    flex-wrap: wrap;
    justify-content: center;
}

.hero-cta .btn {
    padding: var(--spacing-md) var(--spacing-xl);
    font-size: 1.125rem;
    font-weight: 600;
    border-radius: var(--radius-lg);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    transition: all var(--transition-normal);
}

.hero-cta .btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
}

.hero-carousel .carousel-control-prev,
.hero-carousel .carousel-control-next {
    width: 60px;
    height: 60px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    top: 50%;
    transform: translateY(-50%);
    opacity: 0.8;
    transition: all var(--transition-fast);
    backdrop-filter: blur(10px);
    border: 2px solid rgba(255, 255, 255, 0.3);
}

.hero-carousel .carousel-control-prev:hover,
.hero-carousel .carousel-control-next:hover {
    opacity: 1;
    background: rgba(255, 255, 255, 0.3);
    transform: translateY(-50%) scale(1.1);
}

.hero-carousel .carousel-control-prev {
    left: 30px;
}

.hero-carousel .carousel-control-next {
    right: 30px;
}

.hero-carousel .carousel-control-prev-icon,
.hero-carousel .carousel-control-next-icon {
    width: 20px;
    height: 20px;
}

/* Carousel indicators */
.hero-carousel .carousel-indicators {
    bottom: 30px;
    z-index: 4;
}

.hero-carousel .carousel-indicators [data-bs-target] {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.5);
    border: 2px solid rgba(255, 255, 255, 0.8);
    margin: 0 8px;
    transition: all var(--transition-fast);
}

.hero-carousel .carousel-indicators .active {
    background: var(--white);
    transform: scale(1.2);
}

/* Products section carousel (if needed elsewhere) */
.carousel-modern {
    border-radius: var(--radius-lg);
    overflow: hidden;
    box-shadow: var(--shadow-lg);
    margin-bottom: var(--spacing-xxl);
}

.carousel-modern .carousel-item img {
    height: 400px;
    object-fit: cover;
    width: 100%;
}

.filter-sidebar {
    background: var(--white);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm);
    padding: var(--spacing-lg);
    margin-bottom: var(--spacing-lg);
    position: sticky;
    top: var(--spacing-lg);
}

.filter-title {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: var(--spacing-lg);
    color: var(--primary-color);
    border-bottom: 2px solid var(--light-gray);
    padding-bottom: var(--spacing-sm);
}

.filter-item {
    padding: var(--spacing-sm) 0;
    border-bottom: 1px solid var(--light-gray);
}

.filter-item:last-child {
    border-bottom: none;
}

.filter-item label {
    font-weight: 500;
    color: var(--dark-gray);
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
}

.filter-item input[type="checkbox"] {
    width: 18px;
    height: 18px;
    accent-color: var(--primary-color);
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: var(--spacing-lg);
    margin-top: var(--spacing-lg);
}

.section-title {
    font-size: 2rem;
    font-weight: 600;
    text-align: center;
    margin-bottom: var(--spacing-xxl);
    color: var(--black);
    position: relative;
}

.section-title:after {
    content: '';
    position: absolute;
    bottom: -10px;
    left: 50%;
    transform: translateX(-50%);
    width: 60px;
    height: 4px;
    background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
    border-radius: 2px;
}

@media (max-width: 768px) {
    .hero-section {
        height: 50vh;
        min-height: 250px;
    }
    
    .hero-carousel .carousel-item {
        height: 50vh;
        min-height: 250px;
    }

    .hero-title {
        font-size: 2.5rem;
    }

    .hero-subtitle {
        font-size: 1.125rem;
    }

    .hero-cta {
        flex-direction: column;
        gap: var(--spacing-md);
    }

    .hero-cta .btn {
        width: 100%;
        max-width: 300px;
    }

    .hero-carousel .carousel-control-prev,
    .hero-carousel .carousel-control-next {
        width: 50px;
        height: 50px;
    }

    .hero-carousel .carousel-control-prev {
        left: 15px;
    }

    .hero-carousel .carousel-control-next {
        right: 15px;
    }

    .hero-carousel .carousel-indicators {
        bottom: 20px;
    }

    .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: var(--spacing-md);
    }

    .filter-sidebar {
        position: static;
        margin-bottom: var(--spacing-md);
    }
}

@media (max-width: 576px) {
    .hero-section {
        height: 50vh;
        min-height: 200px;
    }
    
    .hero-carousel .carousel-item {
        height: 50vh;
        min-height: 200px;
    }

    .hero-title {
        font-size: 2rem;
    }

    .hero-subtitle {
        font-size: 1rem;
    }

    .hero-content {
        padding: 0 var(--spacing-md);
    }

    .hero-carousel .carousel-control-prev,
    .hero-carousel .carousel-control-next {
        width: 40px;
        height: 40px;
    }

    .hero-carousel .carousel-control-prev-icon,
    .hero-carousel .carousel-control-next-icon {
        width: 16px;
        height: 16px;
    }

    .products-grid {
        grid-template-columns: 1fr;
    }
}

/* ===== HERO ANIMATIONS ===== */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slideInFromLeft {
    from {
        opacity: 0;
        transform: translateX(-50px);
    }

    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes slideInFromRight {
    from {
        opacity: 0;
        transform: translateX(50px);
    }

    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.fade-in-up {
    animation: fadeInUp 0.8s ease-out;
}

.slide-in-left {
    animation: slideInFromLeft 0.8s ease-out;
}

.slide-in-right {
    animation: slideInFromRight 0.8s ease-out;
}
</style>
<?php
$brands = isset($_GET['b']) ? json_decode(urldecode($_GET['b'])) : array();
?>
<!-- Hero Section with Carousel Background -->
<section class="hero-section">
    <!-- Hero Carousel -->
    <div class="hero-carousel">
        <div id="heroCarousel" class="carousel slide" data-ride="carousel" data-interval="5000">
            <div class="carousel-inner">
                <?php
                $upload_path = "uploads/banner";
                if (is_dir(base_app . $upload_path)):
                    $file = scandir(base_app . $upload_path);
                    $_i = 0;
                    foreach ($file as $img):
                        if (in_array($img, array('.', '..')))
                            continue;
                        $_i++;
                        ?>
                <div class="carousel-item <?php echo $_i == 1 ? "active" : '' ?>">
                    <img src="<?php echo validate_image($upload_path . '/' . $img) ?>" class="d-block w-100"
                        alt="<?php echo $img ?>">
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Carousel Controls -->
            <button class="carousel-control-prev" type="button" data-target="#heroCarousel" data-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-target="#heroCarousel" data-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>

            <!-- Carousel Indicators -->
            <ol class="carousel-indicators">
                <?php
                if (is_dir(base_app . $upload_path)):
                    $file = scandir(base_app . $upload_path);
                    $_i = 0;
                    foreach ($file as $img):
                        if (in_array($img, array('.', '..')))
                            continue;
                        $_i++;
                        ?>
                <li data-target="#heroCarousel" data-slide-to="<?php echo $_i - 1 ?>"
                    class="<?php echo $_i == 1 ? "active" : '' ?>"></li>
                <?php endforeach; ?>
                <?php endif; ?>
            </ol>
        </div>
    </div>

    <!-- Hero Overlay -->
    <div class="hero-overlay"></div>

    <!-- Hero Content -->
    <div class="container">
        <div class="hero-content">
            <h1 class="hero-title">Fresh Poultry Products</h1>
            <p class="hero-subtitle">Premium quality chickens and eggs from our smart poultry farm</p>
            <div class="hero-cta">
                <a href="#products" class="btn btn-secondary btn-lg">
                    <i class="fas fa-shopping-cart"></i> Shop Now
                </a>
                <a href="./?p=about" class="btn btn-outline-light btn-lg">
                    <i class="fas fa-info-circle"></i> Learn More
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Main Content -->
<section class="py-5" id="products">
    <div class="container">
        <div class="row">
            <!-- Filter Sidebar -->
            <div class="col-lg-3">
                <div class="filter-sidebar">
                    <h4 class="filter-title">
                        <i class="fas fa-filter"></i> Filter by Breed
                    </h4>
                    <div class="filter-item">
                        <label for="brandAll">
                            <input type="checkbox" id="brandAll">
                            <span>All Breeds</span>
                        </label>
                    </div>
                    <?php
                    $qry = $conn->query("SELECT * FROM brands where status =1 order by name asc");
                    while ($row = $qry->fetch_assoc()):
                        ?>
                    <div class="filter-item">
                        <label for="brand-item-<?php echo $row['id'] ?>">
                            <input type="checkbox" id="brand-item-<?php echo $row['id'] ?>"
                                <?php echo in_array($row['id'], $brands) ? "checked" : "" ?> class="brand-item"
                                value="<?php echo $row['id'] ?>">
                            <span><?php echo $row['name'] ?></span>
                        </label>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <!-- Products Section -->
            <div class="col-lg-9">
                <!-- Products Section -->
                <h2 class="section-title">Our Products</h2>
                <div class="products-grid">
                    <?php
                    $where = "";
                    if (count($brands) > 0)
                        $where = " and p.brand_id in (" . implode(",", $brands) . ") ";
                    $products = $conn->query("SELECT p.*,b.name as bname FROM `products` p inner join brands b on p.brand_id = b.id where p.status = 1 {$where} order by rand() ");
                    while ($row = $products->fetch_assoc()):
                        $upload_path = base_app . '/uploads/product_' . $row['id'];
                        $img = "";
                        if (is_dir($upload_path)) {
                            $fileO = scandir($upload_path);
                            if (isset($fileO[2]))
                                $img = "uploads/product_" . $row['id'] . "/" . $fileO[2];
                            // var_dump($fileO);
                        }
                        foreach ($row as $k => $v) {
                            $row[$k] = trim(stripslashes($v));
                        }
                        $inventory = $conn->query("SELECT * FROM inventory where product_id = " . $row['id']);
                        $inv = array();
                        while ($ir = $inventory->fetch_assoc()) {
                            $inv[] = number_format($ir['price']);
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
                                    <?php foreach ($inv as $k => $v): ?>
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
        </div>
    </div>
    </div>
</section>
<script>
// Enhanced carousel functionality
$(document).ready(function() {
    // Initialize hero carousel with enhanced settings
    $('#heroCarousel').carousel({
        interval: 5000,
        pause: 'hover',
        wrap: true
    });

    // Add smooth scrolling for anchor links
    $('a[href^="#"]').on('click', function(event) {
        var target = $(this.getAttribute('href'));
        if (target.length) {
            event.preventDefault();
            $('html, body').stop().animate({
                scrollTop: target.offset().top - 80
            }, 1000);
        }
    });

    // Add parallax effect to hero section (optional)
    $(window).scroll(function() {
        var scrolled = $(this).scrollTop();
        var parallax = $('.hero-carousel');
        var speed = scrolled * 0.5;
        parallax.css('transform', 'translateY(' + speed + 'px)');
    });

    // Add fade-in animation to hero content
    $('.hero-content').addClass('fade-in-up');

    // Add staggered animation to CTA buttons
    $('.hero-cta .btn').each(function(index) {
        $(this).css('animation-delay', (index * 0.2) + 's');
        $(this).addClass('fade-in-up');
    });

    // Add hover effect to carousel controls
    $('.hero-carousel .carousel-control-prev, .hero-carousel .carousel-control-next').hover(
        function() {
            $(this).css('transform', 'translateY(-50%) scale(1.1)');
        },
        function() {
            $(this).css('transform', 'translateY(-50%) scale(1)');
        }
    );
});

// Product filtering functions
function _filter() {
    var brands = []
    $('.brand-item:checked').each(function() {
        brands.push($(this).val())
    })
    _b = JSON.stringify(brands)
    var checked = $('.brand-item:checked').length
    var total = $('.brand-item').length
    if (checked == total)
        location.href = "./?";
    else
        location.href = "./?b=" + encodeURI(_b);
}

function check_filter() {
    var checked = $('.brand-item:checked').length
    var total = $('.brand-item').length
    if (checked == total) {
        $('#brandAll').attr('checked', true)
    } else {
        $('#brandAll').attr('checked', false)
    }
    if ('<?php echo isset($_GET['b']) ?>' == '')
        $('#brandAll,.brand-item').attr('checked', true)
}

$(function() {
    check_filter()
    $('#brandAll').change(function() {
        if ($(this).is(':checked') == true) {
            $('.brand-item').attr('checked', true)
        } else {
            $('.brand-item').attr('checked', false)
        }
        _filter()
    })
    $('.brand-item').change(function() {
        _filter()
    })
})
</script>