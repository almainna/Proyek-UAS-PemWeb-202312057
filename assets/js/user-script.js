// Modern User JavaScript dengan Hamburger Panel - Klinik Alma Sehat

class HamburgerPanel {
    constructor() {
        this.isOpen = false;
        this.hamburgerBtn = null;
        this.overlay = null;
        this.sidebarPanel = null;
        this.mainContent = null;
        
        this.init();
    }
    
    init() {
        this.createElements();
        this.bindEvents();
        this.updateCartCount();
        this.initializeTooltips();
        this.autoHideAlerts();
    }
    
    createElements() {
        // Create hamburger button
        this.hamburgerBtn = document.createElement('button');
        this.hamburgerBtn.className = 'hamburger-btn';
        this.hamburgerBtn.innerHTML = `
            <div class="hamburger-icon">
                <span></span>
                <span></span>
                <span></span>
            </div>
        `;
        
        // Create overlay
        this.overlay = document.createElement('div');
        this.overlay.className = 'overlay';
        
        // Create sidebar panel
        this.sidebarPanel = document.createElement('div');
        this.sidebarPanel.className = 'sidebar-panel';
        
        // Get main content
        this.mainContent = document.querySelector('.main-content') || document.body;
        
        // Add elements to DOM
        document.body.appendChild(this.hamburgerBtn);
        document.body.appendChild(this.overlay);
        document.body.appendChild(this.sidebarPanel);
        
        this.createSidebarContent();
    }
    
    createSidebarContent() {
        const userInfo = this.getUserInfo();
        const menuItems = this.getMenuItems();
        
        this.sidebarPanel.innerHTML = `
            <div class="sidebar-header">
                <h3><i class="fas fa-hospital-alt"></i> Klinik Alma Sehat</h3>
                <p>Melayani dengan sepenuh hati</p>
            </div>
            
            <div class="sidebar-content">
                ${userInfo}
                ${menuItems}
            </div>
        `;
    }
    
    getUserInfo() {
        const userName = window.userSession?.nama || 'Guest';
        const userRole = window.userSession?.role || 'guest';
        const userInitial = userName.charAt(0).toUpperCase();
        
        return `
            <div class="user-info">
                <div class="user-avatar">${userInitial}</div>
                <div class="user-name">${userName}</div>
                <div class="user-role">${userRole}</div>
            </div>
        `;
    }
    
    getMenuItems() {
        const isLoggedIn = window.userSession?.isLoggedIn || false;
        const userRole = window.userSession?.role || 'guest';
        const baseUrl = window.BASE_URL || '';
        
        let menuHTML = `
            <div class="nav-section">
                <div class="nav-section-title">Menu Utama</div>
                <div class="nav-item">
                    <a href="${baseUrl}" class="nav-link">
                        <i class="fas fa-home"></i>
                        Beranda
                    </a>
                </div>
                <div class="nav-item">
                    <a href="${baseUrl}tentang.php" class="nav-link">
                        <i class="fas fa-info-circle"></i>
                        Tentang
                    </a>
                </div>
                <div class="nav-item">
                    <a href="${baseUrl}dokter.php" class="nav-link">
                        <i class="fas fa-user-md"></i>
                        Dokter
                    </a>
                </div>
                <div class="nav-item">
                    <a href="${baseUrl}obat.php" class="nav-link">
                        <i class="fas fa-pills"></i>
                        Obat
                    </a>
                </div>
            </div>
        `;
        
        if (isLoggedIn) {
            if (userRole === 'pasien') {
                menuHTML += `
                    <div class="nav-section">
                        <div class="nav-section-title">Layanan Pasien</div>
                        <div class="nav-item">
                            <a href="${baseUrl}booking.php" class="nav-link">
                                <i class="fas fa-calendar-check"></i>
                                Booking
                            </a>
                        </div>
                        <div class="nav-item">
                            <a href="${baseUrl}keranjang.php" class="nav-link">
                                <i class="fas fa-shopping-cart"></i>
                                Keranjang
                                <span class="badge bg-warning text-dark" id="cart-count-sidebar">0</span>
                            </a>
                        </div>
                        <div class="nav-item">
                            <a href="${baseUrl}pasien/" class="nav-link">
                                <i class="fas fa-user"></i>
                                Dashboard
                            </a>
                        </div>
                        <div class="nav-item">
                            <a href="${baseUrl}pasien/profil.php" class="nav-link">
                                <i class="fas fa-user-edit"></i>
                                Profil
                            </a>
                        </div>
                        <div class="nav-item">
                            <a href="${baseUrl}pasien/transaksi.php" class="nav-link">
                                <i class="fas fa-receipt"></i>
                                Transaksi
                            </a>
                        </div>
                    </div>
                `;
            } else if (userRole === 'admin') {
                menuHTML += `
                    <div class="nav-section">
                        <div class="nav-section-title">Admin Panel</div>
                        <div class="nav-item">
                            <a href="${baseUrl}admin/" class="nav-link">
                                <i class="fas fa-tachometer-alt"></i>
                                Dashboard
                            </a>
                        </div>
                    </div>
                `;
            } else if (userRole === 'dokter') {
                menuHTML += `
                    <div class="nav-section">
                        <div class="nav-section-title">Dokter Panel</div>
                        <div class="nav-item">
                            <a href="${baseUrl}dokter/" class="nav-link">
                                <i class="fas fa-stethoscope"></i>
                                Dashboard
                            </a>
                        </div>
                    </div>
                `;
            }
            
            menuHTML += `
                <div class="nav-section">
                    <div class="nav-section-title">Akun</div>
                    <div class="nav-item">
                        <a href="${baseUrl}logout.php" class="nav-link">
                            <i class="fas fa-sign-out-alt"></i>
                            Logout
                        </a>
                    </div>
                </div>
            `;
        } else {
            menuHTML += `
                <div class="nav-section">
                    <div class="nav-section-title">Akun</div>
                    <div class="nav-item">
                        <a href="${baseUrl}login.php" class="nav-link">
                            <i class="fas fa-sign-in-alt"></i>
                            Login
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="${baseUrl}register.php" class="nav-link">
                            <i class="fas fa-user-plus"></i>
                            Register
                        </a>
                    </div>
                </div>
            `;
        }
        
        return menuHTML;
    }
    
