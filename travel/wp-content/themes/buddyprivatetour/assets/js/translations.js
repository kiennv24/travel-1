/**
 * BUDDY PRIVATE TOUR - MULTILINGUAL DICTIONARY (EN / VI)
 * Client-side language switcher for Static Web & Dynamic WordPress
 * Default Language: English (EN) for Inbound International Travelers
 */

const BPT_I18N_DATA = {
    en: {
        // Top Ticker
        ticker_welcome: "✨ Welcome to BUDDY PRIVATE TOUR – 24/7 Hotline / WhatsApp Support:",

        // Header Navigation
        nav_home: "HOME",
        nav_about: "ABOUT US",
        nav_tour: "TOURS",
        nav_north: "HANOI & NORTH VIETNAM",
        nav_central: "DA NANG & CENTRAL VIETNAM",
        nav_south: "HO CHI MINH CITY & SOUTH",
        nav_other_services: "CAR RENTAL & HOTELS",
        nav_videos: "VIDEOS",
        nav_contact: "CONTACT",
        nav_cart: "Cart",

        // Hero Slider
        hero_tagline_1: "Exclusive Private Tour Services",
        hero_title_1: "Explore Vietnam In Your Own Unique Way",
        hero_sub_1: "Tailor-made private journeys for families & friends featuring modern private cars and licensed local tour guides.",
        hero_tagline_2: "Seasonal Tour Offers",
        hero_title_2: "Cherish Every Unforgettable Moment",
        hero_sub_2: "Indulge in 5-star standard private services with complete itinerary flexibility.",
        hero_tagline_3: "Distinctive Travel Experiences",
        hero_title_3: "Hanoi • Da Nang • Phu Quoc • Saigon",
        hero_sub_3: "Easy online booking, instant payment via VietQR, and immediate electronic voucher delivery.",

        // Floating Search Pill
        search_dest_label: "Destination",
        search_dest_placeholder: "All Destinations",
        search_dest_north: "Hanoi & North Vietnam (Halong Bay, Ninh Binh)",
        search_dest_central: "Da Nang & Central Vietnam (Hoi An, Hue, Ba Na)",
        search_dest_south: "Ho Chi Minh City & South (Mekong Delta, Phu Quoc)",
        search_date_label: "Departure Date",
        search_guest_label: "Number of Guests",
        search_guest_1: "1 Guest (Solo Private)",
        search_guest_2: "2 Guests (Couples / Friends)",
        search_guest_4: "3 - 5 Guests (Family)",
        search_guest_8: "6+ Guests (Private Group)",
        btn_search: "Search Tours",

        // Category Section Header
        cat_badge_selected: "Hand-Picked Itineraries",
        cat_desc_common: "Experience luxury private journeys with modern vehicles and fully customizable itineraries.",
        cat_north_title: "HANOI & NORTHERN VIETNAM TOURS",
        cat_central_title: "DA NANG & CENTRAL VIETNAM TOURS",
        cat_south_title: "HO CHI MINH CITY & SOUTHERN VIETNAM TOURS",
        sec_videos_badge: "Real Journey Clips",
        sec_videos_title: "JOURNEYS WITH BUDDY PRIVATE TOUR",

        // Tour Cards
        card_badge_private: "Private Tour",
        card_reviews_suffix: "(86 reviews)",
        card_perk_car: "Private car",
        card_perk_guide: "Expert guide",
        card_perk_ticket: "Entry tickets",
        card_perk_pickup: "Hotel pickup",
        card_price_from: "From",
        card_btn_view: "View Tour",

        // Tour Listing Page
        tour_page_desc: "All premium private tour packages include private guide, hotel pickup/drop-off, and 5-star standard care.",
        filter_all_tours: "All Tours",
        filter_north: "Hanoi & North Vietnam",
        filter_central: "Da Nang & Central Vietnam",
        filter_south: "Ho Chi Minh City & South",
        no_tours_found: "No tours found",
        no_tours_desc: "Please select another category or contact our hotline for a customized itinerary.",
        btn_view_all_tours: "View All Tours",

        // Breadcrumbs & Checkout
        crumb_home: "Home",
        crumb_tour: "Tours",
        crumb_checkout: "Checkout",
        crumb_order_received: "Ticket Confirmation & VietQR",
        crumb_tour_payment: "Tour Order Checkout",
        detail_badge_private: "Private Tour",
        detail_rating_reviews: "(86 international traveler reviews)",
        gallery_btn_view_all: "View all photos",
        info_vehicle_lbl: "Transportation",
        info_vehicle_val: "Modern Private Car",
        info_guide_lbl: "Tour Guide",
        info_guide_val: "Private English/Vietnamese",
        info_type_lbl: "Tour Type",
        info_type_val: "100% Private Tour",
        info_pickup_lbl: "Pickup & Dropoff",
        info_pickup_val: "Complimentary Hotel Pickup",
        info_duration_lbl: "Duration",
        sec_highlights_title: "TOUR HIGHLIGHTS",
        sec_itinerary_title: "DETAILED ITINERARY",
        timeline_day_1_title: "Morning: Hotel Pickup & Signature Sightseeing",
        timeline_day_1_body: "Private car and guide pick you up promptly at your hotel or meeting point. Begin your journey visiting world-famous landmarks while enjoying insightful cultural stories from your dedicated local guide.",
        timeline_day_2_title: "Afternoon: Authentic Local Cuisine & Cultural Discovery",
        timeline_day_2_body: "Savor a delicious traditional lunch at handpicked local restaurants. Continue exploring scenic attractions, capturing photos, and shopping for specialties before a safe private drop-off at your hotel.",
        sec_included_title: "INCLUDED IN TOUR",
        sec_excluded_title: "EXCLUDED FROM TOUR",
        inc_item_1: "Modern air-conditioned private vehicle throughout",
        inc_item_2: "Professional English / Vietnamese speaking private guide",
        inc_item_3: "All entrance fees and sightseeing tickets",
        inc_item_4: "Complimentary bottled water and cold towels",
        inc_item_5: "Full-coverage travel insurance",
        exc_item_1: "Personal expenses outside program (shopping, laundry)",
        exc_item_2: "Gratuities & tips for driver and tour guide",
        exc_item_3: "VAT invoice (if tax receipt requested)",
        sec_overview_title: "ABOUT THIS TRIP",
        sec_related_badge: "More Recommendations",
        sec_related_title: "OTHER POPULAR PRIVATE TOURS",

        // Sticky Booking Box
        booking_price_lbl: "Special private tour price",
        booking_price_unit: "/ guest",
        booking_discount_badge: "Save 15%",
        booking_date_lbl: "Select Departure Date:",
        booking_guests_lbl: "Number of Guests:",
        booking_calc_label: "Tour price x",
        booking_calc_guest_unit: "guests:",
        booking_calc_service_lbl: "Private guide & service fees:",
        booking_calc_free: "Free",
        booking_calc_subtotal_lbl: "Subtotal:",
        btn_book_now: "BOOK THIS TOUR",
        guarantee_free_cancel: "Free cancellation up to 24 hours in advance",
        guarantee_instant_confirm: "Instant confirmation & VietQR E-voucher",
        guarantee_quality: "Guaranteed 5-star service excellence",
        mobile_price_label: "From",
        mobile_btn_book: "Book Now",

        // Cart Page
        cart_page_heading: "YOUR SHOPPING CART",
        cart_page_desc: "Review your selected private tour packages before proceeding to checkout and generating your VietQR electronic voucher.",
        cart_empty_title: "Your cart is currently empty!",
        cart_empty_desc: "Explore our exclusive private tours across North, Central, and South Vietnam.",
        cart_btn_explore: "Explore Tours Now",
        cart_list_heading: "Selected Tour Packages",
        cart_badge_tickets: "tickets selected",
        cart_th_info: "Tour Details",
        cart_th_price: "Unit Price",
        cart_th_qty: "Guests",
        cart_th_total: "Total",
        cart_btn_continue: "Choose More Tours",
        cart_btn_clear: "Clear Cart",
        cart_summary_heading: "Order Summary",
        cart_summary_subtotal: "Tours Subtotal:",
        cart_summary_guide: "Private Driver & Guide:",
        cart_summary_free: "Included",
        cart_summary_insurance: "Travel Insurance:",
        cart_summary_tax: "Estimated Tax:",
        cart_summary_total: "Total Payment:",
        cart_summary_vat_note: "(Includes all entrance fees & private service)",
        cart_btn_checkout: "PROCEED TO SECURE CHECKOUT",
        cart_trust_title: "100% SATISFACTION GUARANTEED",
        cart_trust_1: "Instant reservation confirmation & VietQR E-Ticket",
        cart_trust_2: "Dedicated private English-speaking tour guide",
        cart_trust_3: "Full-coverage licensed travel insurance included",

        // Consultation Form
        form_title: "CUSTOMIZE YOUR PRIVATE TOUR",
        form_subtitle: "Leave your contact details and our travel experts will respond within 15 minutes",
        form_fullname_lbl: "FULL NAME *",
        form_fullname_ph: "e.g. John Smith",
        form_phone_lbl: "PHONE / WHATSAPP *",
        form_phone_ph: "e.g. +84 567 599 998",
        form_email_lbl: "EMAIL ADDRESS",
        form_email_ph: "e.g. john@example.com",
        form_dest_lbl: "DESTINATION OF INTEREST",
        form_dest_select: "-- Select Destination --",
        form_notes_lbl: "SPECIAL REQUESTS / DATES",
        form_notes_ph: "Tell us about your preferred schedule, number of travelers, hotel preferences...",
        form_btn_submit: "SEND REQUEST NOW",

        // Footer
        footer_brand_desc: "Specialist in organizing premium, personalized private travel experiences in Vietnam. Accompanied by knowledgeable local guides and modern private vehicles.",
        footer_license_lbl: "Tour Operator License:",
        footer_tax_lbl: "Enterprise Reg Code:",
        footer_col_nav: "QUICK LINKS",
        footer_col_dest: "POPULAR DESTINATIONS",
        footer_col_support: "CUSTOMER SUPPORT",
        footer_dest_hanoi: "Hanoi & Old Quarter",
        footer_dest_halong: "Halong Bay & Ninh Binh",
        footer_dest_danang: "Da Nang & Ba Na Hills",
        footer_dest_hoian: "Hoi An Ancient Town & Hue",
        footer_dest_saigon: "Ho Chi Minh City & Cu Chi",
        footer_dest_phuquoc: "Phu Quoc Island",
        footer_247_support: "24/7 Online Customer Support",
        footer_copyright: "All Rights Reserved. Crafted with Passion for Travel Excellence."
    },
    vi: {
        // Top Ticker
        ticker_welcome: "✨ Chào mừng bạn đến với BUDDY PRIVATE TOUR – Hotline / Zalo hỗ trợ 24/7:",

        // Header Navigation
        nav_home: "TRANG CHỦ",
        nav_about: "GIỚI THIỆU",
        nav_tour: "TOUR DU LỊCH",
        nav_north: "HÀ NỘI & MIỀN BẮC",
        nav_central: "ĐÀ NẴNG & MIỀN TRUNG",
        nav_south: "TP. HỒ CHÍ MINH & MIỀN NAM",
        nav_other_services: "THUÊ XE & KHÁCH SẠN",
        nav_videos: "VIDEO HÀNH TRÌNH",
        nav_contact: "LIÊN HỆ",
        nav_cart: "Giỏ hàng",

        // Hero Slider
        hero_tagline_1: "Dịch Vụ Tour Riêng Đẳng Cấp",
        hero_title_1: "Khám Phá Việt Nam Theo Cách Riêng Của Bạn",
        hero_sub_1: "Hành trình cá nhân hóa cho gia đình & bạn bè với đội xe riêng hiện đại cùng hướng dẫn viên bản địa am hiểu.",
        hero_tagline_2: "Ưu Đãi Tour Theo Mùa",
        hero_title_2: "Trọn Vẹn Từng Khoảnh Khắc Đáng Nhớ",
        hero_sub_2: "Tận hưởng dịch vụ chuẩn 5 sao và lịch trình linh hoạt tuyệt đối.",
        hero_tagline_3: "Trải Nghiệm Du Lịch Khác Biệt",
        hero_title_3: "Hà Nội • Đà Nẵng • Phú Quốc • Sài Gòn",
        hero_sub_3: "Đặt tour dễ dàng, thanh toán quét mã VietQR nhận vé điện tử tức thì.",

        // Floating Search Pill
        search_dest_label: "Điểm Đến",
        search_dest_placeholder: "Tất cả điểm đến",
        search_dest_north: "Hà Nội & Miền Bắc (Hạ Long, Ninh Bình)",
        search_dest_central: "Đà Nẵng & Miền Trung (Hội An, Huế, Bà Nà)",
        search_dest_south: "TP.HCM & Miền Nam (Mê Kông, Phú Quốc)",
        search_date_label: "Ngày Khởi Hành",
        search_guest_label: "Số Khách",
        search_guest_1: "1 Khách (Tour riêng)",
        search_guest_2: "2 Khách (Cặp đôi / Bạn bè)",
        search_guest_4: "3 - 5 Khách (Gia đình)",
        search_guest_8: "6+ Khách (Đoàn riêng)",
        btn_search: "Tìm Kiếm",

        // Category Section Header
        cat_badge_selected: "Lịch Trình Chọn Lọc",
        cat_desc_common: "Trải nghiệm dịch vụ tour riêng cao cấp với đội xe tiện nghi và lịch trình tự do điều chỉnh.",
        cat_north_title: "HÀ NỘI & MIỀN BẮC (NORTHERN VIETNAM TOURS)",
        cat_central_title: "ĐÀ NẴNG & MIỀN TRUNG (CENTRAL VIETNAM TOURS)",
        cat_south_title: "TP. HỒ CHÍ MINH & MIỀN NAM (SOUTHERN VIETNAM TOURS)",
        sec_videos_badge: "Video Thực Tế",
        sec_videos_title: "HÀNH TRÌNH CÙNG BUDDY PRIVATE TOUR",

        // Tour Cards
        card_badge_private: "Private Tour",
        card_reviews_suffix: "(86 đánh giá)",
        card_perk_car: "Xe riêng",
        card_perk_guide: "HDV chuyên nghiệp",
        card_perk_ticket: "Vé tham quan",
        card_perk_pickup: "Đưa đón tận nơi",
        card_price_from: "Giá từ",
        card_btn_view: "Xem Tour",

        // Tour Listing Page
        tour_page_desc: "Tất cả các gói tour riêng cao cấp trọn gói hướng dẫn viên riêng, xe đưa đón tận nơi và dịch vụ chăm sóc chuẩn 5 sao.",
        filter_all_tours: "Tất cả các Tour",
        filter_north: "Hà Nội & Miền Bắc",
        filter_central: "Đà Nẵng & Miền Trung",
        filter_south: "TP.HCM & Miền Nam",
        no_tours_found: "Không tìm thấy tour phù hợp",
        no_tours_desc: "Vui lòng chọn danh mục khác hoặc liên hệ hotline để được thiết kế tour riêng.",
        btn_view_all_tours: "Xem Tất Cả Tour",

        // Breadcrumbs & Checkout
        crumb_home: "Trang chủ",
        crumb_tour: "Tour",
        crumb_checkout: "Thanh toán",
        crumb_order_received: "Xác nhận vé & Quét mã VietQR",
        crumb_tour_payment: "Thanh toán đơn đặt tour",
        detail_badge_private: "Private Tour",
        detail_rating_reviews: "(86 đánh giá từ du khách quốc tế)",
        gallery_btn_view_all: "Xem tất cả ảnh",
        info_vehicle_lbl: "Phương tiện",
        info_vehicle_val: "Xe riêng đời mới",
        info_guide_lbl: "Hướng dẫn viên",
        info_guide_val: "Anh / Việt riêng",
        info_type_lbl: "Loại hình",
        info_type_val: "Tour riêng 100%",
        info_pickup_lbl: "Đón & Trả",
        info_pickup_val: "Tận nơi miễn phí",
        info_duration_lbl: "Thời lượng",
        sec_highlights_title: "ĐIỂM NHẤN HÀNH TRÌNH",
        sec_itinerary_title: "LỊCH TRÌNH CHI TIẾT",
        timeline_day_1_title: "Buổi Sáng: Đón Khách & Khám Phá Thắng Cảnh Nổi Bật",
        timeline_day_1_body: "Xe riêng và hướng dẫn viên đón quý khách tại khách sạn hoặc điểm hẹn đúng giờ. Bắt đầu hành trình tham quan với các điểm đến nổi tiếng, lắng nghe những câu chuyện văn hóa lịch sử thú vị từ hướng dẫn viên địa phương.",
        timeline_day_2_title: "Buổi Trưa & Chiều: Thưởng Thức Ẩm Thực & Trải Nghiệm Văn Hóa",
        timeline_day_2_body: "Thưởng thức bữa trưa đậm đà phong vị bản địa tại nhà hàng truyền thống. Tiếp tục khám phá các danh thắng cảnh đẹp, chụp ảnh kỷ niệm và tự do mua sắm đặc sản trước khi xe riêng đưa quý khách về lại điểm đón an toàn.",
        sec_included_title: "BAO GỒM (INCLUDED)",
        sec_excluded_title: "CHƯA BAO GỒM (EXCLUDED)",
        inc_item_1: "Xe riêng máy lạnh đời mới đưa đón suốt tuyến",
        inc_item_2: "Hướng dẫn viên chuyên nghiệp tiếng Anh / Việt",
        inc_item_3: "Vé tham quan các thắng cảnh trong chương trình",
        inc_item_4: "Nước khoáng và khăn lạnh phục vụ trên xe",
        inc_item_5: "Bảo hiểm du lịch trọn gói",
        exc_item_1: "Chi phí cá nhân ngoài chương trình (mua sắm, giặt ủi)",
        exc_item_2: "Tiền tip tùy tâm cho tài xế và hướng dẫn viên",
        exc_item_3: "Thuế VAT (nếu có yêu cầu xuất hóa đơn)",
        sec_overview_title: "GIỚI THIỆU CHUYẾN ĐI",
        sec_related_badge: "Gợi Ý Thêm",
        sec_related_title: "CÁC TOUR ĐƯỢC YÊU THÍCH KHÁC",

        // Sticky Booking Box
        booking_price_lbl: "Giá ưu đãi tour riêng",
        booking_price_unit: "/ khách",
        booking_discount_badge: "Ưu đãi -15%",
        booking_date_lbl: "Chọn Ngày Đi:",
        booking_guests_lbl: "Số Lượng Khách:",
        booking_calc_label: "Giá tour x",
        booking_calc_guest_unit: "khách:",
        booking_calc_service_lbl: "Phí hướng dẫn viên & phục vụ:",
        booking_calc_free: "Miễn phí",
        booking_calc_subtotal_lbl: "Tổng tiền tạm tính:",
        btn_book_now: "ĐẶT TOUR NGAY",
        guarantee_free_cancel: "Miễn phí hủy tour trước 24 giờ",
        guarantee_instant_confirm: "Xác nhận tức thì & Vé điện tử VietQR",
        guarantee_quality: "Cam kết chất lượng phục vụ 5 sao",
        mobile_price_label: "Giá từ",
        mobile_btn_book: "Đặt Ngay",

        // Cart Page
        cart_page_heading: "GIỎ HÀNG TOUR DU LỊCH",
        cart_page_desc: "Kiểm tra lại các gói tour riêng bạn đã chọn trước khi tiến hành thanh toán và nhận vé điện tử VietQR.",
        cart_empty_title: "Giỏ hàng của bạn đang trống!",
        cart_empty_desc: "Hãy khám phá các tour riêng độc đáo tại Miền Bắc, Miền Trung và Miền Nam ngay bây giờ.",
        cart_btn_explore: "Khám Phá Tour Ngay",
        cart_list_heading: "Danh Sách Tour Đã Chọn",
        cart_badge_tickets: "vé tour",
        cart_th_info: "Thông Tin Tour",
        cart_th_price: "Đơn Giá",
        cart_th_qty: "Số Lượng Khách",
        cart_th_total: "Thành Tiền",
        cart_btn_continue: "Chọn Thêm Tour Khác",
        cart_btn_clear: "Xóa Toàn Bộ Giỏ Hàng",
        cart_summary_heading: "Tóm Tắt Đơn Hàng",
        cart_summary_subtotal: "Tạm tính tour:",
        cart_summary_guide: "Tài xế & HDV riêng:",
        cart_summary_free: "Đã bao gồm",
        cart_summary_insurance: "Bảo hiểm du lịch:",
        cart_summary_tax: "Thuế phí:",
        cart_summary_total: "Tổng thanh toán:",
        cart_summary_vat_note: "(Đã bao gồm toàn bộ vé thắng cảnh & dịch vụ riêng)",
        cart_btn_checkout: "TIẾN HÀNH ĐẶT TOUR & THANH TOÁN",
        cart_trust_title: "AN TÂM 100% KHI ĐẶT TOUR",
        cart_trust_1: "Xác nhận đặt chỗ tức thì & Xuất vé điện tử VietQR",
        cart_trust_2: "Đội ngũ hướng dẫn viên riêng nhiệt tình, chu đáo",
        cart_trust_3: "Bảo hiểm du lịch trọn gói theo quy định",

        // Consultation Form
        form_title: "TƯ VẤN & THIẾT KẾ TOUR RIÊNG",
        form_subtitle: "Để lại thông tin, chuyên viên tư vấn sẽ liên hệ lại với bạn trong vòng 15 phút",
        form_fullname_lbl: "HỌ VÀ TÊN *",
        form_fullname_ph: "Ví dụ: Nguyễn Văn A",
        form_phone_lbl: "SỐ ĐIỆN THOẠI / ZALO *",
        form_phone_ph: "Ví dụ: 0912 345 678",
        form_email_lbl: "ĐỊA CHỈ EMAIL",
        form_email_ph: "Ví dụ: email@domain.com",
        form_dest_lbl: "ĐIỂM ĐẾN QUAN TÂM",
        form_dest_select: "-- Chọn Điểm Đến --",
        form_notes_lbl: "YÊU CẦU ĐẶC BIỆT / NGÀY DỰ KIẾN",
        form_notes_ph: "Nhập thông tin về số người, ngày đi mong muốn, tiêu chuẩn khách sạn...",
        form_btn_submit: "GỬI YÊU CẦU TƯ VẤN",

        // Footer
        footer_brand_desc: "Chuyên gia tổ chức các chuyến du lịch trải nghiệm riêng tư, cá nhân hóa cao cấp tại Việt Nam. Đồng hành cùng hướng dẫn viên bản địa am hiểu văn hóa và đội xe hiện đại.",
        footer_license_lbl: "Giấy phép lữ hành:",
        footer_tax_lbl: "Mã số doanh nghiệp:",
        footer_col_nav: "ĐIỀU HƯỚNG NHANH",
        footer_col_dest: "ĐIỂM ĐẾN NỔI BẬT",
        footer_col_support: "HỖ TRỢ KHÁCH HÀNG",
        footer_dest_hanoi: "Hà Nội & Phố Cổ",
        footer_dest_halong: "Vịnh Hạ Long & Ninh Bình",
        footer_dest_danang: "Đà Nẵng & Bà Nà Hills",
        footer_dest_hoian: "Phố cổ Hội An & Cố Đô Huế",
        footer_dest_saigon: "TP. Hồ Chí Minh & Củ Chi",
        footer_dest_phuquoc: "Đảo Ngọc Phú Quốc",
        footer_247_support: "Hỗ trợ tư vấn 24/7 trực tuyến",
        footer_copyright: "Bảo lưu mọi quyền. Thiết kế tỉ mỉ vì trải nghiệm du lịch hoàn hảo."
    }
};

