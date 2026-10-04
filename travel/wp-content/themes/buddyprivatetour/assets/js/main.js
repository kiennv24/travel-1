/**
 * Travel Platform Main Interactive JS Script
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Buddy Private Tour App Initialized.');

    // Auto scroll to top on click
    const scrollTopBtn = document.getElementById('scroll-top');
    if (scrollTopBtn) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 300) {
                scrollTopBtn.style.display = 'flex';
            } else {
                scrollTopBtn.style.display = 'none';
            }
        });
        scrollTopBtn.addEventListener('click', function() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // Mobile Accordion MENU Bar Toggle (Matching Screenshots)
    const mobileMenuBar = document.getElementById('mobileMenuBar');
    const mobileMenuDropdown = document.getElementById('mobileMenuDropdown');

    if (mobileMenuBar && mobileMenuDropdown) {
        mobileMenuBar.addEventListener('click', function() {
            mobileMenuDropdown.classList.toggle('active');
        });
    }

    // Submenu chevron toggle inside mobile dropdown
    document.querySelectorAll('.mobile-sub-chevron').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const parent = this.closest('.mobile-menu-has-sub');
            if (parent) {
                parent.classList.toggle('open');
            }
        });
    });

    // Confirm product removal from cart
    const removeButtons = document.querySelectorAll('.btn-remove');
    removeButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to remove this item from your cart?')) {
                e.preventDefault();
            }
        });
    });
});
