<style>
  .dashboard-container {
    max-width: 1400px;
    margin: 0 auto;
    background: #f8f9fa;
    min-height: 100vh;
    padding: 20px;
  }

  .dashboard-header {
    background: linear-gradient(135deg, #2e7d32 0%, #4caf50 100%);
    color: #ffffff;
    padding: 40px 0;
    border-radius: 12px;
    text-align: center;
    margin-bottom: 30px;
    box-shadow: 0 4px 15px rgba(46, 125, 50, 0.3);
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

  @media (max-width: 768px) {
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
</style>

<div class="dashboard-container">
  <div class="dashboard-header">
    <h1 class="dashboard-title">Welcome to <?php echo $_settings->info('name') ?></h1>
    <p class="dashboard-subtitle">Admin Dashboard - Manage your poultry farm operations</p>
  </div>

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
  <div class="banner-section">
    <h2 class="banner-title">Farm Gallery</h2>
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
      <div id="tourCarousel" class="carousel slide banner-carousel" data-ride="carousel" data-interval="3000">
        <div class="carousel-inner">
          <?php foreach ($files as $k => $img): ?>
            <div class="carousel-item <?php echo $k == 0 ? 'active' : '' ?>">
              <img class="d-block w-100" src="<?php echo $img ?>" alt="Farm Gallery Image <?php echo $k + 1 ?>">
          </div>
          <?php endforeach; ?>
      </div>
      <a class="carousel-control-prev" href="#tourCarousel" role="button" data-slide="prev">
          <span class="carousel-control-prev-icon" aria-hidden="true"></span>
          <span class="sr-only">Previous</span>
      </a>
      <a class="carousel-control-next" href="#tourCarousel" role="button" data-slide="next">
          <span class="carousel-control-next-icon" aria-hidden="true"></span>
          <span class="sr-only">Next</span>
      </a>
      </div>
    <?php else: ?>
      <div class="text-center py-5">
        <i class="fas fa-images fa-3x text-muted mb-3"></i>
        <p class="text-muted">No banner images available</p>
      </div>
    <?php endif; ?>
  </div>
</div>