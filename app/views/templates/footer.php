<!-- Footer du site -->
<footer class="footer mt-5" 
        role="contentinfo" 
        itemscope 
        itemtype="https://schema.org/WPFooter">
    
    <!-- Conteneur centré avec padding vertical -->
    <div class="container text-center py-4">
        
        <!-- Ligne copyright -->
        <p class="mb-1 fw-semibold" itemprop="copyrightYear">
            © <?= date('Y'); ?> <!-- Année dynamique -->
            
            <!-- Informations sur l'organisation (SEO & données structurées) -->
            <span itemprop="publisher" itemscope itemtype="https://schema.org/Organization">
                <span itemprop="name">
                    <?= defined('APP_NAME') ? APP_NAME : 'TOUCHE PAS AU KLAXON'; ?>
                    <!-- Si APP_NAME défini, l'afficher sinon valeur par défaut -->
                </span>
            </span>
        </p>

        <!-- Description ou slogan du site -->
        <p class="mb-0 small" itemprop="description">
            Plateforme de covoiturage inter-agences – Tous droits réservés
        </p>
    </div>
</footer>

<!-- Chargement de Bootstrap JS pour les composants interactifs -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
