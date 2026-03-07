document.addEventListener('DOMContentLoaded', function() {

    function getFormParams(form) {
        const data = new FormData(form);
        const params = {};
        for (const [k, v] of data.entries()) {
            if (v) params[k] = v;
        }
        return new URLSearchParams(params).toString();
    }
    
    function ajaxLoadFilter(url, containerId, callback) {
        const container = document.getElementById(containerId);
        if (!container) return;
        container.style.opacity = '0.6';
        container.style.pointerEvents = 'none';
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(r) { return r.text(); })
            .then(function(html) {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const wrap = doc.getElementById(containerId) || doc.querySelector('#' + containerId);
                if (wrap) {
                    container.innerHTML = wrap.innerHTML;
                } else {
                    container.innerHTML = (doc.body && doc.body.innerHTML) ? doc.body.innerHTML : html;
                }
                container.style.opacity = '';
                container.style.pointerEvents = '';
                if (callback) callback();
            })
            .catch(function() {
                container.style.opacity = '';
                container.style.pointerEvents = '';
                window.location.href = url.replace(/[?&]ajax=[^&]*/g, '').replace(/&$/, '');
            });
    }
    
    function runFilterForm(form) {
        const params = getFormParams(form);
        let fetchUrl, displayUrl, containerId, ajaxParam;
        if (document.getElementById('products-ajax-container')) {
            containerId = 'products-ajax-container';
            ajaxParam = 'ajax=1';
            fetchUrl = (window.BASE_URL || '') + '/products.php?' + params + '&' + ajaxParam;
            displayUrl = (window.BASE_URL || '') + '/products.php' + (params ? '?' + params : '');
        } else if (document.getElementById('category-ajax-container')) {
            containerId = 'category-ajax-container';
            ajaxParam = 'ajax=1';
            fetchUrl = window.location.pathname + '?' + params + '&' + ajaxParam;
            displayUrl = window.location.pathname + (params ? '?' + params : '');
        } else if (document.getElementById('index-popular-ajax-container')) {
            containerId = 'index-popular-ajax-container';
            ajaxParam = 'ajax=popular';
            fetchUrl = (window.BASE_URL || '') + '/index.php?' + params + '&' + ajaxParam;
            displayUrl = (window.BASE_URL || '') + '/index.php' + (params ? '?' + params : '') + '#populer-urunler';
        } else return;
        history.replaceState(null, '', displayUrl);
        ajaxLoadFilter(fetchUrl, containerId, function() {
            initPaginationLinks(containerId);
        });
    }

    document.querySelectorAll('form.filter-form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            e.stopPropagation();
            runFilterForm(form);
            return false;
        }, true);

    });
    
    function initPaginationLinks(containerId) {
        const container = document.getElementById(containerId);
        if (!container) return;
        const ajaxParam = containerId === 'index-popular-ajax-container' ? 'ajax=popular' : 'ajax=1';
        container.querySelectorAll('a.pagination-link').forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const href = link.getAttribute('href') || '';
                if (!href) return;
                const fullUrl = new URL(href, window.location.href);
                const fetchUrl = fullUrl.toString() + (fullUrl.search ? '&' : '?') + ajaxParam;
                history.replaceState(null, '', fullUrl.pathname + fullUrl.search + fullUrl.hash);
                ajaxLoadFilter(fetchUrl, containerId, function() {
                    initPaginationLinks(containerId);
                });
            });
        });
    }
    initPaginationLinks('products-ajax-container');
    initPaginationLinks('category-ajax-container');
    initPaginationLinks('index-popular-ajax-container');

    const themeToggle = document.getElementById('theme-toggle');
    const themeLabel = themeToggle?.querySelector('.theme-label');

    const savedTheme = localStorage.getItem('theme') || 'light';
    if (savedTheme === 'dark' && !document.body.classList.contains('dark-theme')) {
        document.body.classList.add('dark-theme');
    } else if (savedTheme === 'light' && document.body.classList.contains('dark-theme')) {
        document.body.classList.remove('dark-theme');
    }

    if (themeToggle) {

        const isDark = document.body.classList.contains('dark-theme');
        if (themeLabel) {
            themeLabel.textContent = isDark ? '🌕' : '☀️';
        }

        themeToggle.addEventListener('click', function() {
            const isDark = document.body.classList.toggle('dark-theme');
            const theme = isDark ? 'dark' : 'light';
            localStorage.setItem('theme', theme); // Tercihi kaydet

            if (themeLabel) {
                themeLabel.textContent = isDark ? '🌕' : '☀️';
            }
        });
    }

    document.addEventListener('submit', function(e) {
        const form = e.target && e.target.closest('.add-to-cart-form');
        if (!form) return;
        e.preventDefault();
        
        const formData = new FormData(form);
        if (!formData.has('_csrf_token') && window.CSRF_TOKEN) {
            formData.append('_csrf_token', window.CSRF_TOKEN);
        }
        formData.append('add_to_cart', '1');

            fetch(window.BASE_URL + '/add_to_cart_ajax.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {

                    if (data.cart_count !== undefined) {
                        const cartBadge = document.querySelector('.cart-badge');
                        const cartButton = document.querySelector('.cart-button');
                        
                        if (data.cart_count > 0) {
                            if (cartBadge) {
                                cartBadge.textContent = data.cart_count;
                            } else if (cartButton) {

                                const badge = document.createElement('span');
                                badge.className = 'cart-badge';
                                badge.textContent = data.cart_count;
                                cartButton.appendChild(badge);
                            }
                        }
                    }

                    showCartNotification(data.message);
                } else {

                    if (data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        showCartNotification(data.message, 'error');
                    }
                }
            })
            .catch(error => {
                console.error('Sepete ekleme hatası:', error);
                showCartNotification('Bir hata oluştu.', 'error');
            });
    });

    function showCartNotification(message, type = 'success') {
        if (!document.body) return;

        const existingNotification = document.querySelector('.cart-notification');
        if (existingNotification) {
            existingNotification.remove();
        }

        const notification = document.createElement('div');
        notification.className = 'cart-notification ' + type;
        notification.textContent = message;
        document.body.appendChild(notification);

        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transform = 'translateY(-10px)';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }

    const sliderSlides = document.querySelectorAll('.slider-slide');
    const sliderDots = document.querySelectorAll('.slider-dots .dot');
    const sliderPrev = document.querySelector('.slider-prev');
    const sliderNext = document.querySelector('.slider-next');
    
    if (sliderSlides.length > 0) {
        let currentSlide = 0; // Şu anki aktif slide index'i
        let sliderInterval; // Otomatik geçiş için interval ID'si

        function showSlide(index) {

            sliderSlides.forEach((slide, i) => {
                if (i === index) {
                    slide.classList.add('active');
                } else {
                    slide.classList.remove('active');
                }
            });

            sliderDots.forEach((dot, i) => {
                if (i === index) {
                    dot.classList.add('active');
                } else {
                    dot.classList.remove('active');
                }
            });
            
            currentSlide = index;
        }

        function nextSlide() {
            const next = (currentSlide + 1) % sliderSlides.length; // Döngüsel geçiş
            showSlide(next);
        }

        function prevSlide() {
            const prev = (currentSlide - 1 + sliderSlides.length) % sliderSlides.length; // Döngüsel geçiş
            showSlide(prev);
        }

        function startSlider() {
            stopSlider(); // Önceki interval'i temizle
            sliderInterval = setInterval(nextSlide, 5000); // 5 saniye aralıklarla geçiş
        }

        function stopSlider() {
            if (sliderInterval) {
                clearInterval(sliderInterval);
                sliderInterval = null;
            }
        }

        showSlide(0);

        if (sliderNext) {
            sliderNext.addEventListener('click', () => {
                nextSlide();
                stopSlider(); // Manuel kontrol sonrası timer'ı sıfırla
                startSlider(); // Timer'ı tekrar başlat
            });
        }

        if (sliderPrev) {
            sliderPrev.addEventListener('click', () => {
                prevSlide();
                stopSlider();
                startSlider();
            });
        }

        sliderDots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                showSlide(index);
                stopSlider();
                startSlider();
            });
        });

        startSlider();
    }

    const scrollToTopBtn = document.getElementById('scroll-to-top');
    if (scrollToTopBtn) {
        
        function toggleScrollToTop() {
            if (window.pageYOffset > 300) {
                scrollToTopBtn.classList.add('show');
            } else {
                scrollToTopBtn.classList.remove('show');
            }
        }

        window.addEventListener('scroll', toggleScrollToTop);

        toggleScrollToTop();

        scrollToTopBtn.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }

    function handleResize() {
        if (window.innerWidth > 768) {

            document.body.classList.remove('menu-open');
            const mobileMenu = document.getElementById('mobileMenu');
            const mobileMenuToggle = document.getElementById('mobileMenuToggle');
            if (mobileMenu) {
                mobileMenu.classList.remove('active');
            }
            if (mobileMenuToggle) {
                mobileMenuToggle.classList.remove('active');
            }
        }
    }

    handleResize();
    window.addEventListener('resize', handleResize);

    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const mobileMenu = document.getElementById('mobileMenu');
    
    if (mobileMenuToggle && mobileMenu) {
        let isAnimating = false; // Animasyon devam ediyor mu kontrolü
        let menuTimeout = null; // Timeout ID'si

        function toggleMobileMenu() {

            if (isAnimating) {
                return;
            }

            if (menuTimeout) {
                clearTimeout(menuTimeout);
            }
            
            const isOpen = mobileMenu.classList.contains('active');

            isAnimating = true;
            
            if (isOpen) {

                mobileMenuToggle.classList.remove('active');
                mobileMenu.classList.remove('active');
                document.body.classList.remove('menu-open'); // Body scroll'u geri aç
            } else {

                mobileMenuToggle.classList.add('active');
                mobileMenu.classList.add('active');
                document.body.classList.add('menu-open'); // Body scroll'u engelle
            }

            menuTimeout = setTimeout(function() {
                isAnimating = false;
                menuTimeout = null;
            }, 300);
        }

        let lastClickTime = 0;
        mobileMenuToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const now = Date.now();

            if (now - lastClickTime < 150) {
                return;
            }
            lastClickTime = now;
            
            toggleMobileMenu();
        });

        document.addEventListener('click', function(event) {
            if (isAnimating) return;
            
            if (mobileMenu && mobileMenuToggle && 
                mobileMenu.classList.contains('active') &&
                !mobileMenu.contains(event.target) && 
                !mobileMenuToggle.contains(event.target)) {
                toggleMobileMenu();
            }
        });

        const mobileDropdowns = document.querySelectorAll('.mobile-menu-content .dropdown, .mobile-menu-buttons .dropdown');
        let isDropdownAnimating = false; // Dropdown animasyon kontrolü

        function handleDropdownClick(dropdown, e) {
            e.preventDefault();
            e.stopPropagation();

            if (isDropdownAnimating) {
                return;
            }
            
            isDropdownAnimating = true;
            
            const isActive = dropdown.classList.contains('active');

            mobileDropdowns.forEach(function(otherDropdown) {
                if (otherDropdown !== dropdown && otherDropdown.classList.contains('active')) {
                    otherDropdown.classList.remove('active');
                }
            });

            if (isActive) {
                dropdown.classList.remove('active');
            } else {
                dropdown.classList.add('active');
            }

            setTimeout(function() {
                isDropdownAnimating = false;
            }, 300);
        }

        mobileDropdowns.forEach(function(dropdown) {
            const dropdownButton = dropdown.querySelector('button');
            const dropdownMenu = dropdown.querySelector('.dropdown-menu');
            
            if (dropdownButton && dropdownMenu) {
                dropdownButton.addEventListener('click', function(e) {
                    handleDropdownClick(dropdown, e);
                });
            }
        });
    }

    const themeToggleMobile = document.getElementById('theme-toggle-mobile');
    if (themeToggleMobile) {
        const themeLabelMobile = themeToggleMobile.querySelector('.theme-label');
        const isDark = document.body.classList.contains('dark-theme');

        if (themeLabelMobile) {
            themeLabelMobile.textContent = isDark ? '🌕' : '☀️';
        }

        themeToggleMobile.addEventListener('click', function() {
            const isDark = document.body.classList.toggle('dark-theme');
            const theme = isDark ? 'dark' : 'light';
            localStorage.setItem('theme', theme);

            if (themeLabelMobile) {
                themeLabelMobile.textContent = isDark ? '🌕' : '☀️';
            }

            const mainThemeToggle = document.getElementById('theme-toggle');
            const mainThemeLabel = mainThemeToggle?.querySelector('.theme-label');
            if (mainThemeLabel) {
                mainThemeLabel.textContent = isDark ? '🌕' : '☀️';
            }
        });
    }

    function syncFooterLinksDetails() {
        const details = document.getElementById('footer-links-details');
        if (!details) return;
        if (window.innerWidth > 768) {
            details.setAttribute('open', '');
        } else {
            details.removeAttribute('open');
        }
    }
    syncFooterLinksDetails();
    window.addEventListener('resize', syncFooterLinksDetails);

});
