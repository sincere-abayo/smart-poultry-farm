<style>
.about-container {
    max-width: 1200px;
    margin: 0 auto;
}

.about-hero {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
    color: var(--white);
    padding: var(--spacing-xxl) 0;
    text-align: center;
    margin-bottom: var(--spacing-xl);
    border-radius: var(--radius-lg);
}

.about-title {
    font-size: 3rem;
    font-weight: 700;
    margin-bottom: var(--spacing-md);
}

.about-subtitle {
    font-size: 1.25rem;
    opacity: 0.9;
    max-width: 600px;
    margin: 0 auto;
}

.about-content {
    background: var(--white);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm);
    padding: var(--spacing-xxl);
    margin-bottom: var(--spacing-xl);
}

.about-section {
    margin-bottom: var(--spacing-xl);
}

.about-section:last-child {
    margin-bottom: 0;
}

.section-title {
    font-size: 2rem;
    font-weight: 600;
    color: var(--primary-color);
    margin-bottom: var(--spacing-lg);
    text-align: center;
}

.about-text {
    font-size: 1.125rem;
    line-height: 1.8;
    color: var(--text-color);
    margin-bottom: var(--spacing-lg);
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: var(--spacing-lg);
    margin-top: var(--spacing-xl);
}

.feature-card {
    background: var(--white);
    border: 1px solid var(--light-gray);
    border-radius: var(--radius-md);
    padding: var(--spacing-lg);
    text-align: center;
    transition: all var(--transition-fast);
}

.feature-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-md);
    border-color: var(--primary-color);
}

.feature-icon {
    font-size: 3rem;
    color: var(--primary-color);
    margin-bottom: var(--spacing-md);
}

.feature-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--black);
    margin-bottom: var(--spacing-sm);
}

.feature-description {
    color: var(--text-color);
    line-height: 1.6;
}

.stats-section {
    background: var(--light-gray);
    border-radius: var(--radius-lg);
    padding: var(--spacing-xxl);
    margin: var(--spacing-xl) 0;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: var(--spacing-lg);
    text-align: center;
}

.stat-item {
    padding: var(--spacing-lg);
}

.stat-number {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--primary-color);
    display: block;
    margin-bottom: var(--spacing-sm);
}

.stat-label {
    font-size: 1rem;
    color: var(--text-color);
    font-weight: 500;
}

.contact-section {
    background: var(--primary-color);
    color: var(--white);
    border-radius: var(--radius-lg);
    padding: var(--spacing-xxl);
    text-align: center;
    margin-top: var(--spacing-xl);
}

.contact-title {
    font-size: 2rem;
    font-weight: 600;
    margin-bottom: var(--spacing-md);
}

.contact-info {
    font-size: 1.25rem;
    margin-bottom: var(--spacing-lg);
}

.contact-phone {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--white);
    text-decoration: none;
}

.contact-phone:hover {
    color: var(--white);
    text-decoration: none;
    opacity: 0.8;
}

@media (max-width: 768px) {
    .about-title {
        font-size: 2.5rem;
    }

    .about-content {
        padding: var(--spacing-lg);
    }

    .features-grid,
    .stats-grid {
        grid-template-columns: 1fr;
    }

    .section-title {
        font-size: 1.75rem;
    }
}
</style>

<section class="py-5">
    <div class="container about-container">
        <div class="about-hero">
            <h1 class="about-title">About Selling Poultry Farm</h1>
            <p class="about-subtitle">Your trusted partner in fresh, quality poultry products delivered right to your
                doorstep</p>
        </div>

        <div class="about-content">
            <div class="about-section">
                <h2 class="section-title">Welcome to Selling Poultry Farm Management System!</h2>
                <p class="about-text">
                    This system was created to help our customers easily order <strong>mature laying hens</strong> of
                    the
                    <strong>Sasso</strong> and <strong>Korolier</strong> breeds, along with their <strong>high-quality
                        eggs</strong>,
                    through our online platform.
                </p>
                <p class="about-text">
                    Now, you can order <strong>meat or egg-laying chickens</strong> from anywhere using your phone or
                    computer.
                    Our system makes it easy for customers to access quality products quickly and reliably.
                </p>
                <p class="about-text">
                    <strong>Our goal</strong> is to modernize poultry farming using technology and make it easier for
                    every
                    customer to get what they need without hassle.
                </p>
            </div>

            <div class="about-section">
                <h2 class="section-title">Why Choose Us?</h2>
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-leaf"></i>
                        </div>
                        <h3 class="feature-title">Fresh & Natural</h3>
                        <p class="feature-description">
                            Our poultry is raised naturally without harmful additives, ensuring you get the freshest,
                            healthiest products possible.
                        </p>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-truck"></i>
                        </div>
                        <h3 class="feature-title">Fast Delivery</h3>
                        <p class="feature-description">
                            We deliver fresh products directly to your doorstep with our efficient delivery system,
                            ensuring maximum freshness.
                        </p>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-award"></i>
                        </div>
                        <h3 class="feature-title">Quality Guaranteed</h3>
                        <p class="feature-description">
                            Every product undergoes strict quality checks to meet our high standards before
                            reaching your table.
                        </p>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h3 class="feature-title">Easy Ordering</h3>
                        <p class="feature-description">
                            Order from anywhere using your phone or computer with our user-friendly online platform.
                        </p>
                    </div>
                </div>
            </div>

            <div class="stats-section">
                <h2 class="section-title">Our Impact</h2>
                <div class="stats-grid">
                    <div class="stat-item">
                        <span class="stat-number">500+</span>
                        <span class="stat-label">Happy Customers</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">1000+</span>
                        <span class="stat-label">Orders Delivered</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">2</span>
                        <span class="stat-label">Premium Breeds</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">24/7</span>
                        <span class="stat-label">Online Service</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="contact-section">
            <h2 class="contact-title">Get in Touch</h2>
            <p class="contact-info">For more information, feel free to call us at:</p>
            <a href="tel:+250780162188" class="contact-phone">
                <i class="fas fa-phone"></i> +250 784 659 516
            </a>
        </div>
    </div>
</section>

<?php include 'inc/footer.php' ?>