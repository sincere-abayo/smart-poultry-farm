<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary bg-purple elevation-4 sidebar-no-expand">
  <!-- Brand Logo -->
  <a href="<?php echo base_url ?>admin" class="brand-link bg-maroon text-sm">
    <img src="<?php echo validate_image($_settings->info('logo'))?>" alt="Store Logo" class="brand-image img-circle elevation-3" style="opacity: .8;width: 1.5rem;height: 1.5rem;max-height: unset">
    <span class="brand-text font-weight-light"><?php echo $_settings->info('short_name') ?></span>
  </a>

  <!-- Sidebar -->
  <div class="sidebar">
    <div class="clearfix"></div>

    <!-- Sidebar Menu -->
    <nav class="mt-4">
      <ul class="nav nav-pills nav-sidebar flex-column text-sm nav-compact nav-flat nav-child-indent nav-collapse-hide-child" data-widget="treeview" role="menu" data-accordion="false">
        
        <li class="nav-item dropdown">
          <a href="./" class="nav-link nav-home">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>Dashboard</p>
          </a>
        </li>

        <li class="nav-item dropdown">
          <a href="<?php echo base_url ?>admin/?page=product" class="nav-link nav-product">
            <i class="nav-icon fas fa-mobile-alt"></i>
            <p>Product List</p>
          </a>
        </li>

        <li class="nav-item dropdown">
          <a href="<?php echo base_url ?>admin/?page=inventory" class="nav-link nav-inventory">
            <i class="nav-icon fas fa-clipboard-list"></i>
            <p>Inventory List</p>
          </a>
        </li>

        <li class="nav-item dropdown">
          <a href="<?php echo base_url ?>admin/?page=orders" class="nav-link nav-orders">
            <i class="nav-icon fas fa-list"></i>
            <p>Order List</p>
          </a>
        </li>

        <li class="nav-item dropdown">
          <a href="<?php echo base_url ?>admin/?page=expenses" class="nav-link nav-expenses">
            <i class="nav-icon fas fa-money-bill-wave"></i>
            <p>Expenses</p>
          </a>
        </li>

        <!-- Vaccination Menu -->
        <li class="nav-item dropdown">
          <a href="<?php echo base_url ?>admin/?page=vaccination" class="nav-link nav-vaccination">
            <i class="nav-icon fas fa-syringe"></i>
            <p>Vaccination</p>
          </a>
        </li>

        <!-- Production Menu -->
        <li class="nav-item dropdown">
          <a href="<?php echo base_url ?>admin/?page=production" class="nav-link nav-production">
            <i class="nav-icon fas fa-drumstick-bite"></i>
            <p>Production</p>
          </a>
        </li>

        <li class="nav-item dropdown">
          <a href="<?php echo base_url ?>admin/?page=sales" class="nav-link nav-sales">
            <i class="nav-icon fas fa-file"></i>
            <p>Sales Report</p>
          </a>
        </li>

        <li class="nav-header">Maintenance</li>

        <li class="nav-item dropdown">
          <a href="<?php echo base_url ?>admin/?page=maintenance/brand" class="nav-link nav-maintenance_brand">
            <i class="nav-icon fas fa-star"></i>
            <p>Breed List</p>
          </a>
        </li>

        <li class="nav-item dropdown">
          <a href="<?php echo base_url ?>admin/?page=maintenance/category" class="nav-link nav-maintenance_category">
            <i class="nav-icon fas fa-th-list"></i>
            <p>Category List</p>
          </a>
        </li>

        <!-- Sub Category List removed -->

        <li class="nav-item dropdown">
          <a href="<?php echo base_url ?>admin/?page=system_info" class="nav-link nav-system_info">
            <i class="nav-icon fas fa-cogs"></i>
            <p>Settings</p>
          </a>
        </li>
      </ul>
    </nav>
    <!-- /.sidebar-menu -->
  </div>
  <!-- /.sidebar -->
</aside>

<!-- Highlight current page script -->
<script>
  $(document).ready(function(){
    var page = '<?php echo isset($_GET['page']) ? $_GET['page'] : 'home' ?>';
    var s = '<?php echo isset($_GET['s']) ? $_GET['s'] : '' ?>';
    page = page.replace(/\//g,'_');

    if($('.nav-link.nav-'+page).length > 0){
      $('.nav-link.nav-'+page).addClass('active')
      if($('.nav-link.nav-'+page).hasClass('tree-item') == true){
        $('.nav-link.nav-'+page).closest('.nav-treeview').siblings('a').addClass('active')
        $('.nav-link.nav-'+page).closest('.nav-treeview').parent().addClass('menu-open')
      }
      if($('.nav-link.nav-'+page).hasClass('nav-is-tree') == true){
        $('.nav-link.nav-'+page).parent().addClass('menu-open')
      }
    }
    $('.nav-link.active').addClass('bg-maroon')
  })
</script>
