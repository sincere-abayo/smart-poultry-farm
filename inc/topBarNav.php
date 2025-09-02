<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
  <div class="container">
    <button class="navbar-toggler border-0" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
      aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <a class="navbar-brand d-flex align-items-center" href="./">
      <img src="<?php echo validate_image($_settings->info('logo')) ?>" width="40" height="40"
        class="d-inline-block align-top me-2" alt="" loading="lazy">
      <span class="fw-bold text-primary"><?php echo $_settings->info('short_name') ?></span>
    </a>

    <form class="d-flex" id="search-form">
      <div class="input-group">
        <input class="form-control" type="search" placeholder="Search products..." aria-label="Search" name="search"
          value="<?php echo isset($_GET['search']) ? $_GET['search'] : "" ?>">
        <button class="btn btn-outline-primary" type="submit">
          <i class="fas fa-search"></i>
        </button>
      </div>
    </form>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
        <li class="nav-item"><a class="nav-link" aria-current="page" href="./">Home</a></li>
        <?php
        $cat_qry = $conn->query("SELECT * FROM categories where status = 1  limit 3");
        $count_cats = $conn->query("SELECT * FROM categories where status = 1 ")->num_rows;
        while ($crow = $cat_qry->fetch_assoc()):
          $sub_qry = $conn->query("SELECT * FROM sub_categories where status = 1 and parent_id = '{$crow['id']}'");
          if ($sub_qry->num_rows <= 0):
            ?>
            <li class="nav-item"><a class="nav-link" aria-current="page"
                href="./?p=products&c=<?php echo md5($crow['id']) ?>"><?php echo $crow['category'] ?></a></li>

          <?php else: ?>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" id="navbarDropdown<?php echo $crow['id'] ?>" href="#" role="button"
                data-toggle="dropdown" aria-expanded="false"><?php echo $crow['category'] ?></a>
              <ul class="dropdown-menu  p-0" aria-labelledby="navbarDropdown<?php echo $crow['id'] ?>">
                <?php while ($srow = $sub_qry->fetch_assoc()): ?>
                  <li><a class="dropdown-item border-bottom"
                      href="./?p=products&c=<?php echo md5($crow['id']) ?>&s=<?php echo md5($srow['id']) ?>"><?php echo $srow['sub_category'] ?></a>
                  </li>
                <?php endwhile; ?>
              </ul>
            </li>
          <?php endif; ?>
        <?php endwhile; ?>
        <?php if ($count_cats > 3): ?>
          <li class="nav-item"><a class="nav-link" href="./?p=view_categories">All Categories</a></li>
        <?php endif; ?>
        <li class="nav-item"><a class="nav-link" href="./?p=about">About</a></li>
      </ul>
      <div class="d-flex align-items-center">
        <?php if (!isset($_SESSION['userdata']['id'])): ?>
          <button class="btn btn-outline-primary me-2" id="login-btn" type="button">
            <i class="fas fa-sign-in-alt"></i> Login
          </button>
        <?php else: ?>
          <a class="nav-link me-3" href="./?p=cart">
            <i class="fas fa-shopping-cart"></i>
            <span class="badge bg-primary ms-1" id="cart-count">
              <?php
              if (isset($_SESSION['userdata']['id'])):
                $count = $conn->query("SELECT SUM(quantity) as items from `cart` where client_id =" . $_settings->userdata('id'))->fetch_assoc()['items'];
                echo ($count > 0 ? $count : 0);
              else:
                echo "0";
              endif;
              ?>
            </span>
          </a>

          <div class="dropdown">
            <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
              <i class="fas fa-user"></i> Hi, <?php echo $_settings->userdata('firstname') ?>!
            </a>
            <div class="dropdown-menu dropdown-menu-right">
              <a href="./?p=my_account" class="dropdown-item">
                <i class="fas fa-user-circle"></i> My Account
              </a>
              <a href="logout.php" class="dropdown-item">
                <i class="fas fa-sign-out-alt"></i> Logout
              </a>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>
<script>
  $(function () {
    $('#login-btn').click(function () {
      uni_modal("", "login.php")
    })
    $('#navbarResponsive').on('show.bs.collapse', function () {
      $('#mainNav').addClass('navbar-shrink')
    })
    $('#navbarResponsive').on('hidden.bs.collapse', function () {
      if ($('body').offset.top == 0)
        $('#mainNav').removeClass('navbar-shrink')
    })
  })

  $('#search-form').submit(function (e) {
    e.preventDefault()
    var sTxt = $('[name="search"]').val()
    if (sTxt != '')
      location.href = './?p=products&search=' + sTxt;
  })
</script>