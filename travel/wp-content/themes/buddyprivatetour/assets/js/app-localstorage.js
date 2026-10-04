/**
 * LocalStorage Engine for Exported Static HTML Site
 * Fully decouples the frontend from database & backend servers.
 * Handles product catalog, image path fixes across subfolders, live cart badges, 
 * pre-populated active cart items, and persistent order history.
 */

const BPT_Storage = {
    KEYS: {
        PRODUCTS: 'bpt_products',
        CATEGORIES: 'bpt_categories',
        CART: 'bpt_cart',
        ORDERS: 'bpt_orders'
    },

    init: function (seedProducts = [], seedCategories = [], seedCart = [], seedOrders = []) {
        // Always overwrite catalog data to show the latest exported prices, images, and texts
        if (seedProducts.length > 0) {
            localStorage.setItem(this.KEYS.PRODUCTS, JSON.stringify(seedProducts));
        }
        if (seedCategories.length > 0) {
            localStorage.setItem(this.KEYS.CATEGORIES, JSON.stringify(seedCategories));
        }

        // Seed active cart if local cart is empty
        const currentCart = JSON.parse(localStorage.getItem(this.KEYS.CART) || '[]');
        if (currentCart.length === 0 && seedCart && seedCart.length > 0) {
            localStorage.setItem(this.KEYS.CART, JSON.stringify(seedCart));
        } else if (!localStorage.getItem(this.KEYS.CART)) {
            localStorage.setItem(this.KEYS.CART, JSON.stringify([]));
        }

        // Seed orders if local orders are empty
        const currentOrders = JSON.parse(localStorage.getItem(this.KEYS.ORDERS) || '[]');
        if (currentOrders.length === 0 && seedOrders && seedOrders.length > 0) {
            localStorage.setItem(this.KEYS.ORDERS, JSON.stringify(seedOrders));
        } else if (!localStorage.getItem(this.KEYS.ORDERS)) {
            localStorage.setItem(this.KEYS.ORDERS, JSON.stringify([]));
        }

        this.updateBadge();
    },

    fixImagePath: function (imagePath) {
        if (!imagePath) return 'https://images.unsplash.com/photo-1528127269322-539801943592?auto=format&fit=crop&w=800&q=80';
        if (imagePath.startsWith('http://') || imagePath.startsWith('https://')) {
            return imagePath;
        }

        const isSubfolder = window.location.pathname.includes('/products/');
        if (isSubfolder) {
            return '../' + imagePath.replace(/^\.\//, '').replace(/^\//, '');
        }
        return imagePath.replace(/^\.\//, '').replace(/^\//, '');
    },

    getProducts: function () {
        return JSON.parse(localStorage.getItem(this.KEYS.PRODUCTS) || '[]');
    },

    getCategories: function () {
        return JSON.parse(localStorage.getItem(this.KEYS.CATEGORIES) || '[]');
    },

    getProductById: function (id) {
        const products = this.getProducts();
        return products.find(p => p.id == id) || null;
    },

    getCart: function () {
        return JSON.parse(localStorage.getItem(this.KEYS.CART) || '[]');
    },

    getOrders: function () {
        return JSON.parse(localStorage.getItem(this.KEYS.ORDERS) || '[]');
    },

    saveCart: function (cart) {
        localStorage.setItem(this.KEYS.CART, JSON.stringify(cart));
        this.updateBadge();
    },

    addToCart: function (productId, quantity = 1) {
        const product = this.getProductById(productId);
        if (!product) return false;

        let cart = this.getCart();
        let existingIndex = cart.findIndex(item => item.id == productId);

        let catName = product.category_name || '';
        if (!catName && product.category_id) {
            const cat = this.getCategories().find(c => c.id == product.category_id);
            if (cat) catName = cat.name;
        }

        if (existingIndex > -1) {
            cart[existingIndex].quantity += parseInt(quantity);
        } else {
            cart.push({
                id: product.id,
                title: product.title,
                price: parseFloat(product.price),
                formatted_price: product.formatted_price,
                image: product.image,
                duration: product.duration,
                category_id: product.category_id,
                category_name: catName,
                quantity: parseInt(quantity)
            });
        }

        this.saveCart(cart);
        
        // Redirect to cart page depending on subfolder depth
        const isSubfolder = window.location.pathname.includes('/products/');
        window.location.href = isSubfolder ? '../cart.html' : 'cart.html';
        
        return true;
    },

    updateCartQuantity: function (productId, quantity) {
        let cart = this.getCart();
        let index = cart.findIndex(item => item.id == productId);
        if (index > -1) {
            if (quantity <= 0) {
                cart.splice(index, 1);
            } else {
                cart[index].quantity = parseInt(quantity);
            }
            this.saveCart(cart);
        }
    },

    removeFromCart: function (productId) {
        let cart = this.getCart();
        cart = cart.filter(item => item.id != productId);
        this.saveCart(cart);
    },

    clearCart: function () {
        localStorage.setItem(this.KEYS.CART, JSON.stringify([]));
        this.updateBadge();
    },

    clearOrders: function () {
        localStorage.setItem(this.KEYS.ORDERS, JSON.stringify([]));
    },

    getCartCount: function () {
        const cart = this.getCart();
        return cart.length;
    },

    getCartTotal: function () {
        const cart = this.getCart();
        return cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    },

    updateBadge: function () {
        const badges = document.querySelectorAll('.cart-badge');
        const count = this.getCartCount();
        badges.forEach(b => b.textContent = count);
    },

    placeOrder: function (customerData) {
        const cart = this.getCart();
        if (cart.length === 0) return { success: false, message: 'Your cart is empty!' };

        const orders = this.getOrders();
        const orderNum = Math.floor(300 + Math.random() * 699);
        const orderCode = 'BUDDYPRIVATETOU' + orderNum;

        const newOrder = {
            order_num: orderNum,
            order_code: orderCode,
            customer: customerData,
            items: cart,
            total_amount: this.getCartTotal(),
            created_at: new Date().toISOString().replace('T', ' ').substring(0, 19)
        };

        orders.unshift(newOrder); // Add newest order first
        localStorage.setItem(this.KEYS.ORDERS, JSON.stringify(orders));
        this.clearCart();

        return { success: true, order_num: orderNum, order_code: orderCode, order: newOrder };
    },

    formatCurrency: function (amount) {
        return new Intl.NumberFormat('vi-VN').format(amount) + ' đ';
    }
};

document.addEventListener('DOMContentLoaded', function () {
    BPT_Storage.updateBadge();

    // Fix YouTube Error 153 when viewing static files via file:/// protocol
    if (window.location.protocol === 'file:') {
        document.querySelectorAll('.home-video-wrapper').forEach(function(wrap) {
            const iframe = wrap.querySelector('iframe');
            if (!iframe) return;
            const src = iframe.getAttribute('src') || '';
            const match = src.match(/embed\/([a-zA-Z0-9_-]+)/);
            if (match && match[1]) {
                const videoId = match[1];
                wrap.innerHTML = `
                    <a href="https://www.youtube.com/watch?v=${videoId}" target="_blank" rel="noopener noreferrer" style="display: block; position: absolute; top: 0; left: 0; width: 100%; height: 100%; text-decoration: none; overflow: hidden; background: #000;">
                        <img src="https://img.youtube.com/vi/${videoId}/hqdefault.jpg" alt="YouTube Video" style="width: 100%; height: 100%; object-fit: cover; opacity: 0.9; transition: transform 0.3s ease, opacity 0.3s ease;">
                        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 68px; height: 48px; background: rgba(255, 0, 0, 0.9); border-radius: 12px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 15px rgba(0,0,0,0.4); transition: transform 0.2s ease, background 0.2s ease;">
                            <svg height="24" viewBox="0 0 24 24" width="24" fill="#ffffff" style="margin-left: 2px;"><path d="M8 5v14l11-7z"/></svg>
                        </div>
                        <div style="position: absolute; bottom: 10px; right: 10px; background: rgba(0,0,0,0.8); color: #fff; font-size: 0.78rem; font-weight: 700; padding: 4px 10px; border-radius: 4px; display: flex; align-items: center; gap: 6px;">
                            <i class="fa-brands fa-youtube" style="color: #ff0000; font-size: 1rem;"></i> Xem trên YouTube
                        </div>
                    </a>
                `;
            }
        });
    }
});
