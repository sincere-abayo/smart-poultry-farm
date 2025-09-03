<style>
/* ===== FOOTER STYLES ===== */
.footer {
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    color: var(--white);
    padding: var(--spacing-xxl) 0 var(--spacing-lg) 0;
    margin-top: var(--spacing-xxl);
    position: relative;
    overflow: hidden;
}

.footer::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
}

.footer-content {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 var(--spacing-lg);
    box-sizing: border-box;
}

.footer-main {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: var(--spacing-xl);
    margin-bottom: var(--spacing-xl);
}

.footer-section h3 {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: var(--spacing-lg);
    color: var(--white);
    position: relative;
    padding-bottom: var(--spacing-sm);
}

.footer-section h3::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 40px;
    height: 2px;
    background: var(--primary-color);
}

.footer-logo {
    display: flex;
    align-items: center;
    margin-bottom: var(--spacing-lg);
}

.footer-logo i {
    font-size: 2rem;
    color: var(--primary-color);
    margin-right: var(--spacing-md);
}

.footer-logo h2 {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0;
    color: var(--white);
}

.footer-description {
    color: rgba(255, 255, 255, 0.8);
    line-height: 1.6;
    margin-bottom: var(--spacing-lg);
}

.footer-links {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-links li {
    margin-bottom: var(--spacing-sm);
}

.footer-links a {
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    transition: all var(--transition-fast);
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
}

.footer-links a:hover {
    color: var(--primary-color);
    transform: translateX(5px);
}

.footer-links a i {
    width: 16px;
    text-align: center;
}

.contact-info {
    color: rgba(255, 255, 255, 0.8);
}

.contact-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-md);
    margin-bottom: var(--spacing-md);
    padding: var(--spacing-sm) 0;
}

.contact-item i {
    width: 20px;
    color: var(--primary-color);
    text-align: center;
}

.social-links {
    display: flex;
    gap: var(--spacing-md);
    margin-top: var(--spacing-lg);
}