window.BPT_I18N = {
    currentLang: 'en',

    init: function () {
        // Default to English ('en') if not explicitly set
        const savedLang = localStorage.getItem('app_lang') || 'en';
        this.setLanguage(savedLang, false);
        this.bindEvents();
    },

    setLanguage: function (lang, save = true) {
        if (!BPT_I18N_DATA[lang]) lang = 'en';
        this.currentLang = lang;
        if (save) {
            localStorage.setItem('app_lang', lang);
        }

        // Apply HTML lang attribute
        document.documentElement.setAttribute('lang', lang);

        // Update all elements with data-i18n
        document.querySelectorAll('[data-i18n]').forEach(el => {
            const key = el.getAttribute('data-i18n');
            if (BPT_I18N_DATA[lang] && BPT_I18N_DATA[lang][key]) {
                el.textContent = BPT_I18N_DATA[lang][key];
            }
        });

        // Update all elements with data-i18n-html
        document.querySelectorAll('[data-i18n-html]').forEach(el => {
            const key = el.getAttribute('data-i18n-html');
            if (BPT_I18N_DATA[lang] && BPT_I18N_DATA[lang][key]) {
                el.innerHTML = BPT_I18N_DATA[lang][key];
            }
        });

        // Update placeholder attributes
        document.querySelectorAll('[data-i18n-placeholder]').forEach(el => {
            const key = el.getAttribute('data-i18n-placeholder');
            if (BPT_I18N_DATA[lang] && BPT_I18N_DATA[lang][key]) {
                el.setAttribute('placeholder', BPT_I18N_DATA[lang][key]);
            }
        });

        // Update title attributes
        document.querySelectorAll('[data-i18n-title]').forEach(el => {
            const key = el.getAttribute('data-i18n-title');
            if (BPT_I18N_DATA[lang] && BPT_I18N_DATA[lang][key]) {
                el.setAttribute('title', BPT_I18N_DATA[lang][key]);
            }
        });

        // Update active class on all language buttons across DOM
        document.querySelectorAll('.bpt-lang-btn').forEach(btn => {
            if (btn.getAttribute('data-lang') === lang) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });

        // Dispatch custom event for any dynamically mounted components
        window.dispatchEvent(new CustomEvent('bpt_language_changed', { detail: { lang: lang } }));
    },

    t: function (key) {
        const lang = this.currentLang || 'en';
        return (BPT_I18N_DATA[lang] && BPT_I18N_DATA[lang][key]) ? BPT_I18N_DATA[lang][key] : key;
    },

    bindEvents: function () {
        document.addEventListener('click', function (e) {
            const btn = e.target.closest('.bpt-lang-btn');
            if (btn) {
                e.preventDefault();
                const targetLang = btn.getAttribute('data-lang');
                if (targetLang) {
                    window.BPT_I18N.setLanguage(targetLang, true);
                }
            }
        });
    }
};

// Auto initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
        window.BPT_I18N.init();
    });
} else {
    window.BPT_I18N.init();
}
