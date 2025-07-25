<?php
require_once 'config/init.php';

// Check if user is logged in and is a patient
if (!isLoggedIn() || !isPasien()) {
    redirect('login.php');
}

$title = "Keranjang Belanja";
include 'includes/user-header.php';
?>

<div class="row">
    <div class="col-12">
        <h2><i class="fas fa-shopping-cart me-2"></i>Keranjang Belanja</h2>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-list me-2"></i>Daftar Obat</h5>
            </div>
            <div class="card-body">
                <div id="cart-items">
                    <div class="text-center text-muted py-5" id="empty-cart">
                        <i class="fas fa-shopping-cart fa-3x mb-3"></i>
                        <h5>Keranjang Kosong</h5>
                        <p>Silakan tambahkan obat ke keranjang terlebih dahulu.</p>
                        <a href="obat.php" class="btn btn-primary">
                            <i class="fas fa-pills me-2"></i>Lihat Obat
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-calculator me-2"></i>Ringkasan Belanja</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Total Item:</span>
                    <span id="total-items">0</span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span>Total Harga:</span>
                    <strong class="text-primary" id="total-price">Rp 0</strong>
                </div>
                <hr>
                <div class="d-grid gap-2">
                    <button class="btn btn-success" id="checkout-btn" onclick="checkout()" disabled>
                        <i class="fas fa-credit-card me-2"></i>Checkout
                    </button>
                    <button class="btn btn-outline-danger" onclick="clearCart()">
                        <i class="fas fa-trash me-2"></i>Kosongkan Keranjang
                    </button>
                </div>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h6><i class="fas fa-info-circle me-2"></i>Informasi</h6>
            </div>
            <div class="card-body">
                <small class="text-muted">
                    <ul class="mb-0">
                        <li>Pastikan obat yang dipilih sesuai dengan resep dokter</li>
                        <li>Konsultasikan dengan apoteker jika ada pertanyaan</li>
                        <li>Simpan obat di tempat yang aman dan kering</li>
                    </ul>
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Checkout Modal -->
<div class="modal fade" id="checkoutModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Pembelian</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="checkout-summary"></div>
                <hr>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Setelah checkout, pesanan Anda akan diproses dan dapat diambil di klinik.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" onclick="processCheckout()">
                    <i class="fas fa-check me-2"></i>Konfirmasi Pembelian
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Load cart on page load
document.addEventListener('DOMContentLoaded', function() {
    loadCartItems();
});

function loadCartItems() {
    let cart = getCart();
    let cartItemsContainer = document.getElementById('cart-items');
    let emptyCart = document.getElementById('empty-cart');
    
    if (cart.length === 0) {
        emptyCart.style.display = 'block';
        document.getElementById('checkout-btn').disabled = true;
    } else {
        emptyCart.style.display = 'none';
        document.getElementById('checkout-btn').disabled = false;
        
        let html = '<div class="table-responsive"><table class="table">';
        html += '<thead><tr><th>Obat</th><th>Harga</th><th>Qty</th><th>Subtotal</th><th>Aksi</th></tr></thead><tbody>';
        
        let totalItems = 0;
        let totalPrice = 0;
        
        cart.forEach(function(item) {
            let subtotal = item.harga * item.qty;
            totalItems += item.qty;
            totalPrice += subtotal;
            
            html += `
                <tr>
                    <td>${item.nama}</td>
                    <td>${formatCurrency(item.harga)}</td>
                    <td>
                        <div class="input-group" style="width: 120px;">
                            <button class="btn btn-outline-secondary btn-sm" onclick="updateQuantity(${item.id}, ${item.qty - 1})">-</button>
                            <input type="number" class="form-control form-control-sm text-center" value="${item.qty}" min="1" onchange="updateQuantity(${item.id}, this.value)">
                            <button class="btn btn-outline-secondary btn-sm" onclick="updateQuantity(${item.id}, ${item.qty + 1})">+</button>
                        </div>
                    </td>
                    <td>${formatCurrency(subtotal)}</td>
                    <td>
                        <button class="btn btn-danger btn-sm" onclick="removeFromCart(${item.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
        
        html += '</tbody></table></div>';
        cartItemsContainer.innerHTML = html;
        
        document.getElementById('total-items').textContent = totalItems;
        document.getElementById('total-price').textContent = formatCurrency(totalPrice);
    }
}

function updateQuantity(obatId, newQty) {
    newQty = parseInt(newQty);
    if (newQty <= 0) {
        removeFromCart(obatId);
    } else {
        updateCartQuantity(obatId, newQty);
        loadCartItems();
    }
}

function checkout() {
    let cart = getCart();
    if (cart.length === 0) return;
    
    let html = '<div class="table-responsive"><table class="table table-sm">';
    html += '<thead><tr><th>Obat</th><th>Qty</th><th>Subtotal</th></tr></thead><tbody>';
    
    let totalPrice = 0;
    cart.forEach(function(item) {
        let subtotal = item.harga * item.qty;
        totalPrice += subtotal;
        html += `<tr><td>${item.nama}</td><td>${item.qty}</td><td>${formatCurrency(subtotal)}</td></tr>`;
    });
    
    html += `</tbody><tfoot><tr><th colspan="2">Total</th><th>${formatCurrency(totalPrice)}</th></tr></tfoot></table></div>`;
    
    document.getElementById('checkout-summary').innerHTML = html;
    new bootstrap.Modal(document.getElementById('checkoutModal')).show();
}

function processCheckout() {
    let cart = getCart();
    if (cart.length === 0) return;
    
    // Send checkout data to server
    fetch('process_checkout.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({cart: cart})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            clearCart();
            bootstrap.Modal.getInstance(document.getElementById('checkoutModal')).hide();
            showNotification('Pembelian berhasil! Pesanan Anda sedang diproses.', 'success');
            setTimeout(() => {
                window.location.href = 'pasien/transaksi.php';
            }, 2000);
        } else {
            showNotification('Terjadi kesalahan: ' + data.message, 'danger');
        }
    })
    .catch(error => {
        showNotification('Terjadi kesalahan saat memproses pesanan.', 'danger');
    });
}

// Override the removeFromCart function to reload items
function removeFromCart(obatId) {
    let cart = getCart();
    cart = cart.filter(item => item.id != obatId);
    saveCart(cart);
    updateCartCount();
    loadCartItems();
    showNotification('Obat berhasil dihapus dari keranjang!', 'info');
}

// Override the clearCart function to reload items
function clearCart() {
    if (confirm('Apakah Anda yakin ingin mengosongkan keranjang?')) {
        localStorage.removeItem('cart');
        updateCartCount();
        loadCartItems();
        showNotification('Keranjang berhasil dikosongkan!', 'info');
    }
}
</script>

<?php include 'includes/user-footer.php'; ?>