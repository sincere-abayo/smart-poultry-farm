
<style>
    .carousel-item>img{
        object-fit:fill !important;
    }
    #carouselExampleControls .carousel-inner{
        height:280px !important;
    }
</style>
<?php
// Get brands for filtering
$brands = isset($_GET['b']) ? json_decode(urldecode($_GET['b'])) : array();
?>

<!-- Hero Section -->
<section id="home" class="hero-section">
    <div class="container">
        <div class="hero-content" data-aos="fade-up" data-aos-duration="1000">
            <h1 class="hero-title">Fresh, Healthy Poultry Products</h1>
            <p class="hero-subtitle">From our family-owned farm to your table. Premium Sasso & Kuroiler chickens, farm-fresh eggs, and expert care.</p>
            <div class="hero-buttons">
                <a href="#products" class="btn btn-primary btn-lg">
                    <i class="bi bi-shop me-2"></i>Shop Now
                </a>
                <a href="#about" class="btn btn-outline-primary btn-lg">
                    <i class="bi bi-info-circle me-2"></i>Learn More
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features-section">
    <div class="container">
        <div class="section-title" data-aos="fade-up">
            <h2>Why Choose Our Farm?</h2>
            <p>We're committed to providing the highest quality poultry products with sustainable farming practices</p>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-house-heart"></i>
                    </div>
                    <h4>Family Owned</h4>
                    <p>Three generations of poultry farming expertise, treating every chicken like family.</p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-leaf"></i>
                    </div>
                    <h4>Organic Practices</h4>
                    <p>No antibiotics, natural feed, free-range chickens raised in healthy environments.</p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="300">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-truck"></i>
                    </div>
                    <h4>Fresh Delivery</h4>
                    <p>Same-day delivery to your doorstep, ensuring maximum freshness and quality.</p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="400">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h4>Quality Guarantee</h4>
                    <p>100% satisfaction guaranteed. We stand behind every product we deliver.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Statistics Section -->
<section class="stats-section">
    <div class="container">
        <div class="row">
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="stat-item">
                    <div class="stat-number" data-count="500">0</div>
                    <div class="stat-label">Happy Customers</div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="stat-item">
                    <div class="stat-number" data-count="1000">0</div>
                    <div class="stat-label">Chickens Raised</div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="300">
                <div class="stat-item">
                    <div class="stat-number" data-count="50">0</div>
                    <div class="stat-label">Years Experience</div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="400">
                <div class="stat-item">
                    <div class="stat-number" data-count="24">0</div>
                    <div class="stat-label">Hours Support</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Products Section -->
<section id="products" class="products-section">
    <div class="container">
        <div class="section-title" data-aos="fade-up">
            <h2>Our Premium Products</h2>
            <p>Discover our selection of high-quality poultry products</p>
        </div>
        
        <!-- Brand Filter -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="brand-filter" data-aos="fade-up">
                    <h5 class="mb-3"><i class="bi bi-funnel me-2"></i>Filter by Breed:</h5>
                    <div class="d-flex flex-wrap gap-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="brandAll" checked>
                            <label class="form-check-label" for="brandAll">
                                All Breeds
                            </label>
                        </div>
                        <?php 
                        $qry = $conn->query("SELECT * FROM brands where status =1 order by name asc");
                        while($row=$qry->fetch_assoc()):
                        ?>
                        <div class="form-check">
                            <input class="form-check-input brand-item" type="checkbox" 
                                   id="brand-item-<?php echo $row['id'] ?>" 
                                   value="<?php echo $row['id'] ?>"
                                   <?php echo in_array($row['id'],$brands) ? "checked" : "" ?>>
                            <label class="form-check-label" for="brand-item-<?php echo $row['id'] ?>">
                                <?php echo $row['name'] ?>
                            </label>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row g-4" id="products-container">
            <?php 
                $where = "";
                if(count($brands)>0)
                $where = " and p.brand_id in (".implode(",",$brands).") " ;
                $products = $conn->query("SELECT p.*,b.name as bname FROM `products` p inner join brands b on p.brand_id = b.id where p.status = 1 {$where} order by rand() LIMIT 6");
                while($row = $products->fetch_assoc()):
                    $upload_path = base_app.'/uploads/product_'.$row['id'];
                    $img = "";
                    if(is_dir($upload_path)){
                        $fileO = scandir($upload_path);
                        if(isset($fileO[2]))
                            $img = "uploads/product_".$row['id']."/".$fileO[2];
                    }
                    foreach($row as $k=> $v){
                        $row[$k] = trim(stripslashes($v));
                    }
                    $inventory = $conn->query("SELECT * FROM inventory where product_id = ".$row['id']);
                    $inv = array();
                    while($ir = $inventory->fetch_assoc()){
                        $inv[] = number_format($ir['price']);
                    }
            ?>
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="product-card">
                    <div class="position-relative">
                        <img src="<?php echo validate_image($img) ?>" 
                             alt="<?php echo $row['name'] ?>" class="product-image">
                        <div class="product-badge">Premium</div>
                    </div>
                    <div class="product-body">
                        <h5 class="product-title"><?php echo $row['name'] ?></h5>
                        <div class="product-price">₱<?php echo implode(', ', $inv) ?></div>
                        <div class="product-breed">Breed: <?php echo $row['bname'] ?></div>
                        <a href=".?p=view_product&id=<?php echo md5($row['id']) ?>" class="btn btn-primary w-100">
                            <i class="bi bi-eye me-2"></i>View Details
                        </a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        
        <div class="text-center mt-5" data-aos="fade-up">
            <a href="products.php" class="btn btn-outline-primary btn-lg">
                <i class="bi bi-grid me-2"></i>View All Products
            </a>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="testimonials-section">
    <div class="container">
        <div class="section-title" data-aos="fade-up">
            <h2>What Our Customers Say</h2>
            <p>Real feedback from satisfied customers about our products and service</p>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="testimonial-card">
                    <img src="https://images.unsplash.com/photo-1494790108755-2616b612b786?ixlib=rb-4.0.3&auto=format&fit=crop&w=200&q=80" 
                         alt="Customer" class="testimonial-avatar">
                    <div class="testimonial-stars">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <p class="testimonial-text">"The quality of their chickens is outstanding! Fresh, healthy, and delivered right to my door. Highly recommended!"</p>
                    <div class="testimonial-author">- Maria Santos</div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="testimonial-card">
                    <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?ixlib=rb-4.0.3&auto=format&fit=crop&w=200&q=80" 
                         alt="Customer" class="testimonial-avatar">
                    <div class="testimonial-stars">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <p class="testimonial-text">"Best eggs I've ever tasted! You can really tell the difference when they come from a well-cared-for farm."</p>
                    <div class="testimonial-author">- Juan Dela Cruz</div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                <div class="testimonial-card">
                    <img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?ixlib=rb-4.0.3&auto=format&fit=crop&w=200&q=80" 
                         alt="Customer" class="testimonial-avatar">
                    <div class="testimonial-stars">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <p class="testimonial-text">"Professional service and excellent communication. They really care about their customers and product quality."</p>
                    <div class="testimonial-author">- Ana Rodriguez</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- About Section -->