    bindEvents() {
        // Hamburger button click
        this.hamburgerBtn.addEventListener('click', () => {
            this.toggle();
        });
        
        // Overlay click
        this.overlay.addEventListener('click', () => {
            this.close();
        });
        
        // ESC key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen) {
                this.close();
            }
        });
        
        // Window resize
        window.addEventListener('resize', () => {
            if (window.innerWidth > 768 && this.isOpen) {
                this.close();
            }
        });
        
        // Navigation link clicks
        this.sidebarPanel.addEventListener('click', (e) => {
            if (e.target.closest('.nav-link')) {
                setTimeout(() => {
                    this.close();
                }, 150);
            }
        });
    }
    
    toggle() {
        if (this.isOpen) {
            this.close();
        } else {
            this.open();
        }
    }
    
    open() {
        this.isOpen = true;
        this.hamburgerBtn.classList.add('active');
        this.overlay.classList.add('active');
        this.sidebarPanel.classList.add('active');
        this.mainContent.classList.add('shifted');
        
        // Add animations
        this.overlay.classList.add('fade-in');
        this.sidebarPanel.classList.add('slide-in');
        
        // Prevent body scroll
        document.body.style.overflow = 'hidden';
        
        // Update active nav item
        this.updateActiveNavItem();
    }
    
    close() {
        this.isOpen = false;
        this.hamburgerBtn.classList.remove('active');
        this.overlay.classList.remove('active');
        this.sidebarPanel.classList.remove('active');
        this.mainContent.classList.remove('shifted');
        
        // Add animations
        this.overlay.classList.add('fade-out');
        this.sidebarPanel.classList.add('slide-out');
        
        // Restore body scroll
        document.body.style.overflow = '';
        
        // Clean up animation classes
        setTimeout(() => {
            this.overlay.classList.remove('fade-in', 'fade-out');
            this.sidebarPanel.classList.remove('slide-in', 'slide-out');
        }, 300);
    }
    
    updateActiveNavItem() {
        const currentPath = window.location.pathname;
        const navLinks = this.sidebarPanel.querySelectorAll('.nav-link');
        
        navLinks.forEach(link => {
            link.classList.remove('active');
            const href = link.getAttribute('href');
            if (href && currentPath.includes(href.replace(window.BASE_URL || '', ''))) {
                link.classList.add('active');
            }
        });
    }
    
    updateCartCount() {
        const cart = this.getCart();
        const totalItems = cart.reduce((total, item) => total + item.qty, 0);
        
        const cartCountElements = document.querySelectorAll('#cart-count, #cart-count-sidebar');
        cartCountElements.forEach(element => {
            if (element) {
                element.textContent = totalItems;
                element.style.display = totalItems > 0 ? 'inline-flex' : 'none';
            }
        });
    }
    
    getCart() {
        const cart = localStorage.getItem('cart');
        return cart ? JSON.parse(cart) : [];
    }
    
    initializeTooltips() {
        // Initialize Bootstrap tooltips if available
        if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
    }
    
    autoHideAlerts() {
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                if (typeof bootstrap !== 'undefined' && bootstrap.Alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            });
        }, 5000);
    }
}

// Cart Functions
class CartManager {
    static addToCart(obatId, nama, harga) {
        let cart = CartManager.getCart();
        let existingItem = cart.find(item => item.id == obatId);
        
        if (existingItem) {
            existingItem.qty += 1;
        } else {
            cart.push({
                id: obatId,
                nama: nama,
                harga: harga,
                qty: 1
            });
        }
        
        CartManager.saveCart(cart);
        window.hamburgerPanel?.updateCartCount();
        NotificationManager.show('Obat berhasil ditambahkan ke keranjang!', 'success');
    }
    
