<style>
/* Admin Hero Section with Carousel Background */
.admin-hero-section {
    position: relative;
    height: 50vh;
    min-height: 300px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
}

.admin-hero-carousel {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1;
}

.admin-hero-carousel .carousel-item {
    height: 50vh;
    min-height: 300px;
}

.admin-hero-carousel .carousel-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center top;
    background-color: #f8f9fa;
}

.admin-hero-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, rgba(46, 125, 50, 0.8) 0%, rgba(76, 175, 80, 0.6) 100%);
    z-index: 2;
}

.admin-hero-content {
    position: relative;
    z-index: 3;
    text-align: center;
    color: #ffffff;
    max-width: 1000px;
    padding: 0 20px;
}

.admin-dashboard-title {
    font-size: 3.5rem;
    font-weight: 700;
    margin-bottom: 20px;
    text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.5);
    line-height: 1.2;
}

.admin-dashboard-subtitle {
    font-size: 1.5rem;
    margin-bottom: 40px;
    opacity: 0.95;
    text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.5);
    font-weight: 300;
}

.admin-hero-carousel .carousel-control-prev,
.admin-hero-carousel .carousel-control-next {
    width: 60px;
    height: 60px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    top: 50%;
    transform: translateY(-50%);
    opacity: 0.8;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
    border: 2px solid rgba(255, 255, 255, 0.3);
}

.admin-hero-carousel .carousel-control-prev:hover,
.admin-hero-carousel .carousel-control-next:hover {
    opacity: 1;
    background: rgba(255, 255, 255, 0.3);
    transform: translateY(-50%) scale(1.1);
}

.admin-hero-carousel .carousel-control-prev {
    left: 30px;
}

.admin-hero-carousel .carousel-control-next {
    right: 30px;
}

.admin-hero-carousel .carousel-control-prev-icon,
.admin-hero-carousel .carousel-control-next-icon {
    width: 20px;
    height: 20px;
}

/* Carousel indicators */
.admin-hero-carousel .carousel-indicators {
    bottom: 30px;
    z-index: 4;
}

.admin-hero-carousel .carousel-indicators [data-bs-target] {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.5);
    border: 2px solid rgba(255, 255, 255, 0.8);
    margin: 0 8px;
    transition: all 0.3s ease;
}

.admin-hero-carousel .carousel-indicators .active {
    background: #ffffff;
    transform: scale(1.2);
}

.dashboard-container {
    max-width: 1400px;
    margin: 0 auto;
    background: #f8f9fa;
    min-height: 100vh;
    padding: 20px;
}

.dashboard-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 10px;
    color: #ffffff;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
}

.dashboard-subtitle {
    font-size: 1.125rem;
    opacity: 0.9;
    color: #ffffff;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 25px;
    margin-bottom: 30px;
}

.stat-card {
    background: #ffffff;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    border: 1px solid #e9ecef;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #2e7d32, #4caf50);
}