<section id="about" class="features-section">
    <div class="container">
        <div class="section-title" data-aos="fade-up">
            <h2>About Our Farm</h2>
            <p>Learn more about our commitment to quality and sustainable farming</p>
        </div>
        
        <div class="row align-items-center">
            <div class="col-lg-6" data-aos="fade-right">
                <img src="https://images.unsplash.com/photo-1559827260-dc66d52bef19?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
                     alt="Our Farm" class="img-fluid rounded-3 shadow-custom">
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <h3 class="mb-4">Our Story</h3>
                <p class="lead mb-4">Started as a small family farm over 50 years ago, we've grown into a trusted name in poultry farming while maintaining our commitment to quality and sustainability.</p>
                
                <div class="row g-3">
                    <div class="col-6">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-check-circle-fill text-success me-2 fs-4"></i>
                            <span>Organic Feed</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-check-circle-fill text-success me-2 fs-4"></i>
                            <span>Free Range</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-check-circle-fill text-success me-2 fs-4"></i>
                            <span>No Antibiotics</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-check-circle-fill text-success me-2 fs-4"></i>
                            <span>Expert Care</span>
                        </div>
                    </div>
                </div>
                
                <a href="about.php" class="btn btn-primary mt-4">
                    <i class="bi bi-info-circle me-2"></i>Learn More
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section id="contact" class="stats-section">
    <div class="container">
        <div class="section-title text-center" data-aos="fade-up">
            <h2 class="text-white">Get In Touch</h2>
            <p class="text-white-50">Ready to experience the best poultry products? Contact us today!</p>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="row g-4">
                    <div class="col-md-4 text-center" data-aos="fade-up" data-aos-delay="100">
                        <div class="contact-item">
                            <i class="bi bi-geo-alt fs-1 text-secondary-green mb-3"></i>
                            <h5 class="text-white">Visit Us</h5>
                            <p class="text-white-50">123 Farm Road<br>Poultry City, PC 1234</p>
                        </div>
                    </div>
                    
                    <div class="col-md-4 text-center" data-aos="fade-up" data-aos-delay="200">
                        <div class="contact-item">
                            <i class="bi bi-telephone fs-1 text-secondary-green mb-3"></i>
                            <h5 class="text-white">Call Us</h5>
                            <p class="text-white-50">+63 912 345 6789<br>Mon-Sat: 8AM-6PM</p>
                        </div>
                    </div>
                    
                    <div class="col-md-4 text-center" data-aos="fade-up" data-aos-delay="300">
                        <div class="contact-item">
                            <i class="bi bi-envelope fs-1 text-secondary-green mb-3"></i>
                            <h5 class="text-white">Email Us</h5>
                            <p class="text-white-50">info@smartpoultry.com<br>orders@smartpoultry.com</p>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-5" data-aos="fade-up">
                    <a href="mailto:info@smartpoultry.com" class="btn btn-secondary-green btn-lg">
                        <i class="bi bi-envelope me-2"></i>Send Message
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Include the landing page CSS and JS -->
<link rel="stylesheet" href="<?php echo base_url ?>assets/css/landing-page.css">
<script src="<?php echo base_url ?>assets/js/landing-page.js"></script>

<script>
    // Brand filtering functionality
    function _filter(){
        var brands = []
        $('.brand-item:checked').each(function(){
            brands.push($(this).val())
        })
        _b = JSON.stringify(brands)
        var checked = $('.brand-item:checked').length
        var total = $('.brand-item').length
        if(checked == total)
            location.href="./?";
        else
            location.href="./?b="+encodeURI(_b);
    }
    
    function check_filter(){
        var checked = $('.brand-item:checked').length
        var total = $('.brand-item').length
        if(checked == total){
            $('#brandAll').attr('checked',true)
        }else{
            $('#brandAll').attr('checked',false)
        }
        if('<?php echo isset($_GET['b']) ?>' == '')
            $('#brandAll,.brand-item').attr('checked',true)
    }
    
    $(function(){
        check_filter()
        $('#brandAll').change(function(){
            if($(this).is(':checked') == true){
                $('.brand-item').attr('checked',true)
            }else{
                $('.brand-item').attr('checked',false)
            }
            _filter()
        })
        $('.brand-item').change(function(){
            _filter()
        })
    })
</script>