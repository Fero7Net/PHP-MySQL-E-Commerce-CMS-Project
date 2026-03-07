</main>

<!-- ============================================
     SCROLL TO TOP BUTONU (ADMIN)
     ============================================
     Sayfa aşağı kaydırıldığında görünen yukarı çık butonu
     JavaScript ile kontrol edilir (assets/js/admin.js)
-->
<button id="scroll-to-top" class="scroll-to-top" title="Yukarı Çık" aria-label="Yukarı Çık">
    <!-- Yukarı ok SVG ikonu -->
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
        <path d="M12 2L4.5 10.5H9V22H15V10.5H19.5L12 2Z"/>
    </svg>
</button>

<!-- ============================================
     ADMIN PANEL JAVASCRIPT DOSYASI
     ============================================
     Tüm admin panel JavaScript işlevleri bu dosyada
     (Sidebar, Tema Toggle, Scroll to Top vb.)
-->
<script src="<?php echo BASE_URL; ?>/assets/js/admin.js"></script>
</body>
</html>