    static removeFromCart(obatId) {
        let cart = CartManager.getCart();
        cart = cart.filter(item => item.id != obatId);
        CartManager.saveCart(cart);
        window.hamburgerPanel?.updateCartCount();
        NotificationManager.show('Obat berhasil dihapus dari keranjang!', 'info');
    }
    
    static updateCartQuantity(obatId, qty) {
        let cart = CartManager.getCart();
        let item = cart.find(item => item.id == obatId);
        
        if (item) {
            if (qty <= 0) {
                CartManager.removeFromCart(obatId);
            } else {
                item.qty = qty;
                CartManager.saveCart(cart);
                window.hamburgerPanel?.updateCartCount();
            }
        }
    }
    
    static getCart() {
        const cart = localStorage.getItem('cart');
        return cart ? JSON.parse(cart) : [];
    }
    
    static saveCart(cart) {
        localStorage.setItem('cart', JSON.stringify(cart));
    }
    
    static clearCart() {
        localStorage.removeItem('cart');
        window.hamburgerPanel?.updateCartCount();
    }
}

// Notification Manager
class NotificationManager {
    static show(message, type = 'info', duration = 3000) {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = `
            top: 100px; 
            right: 20px; 
            z-index: 9999; 
            min-width: 300px;
            max-width: 400px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            border-radius: 12px;
            border: none;
        `;
        notification.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas fa-${NotificationManager.getIcon(type)} me-2"></i>
                <span>${message}</span>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Auto remove
        setTimeout(() => {
            if (notification.parentNode) {
                notification.classList.remove('show');
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 150);
            }
        }, duration);
    }
    
    static getIcon(type) {
        const icons = {
            success: 'check-circle',
            danger: 'exclamation-triangle',
            warning: 'exclamation-circle',
            info: 'info-circle',
            primary: 'info-circle'
        };
        return icons[type] || 'info-circle';
    }
}

// Form Validation
class FormValidator {
    static validate(formId) {
        const form = document.getElementById(formId);
        if (!form) return false;
        
        let isValid = true;
        
        // Check required fields
        const requiredFields = form.querySelectorAll('[required]');
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });
        
        // Check email format
        const emailFields = form.querySelectorAll('input[type="email"]');
        emailFields.forEach(field => {
            if (field.value && !FormValidator.isValidEmail(field.value)) {
                field.classList.add('is-invalid');
                isValid = false;
            }
        });
        
        return isValid;
    }
    
    static isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
}

// Utility Functions
class Utils {
    static formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('id-ID');
    }
    
    static formatTime(timeString) {
        const time = new Date('2000-01-01 ' + timeString);
        return time.toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit'});
    }
    
    static formatCurrency(amount) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(amount);
    }
    
    static showLoading(element) {
        if (element) {
            element.classList.add('loading');
        }
    }
    
    static hideLoading(element) {
        if (element) {
            element.classList.remove('loading');
        }
    }
    
    static makeAjaxRequest(url, method = 'GET', data = null) {
        return new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();
            xhr.open(method, url);
            xhr.setRequestHeader('Content-Type', 'application/json');
            
            xhr.onload = function() {
                if (xhr.status >= 200 && xhr.status < 300) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        resolve(response);
                    } catch (e) {
                        resolve(xhr.responseText);
                    }
                } else {
                    reject(new Error('Request failed with status: ' + xhr.status));
                }
            };
            
            xhr.onerror = function() {
                reject(new Error('Network error'));
            };
            
            if (data) {
                xhr.send(JSON.stringify(data));
            } else {
                xhr.send();
            }
        });
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize hamburger panel
    window.hamburgerPanel = new HamburgerPanel();
    
// Make functions globally available for backward compatibility
window.addToCart = CartManager.addToCart;
window.removeFromCart = CartManager.removeFromCart;
window.updateCartQuantity = CartManager.updateCartQuantity;
window.clearCart = CartManager.clearCart;
window.showNotification = NotificationManager.show;
window.validateForm = FormValidator.validate;
window.formatDate = Utils.formatDate;
window.formatTime = Utils.formatTime;
window.formatCurrency = Utils.formatCurrency;
window.makeAjaxRequest = Utils.makeAjaxRequest;
window.showLoading = Utils.showLoading;
window.hideLoading = Utils.hideLoading;

// Add showAlert for backward compatibility
window.showAlert = function(type, message) {
    NotificationManager.show(message, type);
};

// Add other missing utility functions
window.formatTanggal = Utils.formatDate;
window.formatWaktu = Utils.formatTime;
    
    // Initialize smooth scrolling
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
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
    
    // Initialize lazy loading for images
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        document.querySelectorAll('img[data-src]').forEach(img => {
            imageObserver.observe(img);
        });
    }
    
    // Add loading states to buttons
    document.querySelectorAll('button[type="submit"], .btn-submit').forEach(btn => {
        btn.addEventListener('click', function() {
            if (this.form && FormValidator.validate(this.form.id)) {
                Utils.showLoading(this);
                this.disabled = true;
                
                setTimeout(() => {
                    Utils.hideLoading(this);
                    this.disabled = false;
                }, 2000);
            }
        });
    });
});