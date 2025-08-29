<!-- AOS Animation JS -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<!-- Initialize AOS -->
<script>
    // Initialize AOS if it exists
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 1000,
            once: true,
            offset: 100,
            easing: 'ease-in-out'
        });
    }
</script>

<!-- Custom Scripts -->
<script>
    // Global utility functions
    window.SmartPoultryFarm = window.SmartPoultryFarm || {};

    // Smooth scroll function
    function smoothScrollTo(targetId, offset = 80) {
        const target = document.getElementById(targetId);
        if (target) {
            const elementPosition = target.offsetTop - offset;
            window.scrollTo({
                top: elementPosition,
                behavior: 'smooth'
            });
        }
    }

    // Add smooth scroll to all anchor links
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                if (targetId !== '#') {
                    smoothScrollTo(targetId);
                }
            });
        });
    });
</script>