.social-link {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    color: var(--white);
    text-decoration: none;
    transition: all var(--transition-fast);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.social-link:hover {
    background: var(--primary-color);
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    color: var(--white);
}

.footer-bottom {
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    padding-top: var(--spacing-lg);
    text-align: center;
    color: rgba(255, 255, 255, 0.6);
}

.footer-bottom p {
    margin: 0;
    font-size: 0.875rem;
}

.footer-bottom a {
    color: var(--primary-color);
    text-decoration: none;
    transition: all var(--transition-fast);
}

.footer-bottom a:hover {
    color: var(--white);
    text-decoration: underline;
}

/* Newsletter Subscription */
.newsletter {
    background: rgba(255, 255, 255, 0.05);
    border-radius: var(--radius-md);
    padding: var(--spacing-lg);
    margin-top: var(--spacing-lg);
}

.newsletter h4 {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: var(--spacing-sm);
    color: var(--white);
}

.newsletter p {
    font-size: 0.875rem;
    color: rgba(255, 255, 255, 0.7);
    margin-bottom: var(--spacing-md);
}

.newsletter-form {
    display: flex;
    gap: var(--spacing-sm);
}

.newsletter-input {
    flex: 1;
    padding: var(--spacing-sm) var(--spacing-md);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: var(--radius-sm);
    background: rgba(255, 255, 255, 0.1);
    color: var(--white);
    font-size: 0.875rem;
}

.newsletter-input::placeholder {
    color: rgba(255, 255, 255, 0.5);
}

.newsletter-input:focus {
    outline: none;
    border-color: var(--primary-color);
    background: rgba(255, 255, 255, 0.15);
}

.newsletter-btn {
    padding: var(--spacing-sm) var(--spacing-md);
    background: var(--primary-color);
    color: var(--white);
    border: none;
    border-radius: var(--radius-sm);
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: all var(--transition-fast);
}

.newsletter-btn:hover {
    background: var(--primary-dark);
    transform: translateY(-1px);
}

/* Responsive Design */
@media (max-width: 1200px) {
    .footer-content {
        padding: 0 var(--spacing-md);
        margin: 0 var(--spacing-md);
    }

    .footer-main {
        gap: var(--spacing-lg);
    }
}

@media (max-width: 992px) {
    .footer-content {
        padding: 0 var(--spacing-md);
        margin: 0 var(--spacing-md);
    }

    .footer-main {
        grid-template-columns: repeat(2, 1fr);
        gap: var(--spacing-lg);
    }

    .footer-section {
        text-align: center;
    }

    .footer-section h3::after {
        left: 50%;
        transform: translateX(-50%);
    }

    .social-links {
        justify-content: center;
    }

    .contact-item {
        justify-content: center;
    }

    .newsletter-form {
        flex-direction: column;
        gap: var(--spacing-sm);
    }
}

@media (max-width: 768px) {
    .footer {
        padding: var(--spacing-xl) 0 var(--spacing-lg) 0;
    }

    .footer-content {
        padding: 0 var(--spacing-md);
        margin: 0 var(--spacing-md);
    }

    .footer-main {
        grid-template-columns: 1fr;
        gap: var(--spacing-lg);
    }

    .footer-section h3 {
        font-size: 1.125rem;
        margin-bottom: var(--spacing-md);
    }

    .footer-logo h2 {
        font-size: 1.25rem;
    }

    .footer-description {
        font-size: 0.9rem;
        margin-bottom: var(--spacing-md);
    }

    .footer-links a {
        font-size: 0.9rem;
    }

    .contact-item {
        font-size: 0.9rem;
    }

    .newsletter h4 {
        font-size: 0.9rem;
    }

    .newsletter p {
        font-size: 0.8rem;
    }
}

@media (max-width: 576px) {
    .footer {
        padding: var(--spacing-lg) 0 var(--spacing-md) 0;
    }

    .footer-content {
        padding: 0 var(--spacing-sm);
        margin: 0 var(--spacing-sm);
    }

    .footer-main {
        gap: var(--spacing-md);
    }

    .footer-section h3 {
        font-size: 1rem;
        margin-bottom: var(--spacing-sm);
    }

    .footer-logo {
        flex-direction: column;
        text-align: center;
        gap: var(--spacing-sm);
    }

    .footer-logo i {
        margin-right: 0;
        margin-bottom: var(--spacing-xs);
    }

    .footer-logo h2 {
        font-size: 1.125rem;
    }

    .footer-description {
        font-size: 0.85rem;
        text-align: center;
    }

    .footer-links {
        text-align: center;
    }

    .footer-links a {
        font-size: 0.85rem;
        justify-content: center;
    }

    .contact-item {
        font-size: 0.85rem;
        flex-direction: column;
        text-align: center;
        gap: var(--spacing-xs);
    }

    .contact-item i {
        margin-bottom: var(--spacing-xs);
    }

    .social-links {
        gap: var(--spacing-sm);
    }

    .social-link {
        width: 35px;
        height: 35px;
    }

    .newsletter {
        padding: var(--spacing-md);
    }

    .newsletter h4 {
        font-size: 0.85rem;
    }

    .newsletter p {
        font-size: 0.75rem;
    }

    .newsletter-input {
        font-size: 0.8rem;
        padding: var(--spacing-xs) var(--spacing-sm);
    }

    .newsletter-btn {
        font-size: 0.8rem;
        padding: var(--spacing-xs) var(--spacing-sm);
    }

    .footer-bottom p {
        font-size: 0.75rem;
        line-height: 1.4;
    }
}

@media (max-width: 480px) {
    .footer {
        padding: var(--spacing-md) 0 var(--spacing-sm) 0;
    }

    .footer-content {
        padding: 0 var(--spacing-xs);
        margin: 0 var(--spacing-xs);
    }

    .footer-main {
        gap: var(--spacing-sm);
    }

    .footer-section h3 {
        font-size: 0.9rem;
    }

    .footer-logo h2 {
        font-size: 1rem;
    }

    .footer-description {
        font-size: 0.8rem;
    }

    .footer-links a {
        font-size: 0.8rem;
    }

    .contact-item {
        font-size: 0.8rem;
    }

    .social-link {
        width: 30px;
        height: 30px;
    }

    .newsletter {
        padding: var(--spacing-sm);
    }

    .newsletter h4 {
        font-size: 0.8rem;
    }

    .newsletter p {
        font-size: 0.7rem;
    }

    .newsletter-input {
        font-size: 0.75rem;
    }

    .newsletter-btn {
        font-size: 0.75rem;
    }

    .footer-bottom p {
        font-size: 0.7rem;
    }
}

/* Landscape orientation adjustments for mobile */
@media (max-width: 768px) and (orientation: landscape) {
    .footer {
        padding: var(--spacing-md) 0;
    }

    .footer-main {
        grid-template-columns: repeat(2, 1fr);
        gap: var(--spacing-md);
    }

    .footer-section h3 {
        font-size: 1rem;
    }

    .footer-logo h2 {
        font-size: 1.125rem;
    }
}
</style>

<footer class="footer">
    <div class="footer-content">
        <div class="footer-main">
            <!-- Company Info Section -->
            <div class="footer-section">
                <div class="footer-logo">
                    <i class="fas fa-feather-alt"></i>
                    <h2><?php echo $_settings->info('name') ?: 'Smart Poultry Farm' ?></h2>
                </div>
                <p class="footer-description">
                    Your trusted partner in fresh, quality poultry products. We deliver premium chickens and eggs
                    directly to your doorstep with modern farming technology and excellent service.
                </p>
                <div class="social-links">
                    <a href="#" class="social-link" title="Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="social-link" title="Twitter">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="social-link" title="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="social-link" title="WhatsApp">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                </div>
            </div>

            <!-- Quick Links Section -->
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul class="footer-links">
                    <li><a href="./?p=home"><i class="fas fa-home"></i> Home</a></li>
                    <li><a href="./?p=about"><i class="fas fa-info-circle"></i> About Us</a></li>
                    <li><a href="./?p=products"><i class="fas fa-shopping-bag"></i> Products</a></li>
                    <?php if (isset($_SESSION['userdata']['id'])): ?>
                    <li><a href="./?p=my_account"><i class="fas fa-user"></i> My Account</a></li>
                    <li><a href="./?p=cart"><i class="fas fa-shopping-cart"></i> Cart</a></li>
                    <?php else: ?>
                    <li><a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                    <li><a href="registration.php"><i class="fas fa-user-plus"></i> Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Contact Info Section -->
            <div class="footer-section">
                <h3>Contact Info</h3>
                <div class="contact-info">
                    <div class="contact-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Kigali, Rwanda</span>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-phone"></i>
                        <a href="tel:+250780162188">+250 780 162 188</a>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <a href="mailto:info@smartpoultryfarm.com">info@smartpoultryfarm.com</a>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-clock"></i>
                        <span>Mon - Sat: 8:00 AM - 6:00 PM</span>
                    </div>
                </div>
            </div>

            <!-- Newsletter Section -->
            <div class="footer-section">
                <h3>Stay Updated</h3>
                <div class="newsletter">
                    <h4>Subscribe to our newsletter</h4>
                    <p>Get the latest updates on our products and special offers.</p>
                    <form class="newsletter-form" id="newsletterForm">
                        <input type="email" class="newsletter-input" placeholder="Enter your email" required>
                        <button type="submit" class="newsletter-btn">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Footer Bottom -->
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y') ?> <?php echo $_settings->info('name') ?: 'Smart Poultry Farm' ?>. All rights
                reserved. |
                <a href="./?p=privacy">Privacy Policy</a> |
                <a href="./?p=terms">Terms of Service</a>
            </p>
        </div>
    </div>
</footer>

<script>
// Newsletter subscription functionality
document.getElementById('newsletterForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const email = this.querySelector('.newsletter-input').value;
    const button = this.querySelector('.newsletter-btn');
    const originalText = button.innerHTML;

    // Show loading state
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    button.disabled = true;

    // Simulate API call (replace with actual implementation)
    setTimeout(() => {
        // Show success message
        button.innerHTML = '<i class="fas fa-check"></i>';
        button.style.background = '#28a745';

        // Reset form
        this.querySelector('.newsletter-input').value = '';

        // Show success notification
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Subscribed!',
                text: 'Thank you for subscribing to our newsletter.',
                timer: 3000,
                showConfirmButton: false
            });
        } else {
            alert('Thank you for subscribing to our newsletter!');
        }

        // Reset button after 3 seconds
        setTimeout(() => {
            button.innerHTML = originalText;
            button.style.background = '';
            button.disabled = false;
        }, 3000);
    }, 1500);
});

// Smooth scroll for footer links
document.querySelectorAll('.footer-links a[href^="#"]').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});
</script>