<footer class="footer mt-5" role="contentinfo" itemscope itemtype="https://schema.org/WPFooter">
    <div class="container text-center py-4">
        <p class="mb-1 fw-semibold" itemprop="copyrightYear">
            © <?= date('Y'); ?> 
            <span itemprop="publisher" itemscope itemtype="https://schema.org/Organization">
                <span itemprop="name"><?= defined('APP_NAME') ? APP_NAME : 'TOUCHE PAS AU KLAXON'; ?></span>
            </span>
        </p>
        <p class="mb-0 small" itemprop="description">
            Plateforme de covoiturage inter-agences – Tous droits réservés
        </p>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
