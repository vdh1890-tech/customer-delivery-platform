/**
 * KR BLUE METALS - Application Logic
 * Handles Shopping Cart, UI Interaction, and Toast Notifications
 */

class CartManager {
    constructor() {
        this.storageKey = 'kr_cart';
        this.items = this.getCart();
        this.init();
    }

    init() {
        this.updateBadge();
        // Bind global add to cart for convenience
        window.addToCart = (id, name, price, image) => this.addItem(id, name, price, image);
    }

    getCart() {
        const cart = localStorage.getItem(this.storageKey);
        return cart ? JSON.parse(cart) : [];
    }

    saveCart() {
        localStorage.setItem(this.storageKey, JSON.stringify(this.items));
        this.updateBadge();
        // Trigger event for other components
        window.dispatchEvent(new Event('cartUpdated'));
    }

    addItem(id, name, price, image) {
        const existing = this.items.find(item => item.id == id);
        if (existing) {
            existing.quantity++;
        } else {
            this.items.push({ id, name, price, image, quantity: 1 });
        }
        this.saveCart();
        this.showToast(`${name} added to cart`);
    }

    removeItem(id) {
        this.items = this.items.filter(item => item.id != id);
        this.saveCart();
    }

    updateQuantity(id, change) {
        const item = this.items.find(i => i.id == id);
        if (item) {
            item.quantity += change;
            if (item.quantity <= 0) {
                this.removeItem(id);
            } else {
                this.saveCart();
            }
        }
    }

    getTotal() {
        return this.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    }

    updateBadge() {
        const count = this.items.reduce((sum, item) => sum + item.quantity, 0);
        const badge = document.getElementById('cart-count');
        if (badge) badge.innerText = count;
    }

    showToast(message) {
        // Simple toast implementation
        const toast = document.createElement('div');
        toast.style.cssText = `
            position: fixed; bottom: 20px; right: 20px;
            background: var(--primary-navy, #142850); color: white;
            padding: 12px 24px; border-radius: 4px; z-index: 1000;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            animation: slideIn 0.3s ease-out;
        `;
        toast.innerText = message;
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    // Initialize globally so other scripts can access it
    window.appCart = new CartManager();

    // Slider Logic
    let currentSlide = 0;
    const slides = document.querySelectorAll('.hero-slide');
    const dots = document.querySelectorAll('.dot'); // Re-select inside event

    function showSlide(n) {
        if (slides.length === 0) return;

        // Remove active from slides and dots
        slides.forEach(s => s.classList.remove('active'));
        dots.forEach(d => d.classList.remove('active'));

        // Handle wrapping carefully
        currentSlide = ((n % slides.length) + slides.length) % slides.length;

        // Add active to current
        slides[currentSlide].classList.add('active');
        if (dots[currentSlide]) dots[currentSlide].classList.add('active');
    }

    // Attach to window so HTML onclicks work (needs global scope or event listeners)
    // Since onclick="changeSlide()" is in HTML, we need to expose these to window
    // Auto Cycle Logic
    let slideInterval;

    function startTimer() {
        stopTimer();
        slideInterval = setInterval(() => {
            window.changeSlide(1);
        }, 6000);
    }

    function stopTimer() {
        if (slideInterval) {
            clearInterval(slideInterval);
        }
    }

    // Attach to window so HTML onclicks work
    window.changeSlide = function (n) {
        stopTimer(); // specific user interaction stops/resets timer
        showSlide(currentSlide + n);
        startTimer();
    };

    window.goToSlide = function (n) {
        stopTimer();
        showSlide(n);
        startTimer();
    };

    // Start initially
    startTimer();
});

// Utility function to toggle password visibility
function togglePasswordVisibility(iconElement) {
    // Safely find the input field inside the same wrapper
    const input = iconElement.parentElement.querySelector('input');
    
    if (input.type === 'password') {
        input.type = 'text';
        iconElement.classList.remove('ph-eye');
        iconElement.classList.add('ph-eye-slash');
    } else {
        input.type = 'password';
        iconElement.classList.remove('ph-eye-slash');
        iconElement.classList.add('ph-eye');
    }
}

