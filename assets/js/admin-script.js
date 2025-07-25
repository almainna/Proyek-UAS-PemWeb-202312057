// Modern Admin JavaScript dengan Hamburger Panel - Klinik Alma Sehat

class AdminHamburgerPanel {
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
    this.initializeTooltips();
    this.autoHideAlerts();
    this.initializeDataTables();
  }

  createElements() {
    // Create hamburger button
    this.hamburgerBtn = document.createElement("button");
    this.hamburgerBtn.className = "admin-hamburger-btn";
    this.hamburgerBtn.innerHTML = `
            <div class="admin-hamburger-icon">
                <span></span>
                <span></span>
                <span></span>
            </div>
        `;

    // Create overlay
    this.overlay = document.createElement("div");
    this.overlay.className = "admin-overlay";

    // Create sidebar panel
    this.sidebarPanel = document.createElement("div");
    this.sidebarPanel.className = "admin-sidebar-panel";

    // Get main content
    this.mainContent =
      document.querySelector(".admin-main-content") ||
      document.querySelector(".main-content") ||
      document.body;

    // Add admin class to main content if not exists
    if (!this.mainContent.classList.contains("admin-main-content")) {
      this.mainContent.classList.add("admin-main-content");
    }

    // Add elements to DOM
    document.body.appendChild(this.hamburgerBtn);
    document.body.appendChild(this.overlay);
    document.body.appendChild(this.sidebarPanel);

    this.createSidebarContent();
  }

  createSidebarContent() {
    const userInfo = this.getUserInfo();
    const menuItems = this.getAdminMenuItems();

    this.sidebarPanel.innerHTML = `
            <div class="admin-sidebar-header">
                <h3><i class="fas fa-shield-alt"></i> Admin Panel</h3>
                <p>Klinik Alma Sehat</p>
            </div>
            
            <div class="admin-sidebar-content">
                ${userInfo}
                ${menuItems}
            </div>
        `;
  }

  getUserInfo() {
    const userName = window.userSession?.nama || "Administrator";
    const userRole = "Administrator";
    const userInitial = userName.charAt(0).toUpperCase();

    return `
            <div class="admin-user-info">
                <div class="admin-user-avatar">${userInitial}</div>
                <div class="admin-user-name">${userName}</div>
                <div class="admin-user-role">${userRole}</div>
            </div>
        `;
  }

  getAdminMenuItems() {
    const baseUrl = window.BASE_URL || "../";
    const adminUrl = baseUrl + "admin/";

    return `
            <div class="admin-nav-section">
                <div class="admin-nav-section-title">Dashboard</div>
                <div class="admin-nav-item">
                    <a href="${adminUrl}" class="admin-nav-link">
                        <i class="fas fa-tachometer-alt"></i>
                        Dashboard
                    </a>
                </div>
            </div>
            
            <div class="admin-nav-section">
                <div class="admin-nav-section-title">Manajemen User</div>
                <div class="admin-nav-item">
                    <a href="${adminUrl}users.php" class="admin-nav-link">
                        <i class="fas fa-users"></i>
                        Kelola User
                    </a>
                </div>
                <div class="admin-nav-item">
                    <a href="${adminUrl}dokter.php" class="admin-nav-link">
                        <i class="fas fa-user-md"></i>
                        Kelola Dokter
                    </a>
                </div>
                <div class="admin-nav-item">
                    <a href="${adminUrl}pasien.php" class="admin-nav-link">
                        <i class="fas fa-user-injured"></i>
                        Kelola Pasien
                    </a>
                </div>
            </div>
            
            <div class="admin-nav-section">
                <div class="admin-nav-section-title">Layanan</div>
                <div class="admin-nav-item">
                    <a href="${adminUrl}obat.php" class="admin-nav-link">
                        <i class="fas fa-pills"></i>
                        Kelola Obat
                    </a>
                </div>
                <div class="admin-nav-item">
                    <a href="${adminUrl}booking.php" class="admin-nav-link">
                        <i class="fas fa-calendar-check"></i>
                        Kelola Booking
                        <span class="badge bg-warning text-dark" id="booking-pending-count">0</span>
                    </a>
                </div>
                <div class="admin-nav-item">
                    <a href="${adminUrl}transaksi.php" class="admin-nav-link">
                        <i class="fas fa-shopping-cart"></i>
                        Kelola Transaksi
                        <span class="badge bg-info" id="transaksi-pending-count">0</span>
                    </a>
                </div>
            </div>
            
            <div class="admin-nav-section">
                <div class="admin-nav-section-title">Laporan & Backup</div>
                <div class="admin-nav-item">
                    <a href="${adminUrl}laporan.php" class="admin-nav-link">
                        <i class="fas fa-chart-bar"></i>
                        Laporan
                    </a>
                </div>
                
            <div class="admin-nav-section">
                <div class="admin-nav-section-title">Sistem</div>   
                <div class="admin-nav-item">
                    <a href="${baseUrl}" class="admin-nav-link">
                        <i class="fas fa-globe"></i>
                        Lihat Website
                    </a>
                </div>
                <div class="admin-nav-item">
                    <a href="${baseUrl}logout.php" class="admin-nav-link">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                </div>
            </div>
        `;
  }

  bindEvents() {
    // Hamburger button click
    this.hamburgerBtn.addEventListener("click", () => {
      this.toggle();
    });

    // Overlay click
    this.overlay.addEventListener("click", () => {
      this.close();
    });

    // ESC key
    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape" && this.isOpen) {
        this.close();
      }
    });

    // Window resize
    window.addEventListener("resize", () => {
      if (window.innerWidth > 768 && this.isOpen) {
        this.close();
      }
    });

    // Navigation link clicks
    this.sidebarPanel.addEventListener("click", (e) => {
      if (e.target.closest(".admin-nav-link")) {
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
    this.hamburgerBtn.classList.add("active");
    this.overlay.classList.add("active");
    this.sidebarPanel.classList.add("active");
    this.mainContent.classList.add("shifted");

    // Add animations
    this.overlay.classList.add("admin-fade-in");
    this.sidebarPanel.classList.add("admin-slide-in");

    // Prevent body scroll
    document.body.style.overflow = "hidden";

    // Update active nav item
    this.updateActiveNavItem();

    // Update pending counts
    this.updatePendingCounts();
  }

  close() {
    this.isOpen = false;
    this.hamburgerBtn.classList.remove("active");
    this.overlay.classList.remove("active");
    this.sidebarPanel.classList.remove("active");
    this.mainContent.classList.remove("shifted");

    // Add animations
    this.overlay.classList.add("admin-fade-out");
    this.sidebarPanel.classList.add("admin-slide-out");

    // Restore body scroll
    document.body.style.overflow = "";

    // Clean up animation classes
    setTimeout(() => {
      this.overlay.classList.remove("admin-fade-in", "admin-fade-out");
      this.sidebarPanel.classList.remove("admin-slide-in", "admin-slide-out");
    }, 300);
  }

  updateActiveNavItem() {
    const currentPath = window.location.pathname;
    const navLinks = this.sidebarPanel.querySelectorAll(".admin-nav-link");

    navLinks.forEach((link) => {
      link.classList.remove("active");
      const href = link.getAttribute("href");
      if (
        href &&
        currentPath.includes(href.replace(window.BASE_URL || "", ""))
      ) {
        link.classList.add("active");
      }
    });
  }

  updatePendingCounts() {
    // Update booking pending count
    this.fetchPendingCount("booking", "booking-pending-count");

    // Update transaksi pending count
    this.fetchPendingCount("transaksi", "transaksi-pending-count");
  }

  fetchPendingCount(type, elementId) {
    // This would typically make an AJAX call to get pending counts
    // For now, we'll use placeholder values
    const element = document.getElementById(elementId);
    if (element) {
      // Simulate API call
      setTimeout(() => {
        const count = Math.floor(Math.random() * 10); // Placeholder
        element.textContent = count;
        element.style.display = count > 0 ? "inline-flex" : "none";
      }, 500);
    }
  }

  initializeTooltips() {
    // Initialize Bootstrap tooltips if available
    if (typeof bootstrap !== "undefined" && bootstrap.Tooltip) {
      const tooltipTriggerList = [].slice.call(
        document.querySelectorAll('[data-bs-toggle="tooltip"]')
      );
      tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
      });
    }
  }

  autoHideAlerts() {
    setTimeout(() => {
      const alerts = document.querySelectorAll(".alert, .admin-alert");
      alerts.forEach((alert) => {
        if (typeof bootstrap !== "undefined" && bootstrap.Alert) {
          const bsAlert = new bootstrap.Alert(alert);
          bsAlert.close();
        }
      });
    }, 5000);
  }

  initializeDataTables() {
    // Initialize DataTables if available
    if (typeof $ !== "undefined" && $.fn.DataTable) {
      $(".admin-datatable").DataTable({
        responsive: true,
        pageLength: 25,
        language: {
          url: "//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json",
        },
      });
    }
  }
}

