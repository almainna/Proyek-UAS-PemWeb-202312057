// JavaScript untuk Klinik Alma Sehat

// DOM Ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize cart count
    updateCartCount();
    
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        var alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            var bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
});

// Cart Functions
function addToCart(obatId, nama, harga) {
    let cart = getCart();
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
    
    saveCart(cart);
    updateCartCount();
    showNotification('Obat berhasil ditambahkan ke keranjang!', 'success');
}

function removeFromCart(obatId) {
    let cart = getCart();
    cart = cart.filter(item => item.id != obatId);
    saveCart(cart);
    updateCartCount();
    showNotification('Obat berhasil dihapus dari keranjang!', 'info');
}

function updateCartQuantity(obatId, qty) {
    let cart = getCart();
    let item = cart.find(item => item.id == obatId);
    
    if (item) {
        if (qty <= 0) {
            removeFromCart(obatId);
        } else {
            item.qty = qty;
            saveCart(cart);
            updateCartCount();
        }
    }
}

function getCart() {
    let cart = localStorage.getItem('cart');
    return cart ? JSON.parse(cart) : [];
}

function saveCart(cart) {
    localStorage.setItem('cart', JSON.stringify(cart));
}

function updateCartCount() {
    let cart = getCart();
    let totalItems = cart.reduce((total, item) => total + item.qty, 0);
    
    let cartCountElement = document.getElementById('cart-count');
    if (cartCountElement) {
        cartCountElement.textContent = totalItems;
        cartCountElement.style.display = totalItems > 0 ? 'inline-flex' : 'none';
    }
}

function clearCart() {
    localStorage.removeItem('cart');
    updateCartCount();
}

// Notification Function
function showNotification(message, type = 'info') {
    // Create notification element
    let notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 100px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Add to body
    document.body.appendChild(notification);
    
    // Auto remove after 3 seconds
    setTimeout(function() {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 3000);
}

// Alias for backward compatibility
function showAlert(type, message) {
    showNotification(message, type);
}

// Form Validation
function validateForm(formId) {
    let form = document.getElementById(formId);
    let isValid = true;
    
    // Check required fields
    let requiredFields = form.querySelectorAll('[required]');
    requiredFields.forEach(function(field) {
        if (!field.value.trim()) {
            field.classList.add('is-invalid');
            isValid = false;
        } else {
            field.classList.remove('is-invalid');
        }
    });
    
    // Check email format
    let emailFields = form.querySelectorAll('input[type="email"]');
    emailFields.forEach(function(field) {
        if (field.value && !isValidEmail(field.value)) {
            field.classList.add('is-invalid');
            isValid = false;
        }
    });
    
    return isValid;
}

function isValidEmail(email) {
    let emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Date and Time Functions
function formatDate(dateString) {
    let date = new Date(dateString);
    return date.toLocaleDateString('id-ID');
}

function formatTime(timeString) {
    let time = new Date('2000-01-01 ' + timeString);
    return time.toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit'});
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(amount);
}

// AJAX Helper
function makeAjaxRequest(url, method = 'GET', data = null) {
    return new Promise((resolve, reject) => {
        let xhr = new XMLHttpRequest();
        xhr.open(method, url);
        xhr.setRequestHeader('Content-Type', 'application/json');
        
        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                try {
                    let response = JSON.parse(xhr.responseText);
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

// Loading Spinner
function showLoading(element) {
    element.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Loading...';
    element.disabled = true;
}

function hideLoading(element, originalText) {
    element.innerHTML = originalText;
    element.disabled = false;
}

// Confirmation Dialog
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

// Search Function
function searchTable(inputId, tableId) {
    let input = document.getElementById(inputId);
    let table = document.getElementById(tableId);
    let rows = table.getElementsByTagName('tr');
    
    input.addEventListener('keyup', function() {
        let filter = input.value.toLowerCase();
        
        for (let i = 1; i < rows.length; i++) {
            let row = rows[i];
            let cells = row.getElementsByTagName('td');
            let found = false;
            
            for (let j = 0; j < cells.length; j++) {
                if (cells[j].textContent.toLowerCase().indexOf(filter) > -1) {
                    found = true;
                    break;
                }
            }
            
            row.style.display = found ? '' : 'none';
        }
    });
}

// Print Function
function printElement(elementId) {
    let element = document.getElementById(elementId);
    let printWindow = window.open('', '_blank');
    
    printWindow.document.write(`
        <html>
        <head>
            <title>Print</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <style>
                @media print {
                    .no-print { display: none !important; }
                }
            </style>
        </head>
        <body>
            ${element.innerHTML}
        </body>
        </html>
    `);
    
    printWindow.document.close();
    printWindow.print();
}