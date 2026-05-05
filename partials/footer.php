</main>

<!-- ============================================
     SİTE FOOTER (ALT KISIM)
     ============================================
     Footer linkleri ve telif hakkı bilgisi
-->
<footer class="site-footer">
    <div class="container">
        <div class="footer-content">
            <!-- Footer linkleri: mobilde açılır menü, masaüstünde normal -->
            <details class="footer-links-details" id="footer-links-details">
                <summary class="footer-links-trigger">Sayfalar</summary>
                <div class="footer-links">
                <a href="<?php echo BASE_URL; ?>/index.php">Anasayfa</a>
                <a href="<?php echo BASE_URL; ?>/products.php">Ürünler</a>
                <?php if (!empty($hasAboutPage)): ?><a href="<?php echo BASE_URL; ?>/about.php">Hakkımızda</a><?php endif; ?>
                <?php if (!empty($hasContactPage)): ?><a href="<?php echo BASE_URL; ?>/contact.php">İletişim</a><?php endif; ?>
                <?php
                if (!empty($pages)) {
                    $footerPages = array_filter($pages, function ($p) use ($pdo) {
                        if (in_array($p['slug'], ['hakkimizda', 'iletisim'], true)) {
                            return false;
                        }
                        return getSetting($pdo, 'page_footer_' . $p['slug'], '1') === '1';
                    });
                    $footerPages = array_values($footerPages);
                    usort($footerPages, function ($a, $b) { return (int) $a['id'] - (int) $b['id']; });
                    foreach ($footerPages as $fp): ?>
                <a href="<?php echo BASE_URL; ?>/page.php?slug=<?php echo rawurlencode($fp['slug']); ?>"><?php echo sanitize($fp['title']); ?></a>
                    <?php endforeach;
                }
                ?>
                </div>
            </details>
            
            <!-- Telif hakkı bilgisi (mevcut yıl dinamik olarak gösterilir) -->
            <p>&copy; <?php echo date('Y'); ?> ManRoMan. Tüm hakları saklıdır.</p>
            <p class="footer-credit" style="margin-top: 0.5rem; font-size: 0.9rem; opacity: 0.9;">Bu Web Sitesi Ferhat ALKAN tarafından yapılmıştır.</p>
        </div>
    </div>
</footer>

<!-- ============================================
     SCROLL TO TOP BUTONU
     ============================================
     Sayfa aşağı kaydırıldığında görünen yukarı çık butonu
     JavaScript ile kontrol edilir (assets/js/main.js)
-->
<button id="scroll-to-top" class="scroll-to-top" title="Yukarı Çık" aria-label="Yukarı Çık">
    <!-- Yukarı ok SVG ikonu -->
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M18 15l-6-6-6 6"/>
    </svg>
</button>

<!-- ============================================
     FRONTEND JAVASCRIPT DOSYASI
     ============================================
     Tüm frontend JavaScript işlevleri bu dosyada
     (Slider, Menü, Sepet, Tema Toggle, Scroll to Top vb.)
-->
<script src="<?php echo BASE_URL; ?>/assets/js/main.js"></script>
</body>
</html>

