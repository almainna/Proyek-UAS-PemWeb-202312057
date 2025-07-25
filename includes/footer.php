</div>
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-light py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5><i class="fas fa-hospital-alt me-2"></i>Klinik Alma Sehat</h5>
                    <p>Melayani kesehatan Anda dengan sepenuh hati. Klinik terpercaya dengan dokter berpengalaman dan fasilitas modern.</p>
                </div>
                <div class="col-md-4">
                    <h5>Kontak</h5>
                    <p><i class="fas fa-map-marker-alt me-2"></i>Jl. Kesehatan No. 123, Jakarta</p>
                    <p><i class="fas fa-phone me-2"></i>021-12345678</p>
                    <p><i class="fas fa-envelope me-2"></i>info@klinikalmasehat.com</p>
                </div>
                <div class="col-md-4">
                    <h5>Jam Operasional</h5>
                    <p><i class="fas fa-clock me-2"></i>Senin - Jumat: 08:00 - 20:00</p>
                    <p><i class="fas fa-clock me-2"></i>Sabtu: 08:00 - 16:00</p>
                    <p><i class="fas fa-clock me-2"></i>Minggu: Tutup</p>
                </div>
            </div>
            <hr class="my-4">
            <div class="row">
                <div class="col-md-6">
                    <p>&copy; 2024 Klinik Alma Sehat. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-end">
                    <a href="#" class="text-light me-3"><i class="fab fa-facebook"></i></a>
                    <a href="#" class="text-light me-3"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="text-light"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="<?php echo BASE_URL; ?>assets/js/script.js"></script>
</body>
</html>
<?php
// Flush output buffer if it's active
if (ob_get_level()) {
    ob_end_flush();
}
?>
