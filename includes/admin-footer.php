</div>
    </main>

    <!-- Admin Footer -->
    <footer class="admin-footer">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0">
                        <i class="fas fa-shield-alt me-2"></i>
                        Admin Panel - Klinik Alma Sehat &copy; 2024
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <small class="text-muted">
                        <i class="fas fa-user me-1"></i>
                        <?php echo $_SESSION['nama'] ?? 'Administrator'; ?>
                        <span class="mx-2">|</span>
                        <i class="fas fa-clock me-1"></i>
                        <span id="admin-current-time"></span>
                    </small>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <!-- Custom JS -->
    <script src="<?php echo BASE_URL; ?>assets/js/script.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/js/admin-script.js"></script>
    
    <script>
        // Update current time in footer
        function updateAdminTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID');
            const timeElement = document.getElementById('admin-current-time');
            if (timeElement) {
                timeElement.textContent = timeString;
            }
        }
        
        // Update time every second
        setInterval(updateAdminTime, 1000);
        updateAdminTime(); // Initial call
    </script>
</body>
</html>
<?php ob_end_flush(); ?>