.stat-card.stocks::before {
    background: linear-gradient(90deg, #d32f2f, #f44336);
}

.stat-card.pending::before {
    background: linear-gradient(90deg, #f57c00, #ff9800);
}

.stat-card.sales::before {
    background: linear-gradient(90deg, #388e3c, #4caf50);
}

.stat-card.total-orders::before {
    background: linear-gradient(90deg, #7b1fa2, #9c27b0);
}

.stat-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: #ffffff;
}

.stat-icon.stocks {
    background: linear-gradient(135deg, #d32f2f, #f44336);
}

.stat-icon.pending {
    background: linear-gradient(135deg, #f57c00, #ff9800);
}

.stat-icon.sales {
    background: linear-gradient(135deg, #388e3c, #4caf50);
}

.stat-icon.total-orders {
    background: linear-gradient(135deg, #7b1fa2, #9c27b0);
}

.stat-trend {
    font-size: 0.875rem;
    color: #4caf50;
    font-weight: 500;
}

.stat-trend.down {
    color: #f44336;
}

.stat-content {
    text-align: left;
}

.stat-label {
    font-size: 0.875rem;
    color: #6c757d;
    font-weight: 500;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-value {
    font-size: 2.5rem;
    font-weight: 700;
    color: #212529;
    line-height: 1;
    margin-bottom: 10px;
}

.stat-description {
    font-size: 0.875rem;
    color: #6c757d;
}

.banner-section {
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    padding: 30px;
    margin-top: 30px;
}

.banner-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #212529;
    margin-bottom: 20px;
    text-align: center;
}

.banner-carousel {
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.banner-carousel .carousel-item img {
    height: 300px;
    object-fit: cover;
    width: 100%;
}

.banner-carousel .carousel-control-prev,
.banner-carousel .carousel-control-next {
    width: 50px;
    height: 50px;
    background: rgba(0, 0, 0, 0.5);
    border-radius: 50%;
    top: 50%;
    transform: translateY(-50%);
}

.banner-carousel .carousel-control-prev {
    left: 20px;
}

.banner-carousel .carousel-control-next {
    right: 20px;
}

/* Admin Hero Animations */
@keyframes adminFadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.admin-fade-in-up {
    animation: adminFadeInUp 0.8s ease-out;
}

@media (max-width: 768px) {
    .admin-hero-section {
        height: 50vh;
        min-height: 250px;
    }

    .admin-hero-carousel .carousel-item {
        height: 50vh;
        min-height: 250px;
    }

    .admin-dashboard-title {
        font-size: 2.5rem;
    }

    .admin-dashboard-subtitle {
        font-size: 1.125rem;
    }

    .admin-hero-carousel .carousel-control-prev,
    .admin-hero-carousel .carousel-control-next {
        width: 50px;
        height: 50px;
    }

    .admin-hero-carousel .carousel-control-prev {
        left: 15px;
    }

    .admin-hero-carousel .carousel-control-next {
        right: 15px;
    }

    .admin-hero-carousel .carousel-indicators {
        bottom: 20px;
    }

    .stats-grid {
        grid-template-columns: 1fr;
    }

    .dashboard-title {
        font-size: 2rem;
    }

    .stat-value {
        font-size: 2rem;
    }

    .banner-carousel .carousel-item img {
        height: 200px;
    }
}

@media (max-width: 576px) {
    .admin-hero-section {
        height: 50vh;
        min-height: 200px;
    }

    .admin-hero-carousel .carousel-item {
        height: 50vh;
        min-height: 200px;
    }

    .admin-dashboard-title {
        font-size: 2rem;
    }

    .admin-dashboard-subtitle {
        font-size: 1rem;
    }

    .admin-hero-content {
        padding: 0 15px;
    }

    .admin-hero-carousel .carousel-control-prev,
    .admin-hero-carousel .carousel-control-next {
        width: 40px;
        height: 40px;
    }

    .admin-hero-carousel .carousel-control-prev-icon,
    .admin-hero-carousel .carousel-control-next-icon {
        width: 16px;
        height: 16px;
    }
}
</style>

<!-- Admin Hero Section with Carousel Background -->
<section class="admin-hero-section">
    <!-- Admin Hero Carousel -->
    <div class="admin-hero-carousel">
        <div id="adminHeroCarousel" class="carousel slide" data-ride="carousel" data-interval="5000">
            <div class="carousel-inner">
                <?php
        $files = array();
        $fopen = scandir(base_app . 'uploads/banner');
        foreach ($fopen as $fname) {
          if (in_array($fname, array('.', '..')))
            continue;
          $files[] = validate_image('uploads/banner/' . $fname);
        }
        ?>
                <?php if (!empty($files)): ?>
                <?php foreach ($files as $k => $img): ?>
                <div class="carousel-item <?php echo $k == 0 ? 'active' : '' ?>">
                    <img class="d-block w-100" src="<?php echo $img ?>" alt="Farm Gallery Image <?php echo $k + 1 ?>">
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <!-- Fallback if no images -->
                <div class="carousel-item active">
                    <div
                        style="width: 100%; height: 100%; background: linear-gradient(135deg, #2e7d32 0%, #4caf50 100%); display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-images fa-5x text-white opacity-50"></i>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Carousel Controls -->
            <button class="carousel-control-prev" type="button" data-target="#adminHeroCarousel" data-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-target="#adminHeroCarousel" data-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>

            <!-- Carousel Indicators -->
            <?php if (!empty($files)): ?>
            <ol class="carousel-indicators">
                <?php foreach ($files as $k => $img): ?>
                <li data-target="#adminHeroCarousel" data-slide-to="<?php echo $k ?>"
                    class="<?php echo $k == 0 ? "active" : '' ?>"></li>
                <?php endforeach; ?>
            </ol>
            <?php endif; ?>
        </div>
    </div>

    <!-- Admin Hero Overlay -->
    <div class="admin-hero-overlay"></div>

    <!-- Admin Hero Content -->
    <div class="container">
        <div class="admin-hero-content">
            <h1 class="admin-dashboard-title">Welcome to <?php echo $_settings->info('name') ?></h1>
            <p class="admin-dashboard-subtitle">Admin Dashboard - Manage your poultry farm operations</p>
        </div>
    </div>
</section>

<div class="dashboard-container">

    <div class="stats-grid">
        <div class="stat-card stocks">
            <div class="stat-header">
                <div class="stat-icon stocks">
                    <i class="fas fa-boxes"></i>
                </div>
                <div class="stat-trend">
                    <i class="fas fa-arrow-up"></i> +5%
                </div>
            </div>
            <div class="stat-content">
                <div class="stat-label">Total Stock</div>
                <div class="stat-value">
                    <?php
          $inv = $conn->query("SELECT sum(quantity) as total FROM inventory ")->fetch_assoc()['total'];
          $sales = $conn->query("SELECT sum(quantity) as total FROM order_list where order_id in (SELECT order_id FROM sales) ")->fetch_assoc()['total'];
          echo number_format($inv - $sales);
          ?>
                </div>
                <div class="stat-description">Available inventory items</div>
            </div>
        </div>

        <div class="stat-card pending">
            <div class="stat-header">
                <div class="stat-icon pending">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-trend">
                    <i class="fas fa-arrow-up"></i> +12%
                </div>
            </div>
            <div class="stat-content">
                <div class="stat-label">Pending Orders</div>
                <div class="stat-value">
                    <?php
          $pending = $conn->query("SELECT count(id) as total FROM `orders` where status = '0' ")->fetch_assoc()['total'];
          echo number_format($pending);
          ?>
                </div>
                <div class="stat-description">Orders awaiting processing</div>
            </div>
        </div>

        <div class="stat-card sales">
            <div class="stat-header">
                <div class="stat-icon sales">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-trend">
                    <i class="fas fa-arrow-up"></i> +8%
                </div>
            </div>
            <div class="stat-content">
                <div class="stat-label">Today's Sales</div>
                <div class="stat-value">₱<?php
        $sales = $conn->query("SELECT sum(amount) as total FROM `orders` where date(date_created) = '" . date('Y-m-d') . "' ")->fetch_assoc()['total'];
        echo number_format($sales, 2);
        ?></div>
                <div class="stat-description">Revenue for today</div>
            </div>
        </div>

        <div class="stat-card total-orders">
            <div class="stat-header">
                <div class="stat-icon total-orders">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-trend">
                    <i class="fas fa-arrow-up"></i> +15%
                </div>
            </div>
            <div class="stat-content">
                <div class="stat-label">Total Orders</div>
                <div class="stat-value">
                    <?php
          $total_orders = $conn->query("SELECT count(id) as total FROM `orders`")->fetch_assoc()['total'];
          echo number_format($total_orders);
          ?>
                </div>
                <div class="stat-description">All-time orders</div>
            </div>
        </div>
    </div>
</div>

<script>
// Enhanced admin carousel functionality
$(document).ready(function() {
    // Initialize admin hero carousel with enhanced settings
    $('#adminHeroCarousel').carousel({
        interval: 5000,
        pause: 'hover',
        wrap: true
    });

    // Add fade-in animation to admin hero content
    $('.admin-hero-content').addClass('admin-fade-in-up');

    // Add hover effect to admin carousel controls
    $('.admin-hero-carousel .carousel-control-prev, .admin-hero-carousel .carousel-control-next').hover(
        function() {
            $(this).css('transform', 'translateY(-50%) scale(1.1)');
        },
        function() {
            $(this).css('transform', 'translateY(-50%) scale(1)');
        }
    );

    // Add parallax effect to admin hero section (optional)
    $(window).scroll(function() {
        var scrolled = $(this).scrollTop();
        var parallax = $('.admin-hero-carousel');
        var speed = scrolled * 0.3;
        parallax.css('transform', 'translateY(' + speed + 'px)');
    });

    // Add smooth scrolling for internal links
    $('a[href^="#"]').on('click', function(event) {
        var target = $(this.getAttribute('href'));
        if (target.length) {
            event.preventDefault();
            $('html, body').stop().animate({
                scrollTop: target.offset().top - 80
            }, 1000);
        }
    });

    // Add scroll-triggered animations for stats cards
    $(window).scroll(function() {
        $('.stat-card').each(function() {
            var elementTop = $(this).offset().top;
            var elementBottom = elementTop + $(this).outerHeight();
            var viewportTop = $(window).scrollTop();
            var viewportBottom = viewportTop + $(window).height();

            if (elementBottom > viewportTop && elementTop < viewportBottom) {
                $(this).addClass('admin-fade-in-up');
            }
        });
    });
});
</script>