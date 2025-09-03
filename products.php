<?php
$title = "";
$sub_title = "";
if (isset($_GET['c']) && isset($_GET['s'])) {
    $cat_qry = $conn->query("SELECT * FROM categories where md5(id) = '{$_GET['c']}'");
    if ($cat_qry->num_rows > 0) {
        $result = $cat_qry->fetch_assoc();
        $title = $result['category'];
        $cat_description = $result['description'];
    }
    $sub_cat_qry = $conn->query("SELECT * FROM sub_categories where md5(id) = '{$_GET['s']}'");
    if ($sub_cat_qry->num_rows > 0) {
        $result = $sub_cat_qry->fetch_assoc();
        $sub_title = $result['sub_category'];
        $sub_cat_description = $result['description'];
    }
} elseif (isset($_GET['c'])) {
    $cat_qry = $conn->query("SELECT * FROM categories where md5(id) = '{$_GET['c']}'");
    if ($cat_qry->num_rows > 0) {
        $result = $cat_qry->fetch_assoc();
        $title = $result['category'];
        $cat_description = $result['description'];
    }
} elseif (isset($_GET['s'])) {
    $sub_cat_qry = $conn->query("SELECT * FROM sub_categories where md5(id) = '{$_GET['s']}'");
    if ($sub_cat_qry->num_rows > 0) {
        $result = $sub_cat_qry->fetch_assoc();
        $sub_title = $result['sub_category'];
        $sub_cat_description = $result['description'];
    }
}
?>
<style>
    .products-header {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
        color: var(--white);
        padding: var(--spacing-xxl) 0;
        position: relative;
        overflow: hidden;
    }

    .products-header:before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="50" cy="10" r="0.5" fill="rgba(255,255,255,0.1)"/><circle cx="10" cy="60" r="0.5" fill="rgba(255,255,255,0.1)"/><circle cx="90" cy="40" r="0.5" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
        opacity: 0.3;
        z-index: 1;
    }

    .products-header-content {
        position: relative;
        z-index: 2;
    }

    .products-title {
        font-size: 3rem;
        font-weight: 700;
        margin-bottom: var(--spacing-md);
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
    }

    .products-subtitle {
        font-size: 1.25rem;
        opacity: 0.9;
        margin-bottom: 0;
    }

    .search-results-header {
        background: var(--light-gray);
        padding: var(--spacing-xl) 0;
        text-align: center;
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
    }

    .search-results-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--black);
        margin: 0;
    }

    .search-query {
        color: var(--primary-color);
        font-weight: 700;
    }

    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: var(--spacing-lg);
        padding: var(--spacing-xl) 0;
    }

    .no-products {
        text-align: center;
        padding: var(--spacing-xxl) 0;
        color: var(--medium-gray);
    }

    .no-products-icon {
        font-size: 4rem;
        color: var(--light-gray);
        margin-bottom: var(--spacing-lg);
    }

    .no-products-title {
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: var(--spacing-sm);
    }

    .no-products-text {
        font-size: 1rem;
        margin-bottom: var(--spacing-lg);
    }

    @media (max-width: 768px) {
        .products-title {
            font-size: 2.5rem;
        }

        .products-subtitle {
            font-size: 1.125rem;
        }

        .products-grid {
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: var(--spacing-md);
        }
    }

    @media (max-width: 576px) {
        .products-title {
            font-size: 2rem;
        }

        .products-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<!-- Products Header -->
<header class="products-header">
    <div class="container">
        <div class="products-header-content text-center">
            <h1 class="products-title"><?php echo $title ?: 'Our Products' ?></h1>
            <?php if ($sub_title): ?>
                <p class="products-subtitle"><?php echo $sub_title ?></p>
            <?php endif; ?>
        </div>
    </div>
</header>

<!-- Search Results Header -->
<?php if (isset($_GET['search'])): ?>
    <div class="search-results-header">
        <div class="container">
            <h2 class="search-results-title">
                Search Results for '<span class="search-query"><?php echo htmlspecialchars($_GET['search']) ?></span>'
            </h2>
        </div>
    </div>
<?php endif; ?>

<!-- Products Section -->
<section class="py-5">
    <div class="container">
        <div class="products-grid">
            <?php
            $whereData = "";
            if (isset($_GET['search']))
                $whereData = " and (p.name LIKE '%{$_GET['search']}%' or b.name LIKE '%{$_GET['search']}%' or p.specs LIKE '%{$_GET['search']}%')";
            elseif (isset($_GET['c']) && isset($_GET['s']))
                $whereData = " and (md5(category_id) = '{$_GET['c']}' and md5(sub_category_id) = '{$_GET['s']}')";
            elseif (isset($_GET['c']) && !isset($_GET['s']))
                $whereData = " and md5(category_id) = '{$_GET['c']}' ";
            elseif (isset($_GET['s']) && !isset($_GET['c']))
                $whereData = " and md5(sub_category_id) = '{$_GET['s']}' ";
            $products = $conn->query("SELECT p.*,b.name as bname FROM `products` p inner join brands b on p.brand_id = b.id where p.status = 1 {$whereData} order by rand() ");
            while ($row = $products->fetch_assoc()):
                $upload_path = base_app . '/uploads/product_' . $row['id'];
                $img = "";
                if (is_dir($upload_path)) {
                    $fileO = scandir($upload_path);
                    if (isset($fileO[2]))
                        $img = "uploads/product_" . $row['id'] . "/" . $fileO[2];
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
                        <img class="card-img-top" src="<?php echo validate_image($img) ?>" loading="lazy"
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
            <?php
            if ($products->num_rows <= 0):
                ?>
                <div class="no-products">
                    <div class="no-products-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3 class="no-products-title">No Products Found</h3>
                    <p class="no-products-text">
                        <?php if (isset($_GET['search'])): ?>
                            We couldn't find any products matching your search. Try different keywords or browse our categories.
                        <?php else: ?>
                            No products are currently available in this category. Please check back later.
                        <?php endif; ?>
                    </p>
                    <a href="./" class="btn btn-primary">
                        <i class="fas fa-home"></i> Back to Home
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include 'inc/footer.php' ?>