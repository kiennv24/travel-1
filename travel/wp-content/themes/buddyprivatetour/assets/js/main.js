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

        // Close drawer when clicking anywhere outside
        document.addEventListener('click', function (e) {
            if (mobileMenuDrawer.classList.contains('active')) {
                if (!mobileMenuDrawer.contains(e.target) && !mobileMenuToggleBtn.contains(e.target)) {
                    mobileMenuDrawer.classList.remove('active');
                }
            }
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

    // Detect mobile bottom booking bar to adjust floating actions
    if (document.querySelector('.mobile-bottom-booking-bar')) {
        document.body.classList.add('has-bottom-booking-bar');
    }

    // Active Navigation Synchronization across Desktop & Mobile
    function syncActiveNav() {
        const path = window.location.pathname.toLowerCase();
        const search = window.location.search.toLowerCase();

        let activeKey = 'home';
        if (search.includes('pagename=feedback') || path.endsWith('/feedback.html') || path.endsWith('/feedback')) {
            activeKey = 'feedback';
        } else if (search.includes('pagename=about-us') || path.endsWith('/about-us.html') || path.endsWith('/about-us')) {
            activeKey = 'about-us';
        } else if (search.includes('pagename=tour') || path.endsWith('/tour.html') || path.endsWith('/tour') || path.includes('/products/') || path.includes('/product/')) {
            activeKey = 'tour';
        } else if (search.includes('pagename=other-services') || path.endsWith('/other-services.html') || path.endsWith('/other-services')) {
            activeKey = 'other-services';
        } else if (search.includes('pagename=videos') || path.endsWith('/videos.html') || path.endsWith('/videos')) {
            activeKey = 'videos';
        } else if (search.includes('pagename=contact') || path.endsWith('/contact.html') || path.endsWith('/contact')) {
            activeKey = 'contact';
        } else if (search.includes('pagename=cart') || path.endsWith('/cart.html') || path.endsWith('/cart')) {
            activeKey = 'cart';
        } else if (search.includes('pagename=checkout') || path.endsWith('/checkout.html') || path.endsWith('/checkout')) {
            activeKey = 'checkout';
        } else {
            activeKey = 'home';
        }

        function checkActiveMatch(li, key) {
            const link = li.querySelector('a');
            if (!link) return false;
            const i18n = (link.querySelector('[data-i18n]')?.getAttribute('data-i18n') || link.getAttribute('data-i18n') || '').toLowerCase();
            const href = (link.getAttribute('href') || '').toLowerCase();

            if (key === 'home') {
                return i18n === 'nav_home' || href === '/' || href.endsWith('/index.html') || href.endsWith('/index.php') || href.endsWith('localhost/') || (href.endsWith('/travel/') && !href.includes('?'));
            }
            if (key === 'tour') {
                return i18n === 'nav_tour' || href.includes('pagename=tour') || href.endsWith('tour.html') || href.endsWith('/tour');
            }
            if (key === 'about-us') {
                return i18n === 'nav_about' || href.includes('about-us');
            }
            if (key === 'feedback') {
                return i18n === 'nav_feedback' || href.includes('feedback');
            }
            if (key === 'contact') {
                return i18n === 'nav_contact' || href.includes('contact');
            }
            if (key === 'other-services') {
                return i18n.includes('other') || i18n.includes('services') || href.includes('other-services');
            }
            if (key === 'videos') {
                return i18n === 'nav_videos' || href.includes('videos');
            }
            return false;
        }

        // Desktop nav sync
        document.querySelectorAll('.nav-menu > li').forEach(function (li) {
            li.classList.remove('active', 'menu-item-home');
            if (checkActiveMatch(li, activeKey)) {
                li.classList.add('active');
            }
        });

        // Mobile drawer nav sync
        document.querySelectorAll('.mobile-menu-list > li').forEach(function (li) {
            li.classList.remove('active');
            if (checkActiveMatch(li, activeKey)) {
                li.classList.add('active');
            }
        });
    }
    syncActiveNav();

    // 2. Mosaic Gallery & Full Mobile Carousel Engine
    window.bptGalleryPhotos = window.bptGalleryPhotos || [];
    window.currentGalleryIdx = 0;

    window.initBptTourGallery = function (photos) {
        if (Array.isArray(photos) && photos.length > 0) {
            window.bptGalleryPhotos = photos;
        } else {
            const list = [];
            document.querySelectorAll('#bpt-mobile-gallery-strip .mobile-gallery-thumb img, .mosaic-item img').forEach(function (img) {
                const s = img.getAttribute('src');
                if (s && !list.includes(s)) list.push(s);
            });
            window.bptGalleryPhotos = list;
        }
        window.currentGalleryIdx = 0;
        setupGallerySwipe();
    };

    window.switchBptMosaicImage = function (imgUrl, element) {
        const mainImg = document.getElementById('bpt-main-img');
        if (mainImg) {
            mainImg.src = imgUrl;
        }
        document.querySelectorAll('.mosaic-item, .bpt-thumb-item, .mobile-gallery-thumb').forEach(function (el) {
            el.classList.remove('active');
        });
        if (element) {
            element.classList.add('active');
        }

        // Sync index & mobile thumb
        const idx = window.bptGalleryPhotos.indexOf(imgUrl);
        if (idx !== -1) {
            window.currentGalleryIdx = idx;
            const counter = document.getElementById('gallery-current-num');
            if (counter) counter.textContent = idx + 1;
            const thumb = document.querySelector('.mobile-gallery-thumb[data-idx="' + idx + '"]');
            if (thumb) {
                thumb.classList.add('active');
                thumb.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
            }
        }
    };

    window.selectBptGalleryIndex = function (idx) {
        if (!window.bptGalleryPhotos || !window.bptGalleryPhotos[idx]) return;
        const url = window.bptGalleryPhotos[idx];
        window.currentGalleryIdx = idx;
        const mainImg = document.getElementById('bpt-main-img');
        if (mainImg) {
            mainImg.src = url;
            mainImg.dataset.idx = idx;
        }
        const counter = document.getElementById('gallery-current-num');
        if (counter) counter.textContent = idx + 1;

        document.querySelectorAll('.mobile-gallery-thumb').forEach(function (el) {
            el.classList.remove('active');
        });
        const thumb = document.querySelector('.mobile-gallery-thumb[data-idx="' + idx + '"]');
        if (thumb) {
            thumb.classList.add('active');
            thumb.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
        }
    };

    window.navigateBptGallery = function (delta) {
        const total = window.bptGalleryPhotos.length;
        if (total <= 1) return;
        let newIdx = (window.currentGalleryIdx + delta) % total;
        if (newIdx < 0) newIdx = total - 1;
        selectBptGalleryIndex(newIdx);
    };

    function setupGallerySwipe() {
        const frame = document.getElementById('bpt-gallery-main-frame');
        if (!frame || frame._swipeSetup) return;
        frame._swipeSetup = true;

        let startX = 0;
        let startY = 0;
        frame.addEventListener('touchstart', function (e) {
            if (e.touches.length === 1) {
                startX = e.touches[0].clientX;
                startY = e.touches[0].clientY;
            }
        }, { passive: true });

        frame.addEventListener('touchend', function (e) {
            if (e.changedTouches.length === 1) {
                const diffX = e.changedTouches[0].clientX - startX;
                const diffY = e.changedTouches[0].clientY - startY;
                if (Math.abs(diffX) > 40 && Math.abs(diffX) > Math.abs(diffY)) {
                    if (diffX < 0) {
                        navigateBptGallery(1); // Swipe left -> Next
                    } else {
                        navigateBptGallery(-1); // Swipe right -> Prev
                    }
                }
            }
        }, { passive: true });
    }

    // Lightbox / Fullscreen Image Viewer Modal
    window.openBptGalleryModal = function (startIdx) {
        let modal = document.getElementById('bpt-lightbox-modal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'bpt-lightbox-modal';
            modal.className = 'bpt-lightbox-overlay';
            modal.innerHTML = '<div class="bpt-lightbox-backdrop" onclick="closeBptGalleryModal()"></div>' +
                '<div class="bpt-lightbox-container">' +
                '<button type="button" class="bpt-lightbox-btn close" onclick="closeBptGalleryModal()">&times;</button>' +
                '<button type="button" class="bpt-lightbox-btn prev" onclick="navigateBptLightbox(-1)"><i class="fa-solid fa-chevron-left"></i></button>' +
                '<div class="bpt-lightbox-content">' +
                '<img id="bpt-lightbox-img" src="" alt="Full view">' +
                '<div class="bpt-lightbox-counter"><span id="lightbox-idx">1</span> / <span id="lightbox-total">1</span></div>' +
                '</div>' +
                '<button type="button" class="bpt-lightbox-btn next" onclick="navigateBptLightbox(1)"><i class="fa-solid fa-chevron-right"></i></button>' +
                '</div>';
            document.body.appendChild(modal);

            document.addEventListener('keydown', function (e) {
                if (!modal.classList.contains('open')) return;
                if (e.key === 'Escape') closeBptGalleryModal();
                if (e.key === 'ArrowLeft') navigateBptLightbox(-1);
                if (e.key === 'ArrowRight') navigateBptLightbox(1);
            });
        }
        window.lightboxIdx = typeof startIdx === 'number' ? startIdx : window.currentGalleryIdx;
        updateLightboxView();
        modal.classList.add('open');
        document.body.style.overflow = 'hidden';
    };

    window.closeBptGalleryModal = function () {
        const modal = document.getElementById('bpt-lightbox-modal');
        if (modal) modal.classList.remove('open');
        document.body.style.overflow = '';
    };

    window.navigateBptLightbox = function (delta) {
        const total = window.bptGalleryPhotos.length;
        if (total <= 1) return;
        window.lightboxIdx = (window.lightboxIdx + delta + total) % total;
        updateLightboxView();
    };

    function updateLightboxView() {
        const img = document.getElementById('bpt-lightbox-img');
        const counter = document.getElementById('lightbox-idx');
        const totalEl = document.getElementById('lightbox-total');
        if (img && window.bptGalleryPhotos[window.lightboxIdx]) {
            img.src = window.bptGalleryPhotos[window.lightboxIdx];
        }
        if (counter) counter.textContent = window.lightboxIdx + 1;
        if (totalEl) totalEl.textContent = window.bptGalleryPhotos.length;
    }

    // ==========================================================================
    // 3. MODERN TOUR BOOKING ENGINE: PARTICIPANTS COUNTER & INTERACTIVE CALENDAR
    // ==========================================================================
    const bptInitialToday = new Date();
    const bptTodayY = bptInitialToday.getFullYear();
    const bptTodayM = String(bptInitialToday.getMonth() + 1).padStart(2, '0');
    const bptTodayD = String(bptInitialToday.getDate()).padStart(2, '0');
    const bptTodayDateStr = `${bptTodayY}-${bptTodayM}-${bptTodayD}`;

    window.bptBookingState = {
        unitPrice: window.tourPrice || 0,
        adults: 1,
        youths: 0,
        children: 0,
        infants: 0,
        selectedDate: bptTodayDateStr,
        viewYear: bptInitialToday.getFullYear(),
        viewMonth: bptInitialToday.getMonth(),
        rates: {
            adult: 1.0,
            youth: 0.85,
            child: 0.5,
            infant: 0.0
        }
    };

    window.initBptTourBooking = function (unitPrice) {
        const cardEl = document.getElementById('tour-booking-card');
        const domPrice = cardEl && cardEl.dataset.unitPrice ? parseFloat(cardEl.dataset.unitPrice) : 0;
        if (!unitPrice && domPrice) {
            unitPrice = domPrice;
        } else if (!unitPrice && window.tourPrice) {
            unitPrice = window.tourPrice;
        }
        window.bptBookingState.unitPrice = parseFloat(unitPrice) || domPrice || parseFloat(window.tourPrice) || 0;

        // Initialize default departure date (Today - current date)
        const today = new Date();
        const y = today.getFullYear();
        const m = String(today.getMonth() + 1).padStart(2, '0');
        const d = String(today.getDate()).padStart(2, '0');
        const dateStr = `${y}-${m}-${d}`;

        window.bptBookingState.selectedDate = dateStr;
        window.bptBookingState.viewYear = today.getFullYear();
        window.bptBookingState.viewMonth = today.getMonth();

        // Update hidden inputs if exist
        const dateInput = document.getElementById('detail-tour-date');
        if (dateInput) dateInput.value = dateStr;

        // Read initial participant values from DOM if present
        ['adult', 'youth', 'child', 'infant'].forEach(function (type) {
            const el = document.getElementById('qty-' + type);
            if (el) {
                const val = parseInt(el.textContent.trim(), 10);
                if (!isNaN(val)) {
                    const k = type === 'adult' ? 'adults' : (type === 'youth' ? 'youths' : (type === 'child' ? 'children' : 'infants'));
                    window.bptBookingState[k] = val;
                }
            }
            // Update minus button disabled state
            const minusBtn = document.querySelector(`.booking-participant-row[data-type="${type}"] .btn-minus`);
            if (minusBtn) {
                const minVal = (type === 'adult') ? 1 : 0;
                const cur = window.bptBookingState[type === 'adult' ? 'adults' : (type === 'youth' ? 'youths' : (type === 'child' ? 'children' : 'infants'))];
                if (cur <= minVal) {
                    minusBtn.classList.add('disabled');
                } else {
                    minusBtn.classList.remove('disabled');
                }
            }
        });

        // Initialize calendar
        initBptCalendar();

        // Initialize calculation
        recalculateTourTotal();

        // Close month picker when clicking outside
        document.addEventListener('click', function (e) {
            const monthDisplay = document.getElementById('cal-month-display');
            const dropdown = document.getElementById('cal-month-dropdown');
            if (dropdown && dropdown.style.display === 'grid') {
                if (monthDisplay && !monthDisplay.contains(e.target) && !dropdown.contains(e.target)) {
                    dropdown.style.display = 'none';
                    if (monthDisplay) monthDisplay.classList.remove('open');
                }
            }
        });
    };

    window.changeParticipantQty = function (type, delta) {
        const key = type === 'adult' ? 'adults' : (type === 'youth' ? 'youths' : (type === 'child' ? 'children' : 'infants'));
        let current = window.bptBookingState[key] || 0;
        current += delta;

        // Min limits: Adult min 1; Youth min 0; Child min 0; Infant min 0
        const minVal = (type === 'adult') ? 1 : 0;
        if (current < minVal) current = minVal;
        if (current > 50) current = 50;

        window.bptBookingState[key] = current;

        // Update DOM display
        const displayEl = document.getElementById('qty-' + type);
        if (displayEl) {
            displayEl.textContent = current;
        }

        // Update minus button disabled state
        const minusBtn = document.querySelector(`.booking-participant-row[data-type="${type}"] .btn-minus`);
        if (minusBtn) {
            if (current <= minVal) {
                minusBtn.classList.add('disabled');
            } else {
                minusBtn.classList.remove('disabled');
            }
        }

        recalculateTourTotal();
    };

    window.recalculateTourTotal = function () {
        const state = window.bptBookingState;
        const cardEl = document.getElementById('tour-booking-card');
        const domPrice = cardEl && cardEl.dataset.unitPrice ? parseFloat(cardEl.dataset.unitPrice) : 0;
        const unitPrice = parseFloat(state.unitPrice) || domPrice || parseFloat(window.tourPrice) || 0;
        if (!state.unitPrice && unitPrice > 0) {
            state.unitPrice = unitPrice;
        }
        const { adults, youths, children, infants, rates } = state;
        const totalGuests = adults + youths + children + infants;

        const adultTotal = adults * unitPrice * rates.adult;
        const youthTotal = youths * unitPrice * rates.youth;
        const childTotal = children * unitPrice * rates.child;
        const infantTotal = 0;
        const grandTotal = adultTotal + youthTotal + childTotal + infantTotal;

        // Update hidden inputs for form submissions
        const guestCountInput = document.getElementById('detail-guest-count');
        if (guestCountInput) guestCountInput.value = totalGuests;

        const fmt = function (num) {
            return new Intl.NumberFormat('vi-VN').format(Math.round(num)) + ' VNĐ';
        };

        // Update calculation breakdown rows
        const elAdultCount = document.getElementById('calc-adult-count');
        if (elAdultCount) elAdultCount.textContent = adults;
        const elAdultTotal = document.getElementById('calc-adult-total');
        if (elAdultTotal) elAdultTotal.textContent = fmt(adultTotal);

        const elYouthCount = document.getElementById('calc-youth-count');
        if (elYouthCount) elYouthCount.textContent = youths;
        const elYouthTotal = document.getElementById('calc-youth-total');
        if (elYouthTotal) elYouthTotal.textContent = fmt(youthTotal);
        const rowYouth = document.getElementById('row-calc-youth');
        if (rowYouth) rowYouth.style.display = youths > 0 ? 'flex' : 'none';

        const elChildCount = document.getElementById('calc-child-count');
        if (elChildCount) elChildCount.textContent = children;
        const elChildTotal = document.getElementById('calc-child-total');
        if (elChildTotal) elChildTotal.textContent = fmt(childTotal);
        const rowChild = document.getElementById('row-calc-child');
        if (rowChild) rowChild.style.display = children > 0 ? 'flex' : 'none';

        const elInfantCount = document.getElementById('calc-infant-count');
        if (elInfantCount) elInfantCount.textContent = infants;
        const rowInfant = document.getElementById('row-calc-infant');
        if (rowInfant) rowInfant.style.display = infants > 0 ? 'flex' : 'none';

        // Overall guest display
        const calcGuestDisplay = document.getElementById('calc-guest-display');
        if (calcGuestDisplay) calcGuestDisplay.textContent = totalGuests;

        const subtotalDisplay = document.getElementById('calc-subtotal-display');
        if (subtotalDisplay) subtotalDisplay.textContent = fmt(grandTotal);

        const grandTotalDisplay = document.getElementById('calc-grand-total');
        if (grandTotalDisplay) grandTotalDisplay.textContent = fmt(grandTotal);

        // Mobile bottom sticky bar price
        const mobilePriceVal = document.getElementById('mobile-bottom-val') || document.querySelector('.mobile-bottom-bar-price .val');
        if (mobilePriceVal) {
            mobilePriceVal.textContent = fmt(grandTotal);
        }
    };

    window.initBptCalendar = function () {
        renderCalendarGrid();
    };

    function renderCalendarGrid() {
        const grid = document.getElementById('cal-days-grid');
        if (!grid) return;

        const { viewYear, viewMonth, selectedDate } = window.bptBookingState;
        const monthNames = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];

        // Update Month Label (e.g. May 2026)
        const label = document.getElementById('cal-month-label');
        if (label) {
            label.textContent = `${monthNames[viewMonth]} ${viewYear}`;
        }

        // Disable Prev Month button if view month is current or earlier
        const today = new Date();
        const currentYear = today.getFullYear();
        const currentMonth = today.getMonth();
        const prevBtn = document.getElementById('cal-prev-month');
        if (prevBtn) {
            if (viewYear < currentYear || (viewYear === currentYear && viewMonth <= currentMonth)) {
                prevBtn.classList.add('disabled');
            } else {
                prevBtn.classList.remove('disabled');
            }
        }

        grid.innerHTML = '';

        // First day of view month
        const firstDay = new Date(viewYear, viewMonth, 1);
        // Day of week: 0=Sun, 1=Mon, ..., 6=Sat
        let startDay = firstDay.getDay() - 1;
        if (startDay < 0) startDay = 6;

        const daysInMonth = new Date(viewYear, viewMonth + 1, 0).getDate();

        // Empty padding cells for start of month
        for (let i = 0; i < startDay; i++) {
            const emptyCell = document.createElement('div');
            emptyCell.className = 'cal-day-cell empty';
            grid.appendChild(emptyCell);
        }

        // Populate days
        const todayStr = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;

        for (let day = 1; day <= daysInMonth; day++) {
            const cell = document.createElement('div');
            cell.className = 'cal-day-cell';
            cell.textContent = day;

            const mStr = String(viewMonth + 1).padStart(2, '0');
            const dStr = String(day).padStart(2, '0');
            const dateStr = `${viewYear}-${mStr}-${dStr}`;

            const cellDate = new Date(viewYear, viewMonth, day);
            const isPast = cellDate < new Date(today.getFullYear(), today.getMonth(), today.getDate());

            if (isPast) {
                cell.classList.add('past');
            } else {
                if (dateStr === todayStr) {
                    cell.classList.add('today');
                }
                if (dateStr === selectedDate) {
                    cell.classList.add('selected');
                }

                cell.addEventListener('click', function () {
                    selectCalendarDate(viewYear, viewMonth, day);
                });
            }

            grid.appendChild(cell);
        }

        updateSelectedDateBadge();
    }

    window.changeCalendarMonth = function (delta) {
        const today = new Date();
        const currentYear = today.getFullYear();
        const currentMonth = today.getMonth();

        let newMonth = window.bptBookingState.viewMonth + delta;
        let newYear = window.bptBookingState.viewYear;

        if (newMonth < 0) {
            newMonth = 11;
            newYear--;
        } else if (newMonth > 11) {
            newMonth = 0;
            newYear++;
        }

        // Don't navigate into past months
        if (newYear < currentYear || (newYear === currentYear && newMonth < currentMonth)) {
            return;
        }

        window.bptBookingState.viewYear = newYear;
        window.bptBookingState.viewMonth = newMonth;
        renderCalendarGrid();
    };

    window.selectCalendarDate = function (year, month, day) {
        const mStr = String(month + 1).padStart(2, '0');
        const dStr = String(day).padStart(2, '0');
        const dateStr = `${year}-${mStr}-${dStr}`;

        window.bptBookingState.selectedDate = dateStr;

        // Update hidden date input
        const dateInput = document.getElementById('detail-tour-date');
        if (dateInput) {
            dateInput.value = dateStr;
        }

        renderCalendarGrid();
        updateSelectedDateBadge();
    };

    function updateSelectedDateBadge() {
        const text = document.getElementById('selected-date-text');
        if (!text) return;

        if (!window.bptBookingState.selectedDate) {
            text.textContent = 'Please choose a date';
            return;
        }

        const parts = window.bptBookingState.selectedDate.split('-');
        if (parts.length === 3) {
            const d = new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]));
            const options = { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' };
            text.textContent = d.toLocaleDateString('en-US', options);
        }
    }

    window.toggleMonthPicker = function () {
        let dropdown = document.getElementById('cal-month-dropdown');
        const display = document.getElementById('cal-month-display');
        if (!dropdown) return;

        if (dropdown.style.display === 'grid') {
            dropdown.style.display = 'none';
            if (display) display.classList.remove('open');
            return;
        }

        dropdown.innerHTML = '';
        dropdown.style.display = 'grid';
        if (display) display.classList.add('open');

        const monthNamesShort = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        const today = new Date();

        // Show upcoming 12 months starting from current month
        for (let i = 0; i < 12; i++) {
            const d = new Date(today.getFullYear(), today.getMonth() + i, 1);
            const y = d.getFullYear();
            const m = d.getMonth();

            const opt = document.createElement('div');
            opt.className = 'cal-month-option';
            if (y === window.bptBookingState.viewYear && m === window.bptBookingState.viewMonth) {
                opt.classList.add('active');
            }
            opt.textContent = `${monthNamesShort[m]} ${y}`;
            opt.addEventListener('click', function (e) {
                e.stopPropagation();
                window.bptBookingState.viewYear = y;
                window.bptBookingState.viewMonth = m;
                dropdown.style.display = 'none';
                if (display) display.classList.remove('open');
                renderCalendarGrid();
            });
            dropdown.appendChild(opt);
        }
    };

    window.submitTourBooking = function (id) {
        const state = window.bptBookingState || {};
        const adults = state.adults !== undefined ? state.adults : 1;
        const youths = state.youths || 0;
        const children = state.children || 0;
        const infants = state.infants || 0;
        const totalGuests = adults + youths + children + infants;
        const todayStr = new Date().toISOString().split('T')[0];
        const date = state.selectedDate || document.getElementById('detail-tour-date')?.value || todayStr;

        // If on exported static HTML site
        if (typeof BPT_Storage !== 'undefined' && typeof window.BPT_IS_WORDPRESS === 'undefined') {
            const uPrice = parseFloat(state.unitPrice) || parseFloat(window.tourPrice) || 0;
            const calculatedTotal = (adults * uPrice * (state.rates?.adult || 1)) +
                (youths * uPrice * (state.rates?.youth || 0.85)) +
                (children * uPrice * (state.rates?.child || 0.5));
            BPT_Storage.addToCart(id, totalGuests, {
                date: date,
                adults: adults,
                youths: youths,
                children: children,
                infants: infants,
                totalPrice: calculatedTotal
            });
            return;
        }

        // On dynamic WordPress site
        const baseUrl = window.BPT_BASE_URL || '?';
        const params = new URLSearchParams({
            bpt_action: 'add_to_cart',
            product_id: id,
            qty: totalGuests,
            date: date,
            adults: adults,
            youths: youths,
            children: children,
            infants: infants
        });
        window.location.href = baseUrl + (baseUrl.includes('?') ? '&' : '?') + params.toString();
    };

    // Backward-compatibility wrapper
    window.updateTourDetailGuests = function (delta, unitPrice) {
        changeParticipantQty('adult', delta);
    };

    // 4. Day-by-Day Timeline Accordion Toggle
    window.toggleTimelineDay = function (headerElement) {
        const card = headerElement.closest('.timeline-day-card');
        if (card) {
            card.classList.toggle('active');
        }
    };

    // 5. One-Click Copy to Clipboard with Modern Toast Notification
    window.copyToClipboard = function (text, message = 'Copied to clipboard!') {
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
                    showToast('Saved to wishlist!');
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

    // 9. Smooth Scroll to Tour Booking Card from Floating Button
    window.scrollToTourBooking = function () {
        const target = document.getElementById('tour-booking-card') || document.querySelector('.sticky-booking-card');
        if (target) {
            const headerOffset = 85;
            const elementPosition = target.getBoundingClientRect().top;
            const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

            window.scrollTo({
                top: offsetPosition,
                behavior: 'smooth'
            });

            target.classList.add('bpt-highlight-pulse');
            setTimeout(function () {
                target.classList.remove('bpt-highlight-pulse');
            }, 1800);
        }
    };

    // 10. Auto-initialize tour booking engine if calendar is present
    if (document.getElementById('bpt-calendar')) {
        const cardEl = document.getElementById('tour-booking-card');
        const initialPrice = (cardEl && cardEl.dataset.unitPrice) ? parseFloat(cardEl.dataset.unitPrice) : (window.tourPrice || 0);
        initBptTourBooking(initialPrice);
    }
});
