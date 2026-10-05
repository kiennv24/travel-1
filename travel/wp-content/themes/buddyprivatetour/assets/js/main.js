/**
 * BUDDY PRIVATE TOUR - MODERN FRONTEND JAVASCRIPT ENGINE
 * Handles interactive search filters, mosaic image galleries, timeline accordions,
 * guest calculation steppers, 1-click clipboard copy, and toast notifications.
 */

document.addEventListener('DOMContentLoaded', function () {
    console.log('Buddy Private Tour Modern UI/UX Initialized.');

    // 1. Mobile Menu Drawer Toggle
    const mobileMenuToggleBtn = document.getElementById('mobileMenuToggleBtn') || document.getElementById('mobileMenuBar');
    const mobileMenuDrawer = document.getElementById('mobileMenuDrawer') || document.getElementById('mobileMenuDropdown');

    if (mobileMenuToggleBtn && mobileMenuDrawer) {
        mobileMenuToggleBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            mobileMenuDrawer.classList.toggle('active');
        });
    }

    // Submenu toggle in mobile drawer
    document.querySelectorAll('.mobile-sub-toggle, .mobile-sub-chevron').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            const parent = this.closest('.mobile-menu-has-sub');
            if (parent) {
                parent.classList.toggle('open');
            }
        });
    });

    // 2. Mosaic Gallery Click-to-Switch Main Image
    window.switchBptMosaicImage = function (imgUrl, element) {
        const mainImg = document.getElementById('bpt-main-img');
        if (mainImg) {
            mainImg.src = imgUrl;
        }
        document.querySelectorAll('.mosaic-item, .bpt-thumb-item').forEach(el => el.classList.remove('active'));
        if (element) {
            element.classList.add('active');
        }
    };

    // 3. Single Tour Detail Guest Counter Stepper & Subtotal Calculation
    window.updateTourDetailGuests = function (delta, unitPrice) {
        const input = document.getElementById('detail-guest-count');
        if (!input) return;
        let count = parseInt(input.value) || 1;
        count += delta;
        if (count < 1) count = 1;
        if (count > 50) count = 50;
        input.value = count;

        const countDisplay = document.getElementById('calc-guest-display');
        if (countDisplay) countDisplay.textContent = count;

        if (unitPrice && unitPrice > 0) {
            const subtotal = count * unitPrice;
            const subtotalDisplay = document.getElementById('calc-subtotal-display');
            if (subtotalDisplay) {
                subtotalDisplay.textContent = new Intl.NumberFormat('vi-VN').format(subtotal) + ' đ';
            }
        }
    };

    // 4. Day-by-Day Timeline Accordion Toggle
    window.toggleTimelineDay = function (headerElement) {
        const card = headerElement.closest('.timeline-day-card');
        if (card) {
            card.classList.toggle('active');
        }
    };

    // 5. One-Click Copy to Clipboard with Modern Toast Notification
    window.copyToClipboard = function (text, message = 'Đã sao chép vào bộ nhớ tạm!') {
        if (!navigator.clipboard) {
            // Fallback
            const textArea = document.createElement('textarea');
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            showToast(message);
            return;
        }

        navigator.clipboard.writeText(text).then(function () {
            showToast(message);
        }).catch(function (err) {
            console.error('Failed to copy text: ', err);
        });
    };

    function showToast(message) {
        let toast = document.getElementById('bpt-toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'bpt-toast';
            toast.className = 'bpt-toast-notification';
            document.body.appendChild(toast);
        }
        toast.innerHTML = `<i class="fa-solid fa-circle-check" style="color: #10b981; font-size: 1.1rem;"></i> <span>${message}</span>`;
        toast.classList.add('show');
        setTimeout(() => {
            toast.classList.remove('show');
        }, 2800);
    }

    // 6. Floating Search Pill Interactive Filter
    const searchForm = document.getElementById('heroSearchForm');
    if (searchForm) {
        searchForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const destination = document.getElementById('searchDestination') ? document.getElementById('searchDestination').value : '';
            const guests = document.getElementById('searchGuests') ? document.getElementById('searchGuests').value : '1';

            // If on homepage or tour page, scroll or navigate to matching catalog
            if (destination) {
                const targetElement = document.getElementById(destination);
                if (targetElement) {
                    targetElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
                } else {
                    const isSubfolder = window.location.pathname.includes('/products/');
                    const targetPage = isSubfolder ? '../tour.html' : (window.BPT_IS_WORDPRESS ? '?pagename=tour&category=' + destination : 'tour.html?category=' + destination);
                    window.location.href = targetPage;
                }
            } else {
                const catalog = document.getElementById('products-catalog') || document.getElementById('catalog');
                if (catalog) {
                    catalog.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }
        });
    }

    // 7. Interactive Wishlist Heart Buttons
    document.querySelectorAll('.tour-wishlist-btn').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            this.classList.toggle('active');
            const icon = this.querySelector('i');
            if (icon) {
                if (this.classList.contains('active')) {
                    icon.classList.remove('fa-regular');
                    icon.classList.add('fa-solid');
                    showToast('Đã lưu vào danh sách yêu thích!');
                } else {
                    icon.classList.remove('fa-solid');
                    icon.classList.add('fa-regular');
                }
            }
        });
    });

    // 8. Auto scroll to top on click
    const scrollTopBtn = document.querySelector('.floating-back-to-top');
    if (scrollTopBtn) {
        window.addEventListener('scroll', function () {
            if (window.scrollY > 300) {
                scrollTopBtn.style.opacity = '1';
                scrollTopBtn.style.pointerEvents = 'auto';
            } else {
                scrollTopBtn.style.opacity = '0';
                scrollTopBtn.style.pointerEvents = 'none';
            }
        });
    }
});
