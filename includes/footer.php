<?php
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../config/database.php';
}
?>
    <!-- Global Footer (Only shown on non-dashboard or public pages if not inside dashboard-layout) -->
    <footer class="bg-white border-top py-4 mt-auto">
        <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-2">
                <span class="fw-bold text-primary">SIREKA</span>
                <span class="text-muted">|</span>
                <span class="text-muted small">&copy; <?= date('Y') ?> Sistem Informasi Rekrutmen & Kandidat. Project Basis Data.</span>
            </div>
            <div class="d-flex align-items-center gap-3 small text-muted">
                <span>Mendukung <strong class="text-primary">SDGs 8: Decent Work & Economic Growth</strong></span>
                <span>&bull;</span>
                <a href="<?= BASE_URL ?>/helpdesk.php" class="text-muted">Helpdesk WhatsApp</a>
            </div>
        </div>
    </footer>


    <!-- Bootstrap 5 Bundle with Popper JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom Main JS -->
    <script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>