// Admin Notification Manager
class AdminNotificationManager {
  static show(message, type = "info", duration = 3000) {
    const notification = document.createElement("div");
    notification.className = `admin-alert admin-alert-${type} alert-dismissible fade show position-fixed`;
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
                <i class="fas fa-${AdminNotificationManager.getIcon(
                  type
                )} me-2"></i>
                <span>${message}</span>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        `;

    document.body.appendChild(notification);

    // Auto remove
    setTimeout(() => {
      if (notification.parentNode) {
        notification.classList.remove("show");
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
      success: "check-circle",
      danger: "exclamation-triangle",
      warning: "exclamation-circle",
      info: "info-circle",
      primary: "info-circle",
    };
    return icons[type] || "info-circle";
  }
}

// Admin Form Validator
class AdminFormValidator {
  static validate(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;

    let isValid = true;

    // Check required fields
    const requiredFields = form.querySelectorAll("[required]");
    requiredFields.forEach((field) => {
      if (!field.value.trim()) {
        field.classList.add("is-invalid");
        isValid = false;
      } else {
        field.classList.remove("is-invalid");
      }
    });

    // Check email format
    const emailFields = form.querySelectorAll('input[type="email"]');
    emailFields.forEach((field) => {
      if (field.value && !AdminFormValidator.isValidEmail(field.value)) {
        field.classList.add("is-invalid");
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

// Admin Data Manager
class AdminDataManager {
  static async fetchData(endpoint, options = {}) {
    try {
      const response = await fetch(endpoint, {
        method: options.method || "GET",
        headers: {
          "Content-Type": "application/json",
          ...options.headers,
        },
        body: options.body ? JSON.stringify(options.body) : null,
      });

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      return await response.json();
    } catch (error) {
      console.error("Fetch error:", error);
      AdminNotificationManager.show(
        "Terjadi kesalahan saat mengambil data",
        "danger"
      );
      throw error;
    }
  }

  static async saveData(endpoint, data) {
    try {
      const response = await AdminDataManager.fetchData(endpoint, {
        method: "POST",
        body: data,
      });

      AdminNotificationManager.show("Data berhasil disimpan", "success");
      return response;
    } catch (error) {
      AdminNotificationManager.show("Gagal menyimpan data", "danger");
      throw error;
    }
  }

  static async deleteData(endpoint, id) {
    if (!confirm("Apakah Anda yakin ingin menghapus data ini?")) {
      return false;
    }

    try {
      const response = await AdminDataManager.fetchData(`${endpoint}/${id}`, {
        method: "DELETE",
      });

      AdminNotificationManager.show("Data berhasil dihapus", "success");
      return response;
    } catch (error) {
      AdminNotificationManager.show("Gagal menghapus data", "danger");
      throw error;
    }
  }
}

// Admin Chart Manager
class AdminChartManager {
  static createChart(canvasId, type, data, options = {}) {
    if (typeof Chart === "undefined") {
      console.warn("Chart.js not loaded");
      return null;
    }

    const ctx = document.getElementById(canvasId);
    if (!ctx) {
      console.warn(`Canvas with id ${canvasId} not found`);
      return null;
    }

    return new Chart(ctx, {
      type: type,
      data: data,
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: "top",
          },
          title: {
            display: true,
            text: options.title || "Chart",
          },
        },
        ...options,
      },
    });
  }

  static createStatisticsChart(canvasId, labels, data) {
    return AdminChartManager.createChart(canvasId, "bar", {
      labels: labels,
      datasets: [
        {
          label: "Jumlah",
          data: data,
          backgroundColor: [
            "rgba(44, 62, 80, 0.8)",
            "rgba(52, 152, 219, 0.8)",
            "rgba(39, 174, 96, 0.8)",
            "rgba(243, 156, 18, 0.8)",
            "rgba(231, 76, 60, 0.8)",
          ],
          borderColor: [
            "rgba(44, 62, 80, 1)",
            "rgba(52, 152, 219, 1)",
            "rgba(39, 174, 96, 1)",
            "rgba(243, 156, 18, 1)",
            "rgba(231, 76, 60, 1)",
          ],
          borderWidth: 2,
        },
      ],
    });
  }
}

// Admin Utils
class AdminUtils {
  static formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString("id-ID");
  }

  static formatTime(timeString) {
    const time = new Date("2000-01-01 " + timeString);
    return time.toLocaleTimeString("id-ID", {
      hour: "2-digit",
      minute: "2-digit",
    });
  }

  static formatCurrency(amount) {
    return new Intl.NumberFormat("id-ID", {
      style: "currency",
      currency: "IDR",
      minimumFractionDigits: 0,
    }).format(amount);
  }

  static showLoading(element) {
    if (element) {
      element.classList.add("admin-loading");
      element.disabled = true;
    }
  }

  static hideLoading(element) {
    if (element) {
      element.classList.remove("admin-loading");
      element.disabled = false;
    }
  }

  static exportToCSV(data, filename) {
    const csv = AdminUtils.convertToCSV(data);
    const blob = new Blob([csv], { type: "text/csv" });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.setAttribute("hidden", "");
    a.setAttribute("href", url);
    a.setAttribute("download", filename);
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
  }

  static convertToCSV(data) {
    if (!data || data.length === 0) return "";

    const headers = Object.keys(data[0]);
    const csvContent = [
      headers.join(","),
      ...data.map((row) =>
        headers.map((header) => `"${row[header]}"`).join(",")
      ),
    ].join("\n");

    return csvContent;
  }
}

// Initialize when DOM is ready
document.addEventListener("DOMContentLoaded", function () {
  // Initialize admin hamburger panel
  window.adminHamburgerPanel = new AdminHamburgerPanel();

  // Make functions globally available
  window.adminShowNotification = AdminNotificationManager.show;
  window.adminValidateForm = AdminFormValidator.validate;
  window.adminFetchData = AdminDataManager.fetchData;
  window.adminSaveData = AdminDataManager.saveData;
  window.adminDeleteData = AdminDataManager.deleteData;
  window.adminCreateChart = AdminChartManager.createChart;
  window.adminFormatDate = AdminUtils.formatDate;
  window.adminFormatTime = AdminUtils.formatTime;
  window.adminFormatCurrency = AdminUtils.formatCurrency;
  window.adminShowLoading = AdminUtils.showLoading;
  window.adminHideLoading = AdminUtils.hideLoading;
  window.adminExportToCSV = AdminUtils.exportToCSV;

  // Initialize enhanced form handling
  document.querySelectorAll("form").forEach((form) => {
    form.addEventListener("submit", function (e) {
      const submitBtn = form.querySelector(
        'button[type="submit"], input[type="submit"]'
      );
      if (submitBtn) {
        AdminUtils.showLoading(submitBtn);

        setTimeout(() => {
          AdminUtils.hideLoading(submitBtn);
        }, 2000);
      }
    });
  });

  // Initialize enhanced table features
  document.querySelectorAll(".admin-table").forEach((table) => {
    // Add hover effects and click handlers
    const rows = table.querySelectorAll("tbody tr");
    rows.forEach((row) => {
      row.addEventListener("click", function () {
        // Handle row selection
        rows.forEach((r) => r.classList.remove("selected"));
        this.classList.add("selected");
      });
    });
  });

  // Initialize quick action cards
  document.querySelectorAll(".admin-quick-action").forEach((action) => {
    action.addEventListener("mouseenter", function () {
      this.style.transform = "translateY(-5px) scale(1.02)";
    });

    action.addEventListener("mouseleave", function () {
      this.style.transform = "translateY(0) scale(1)";
    });
  });

  // Initialize auto-refresh for dashboard
  if (
    window.location.pathname.includes("admin/index.php") ||
    window.location.pathname.endsWith("admin/")
  ) {
    setInterval(() => {
      window.adminHamburgerPanel?.updatePendingCounts();
    }, 30000); // Refresh every 30 seconds
  }
});
