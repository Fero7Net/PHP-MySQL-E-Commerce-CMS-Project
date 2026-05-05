document.addEventListener('DOMContentLoaded', function() {

    const themeToggle = document.getElementById('adminThemeToggle');
    const themeLabel = document.querySelector('.admin-theme-toggle .theme-label');
    const themeSlider = document.querySelector('.admin-theme-toggle .theme-slider');
    const themeSliderHandle = document.querySelector('.admin-theme-toggle .theme-slider-handle');

    const topThemeToggle = document.getElementById('theme-toggle');
    const topThemeLabel = topThemeToggle?.querySelector('.theme-label');

    const savedTheme = localStorage.getItem('theme') || 'light';
    if (savedTheme === 'dark' && !document.body.classList.contains('dark-theme')) {
        document.body.classList.add('dark-theme');
    } else if (savedTheme === 'light' && document.body.classList.contains('dark-theme')) {
        document.body.classList.remove('dark-theme');
    }

    function toggleTheme() {
        const isDark = document.body.classList.toggle('dark-theme');
        const theme = isDark ? 'dark' : 'light';
        localStorage.setItem('theme', theme); // Tercihi kaydet

        if (themeToggle) {
            themeToggle.checked = isDark;
        }
        if (themeLabel) {
            themeLabel.textContent = isDark ? '🌕' : '☀️';
        }
        if (themeSlider) {
            themeSlider.style.background = isDark ? 'var(--primary)' : '#cbd5e1';
        }
        if (themeSliderHandle) {
            themeSliderHandle.style.left = isDark ? '26px' : '2px';
        }

        if (topThemeLabel) {
            topThemeLabel.textContent = isDark ? '🌕' : '☀️';
        }
    }

    const isDark = document.body.classList.contains('dark-theme');
    if (themeToggle) {
        themeToggle.checked = isDark;
    }
    if (themeLabel) {
        themeLabel.textContent = isDark ? '🌕' : '☀️';
    }
    if (themeSlider) {
        themeSlider.style.background = isDark ? 'var(--primary)' : '#cbd5e1';
    }
    if (themeSliderHandle) {
        themeSliderHandle.style.left = isDark ? '26px' : '2px';
    }
    if (topThemeLabel) {
        topThemeLabel.textContent = isDark ? '🌕' : '☀️';
    }

    if (themeToggle) {
        themeToggle.addEventListener('change', toggleTheme);
    }

    if (topThemeToggle) {
        topThemeToggle.addEventListener('click', toggleTheme);
    }

    const themeToggleMobile = document.getElementById('theme-toggle-mobile');
    if (themeToggleMobile) {
        themeToggleMobile.addEventListener('click', function() {
            toggleTheme();
        });

        const isDark = document.body.classList.contains('dark-theme');
        const mobileLabel = themeToggleMobile.querySelector('.theme-label');
        if (mobileLabel) {
            mobileLabel.textContent = isDark ? '🌕' : '☀️';
        }
    }
});

const scrollToTopBtn = document.getElementById("scroll-to-top");
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

function handleAdminResize() {
    if (window.innerWidth > 768) {

        document.body.classList.remove('menu-open');
        const adminSidebar = document.getElementById('adminSidebar');
        const adminSidebarOverlay = document.getElementById('adminSidebarOverlay');
        const adminMobileMenuToggle = document.getElementById('adminMobileMenuToggle');
        if (adminSidebar) {
            adminSidebar.classList.remove('mobile-open');
        }
        if (adminSidebarOverlay) {
            adminSidebarOverlay.classList.remove('active');
        }
        if (adminMobileMenuToggle) {
            adminMobileMenuToggle.classList.remove('active');
        }
    }
}

handleAdminResize();
window.addEventListener('resize', handleAdminResize);

const adminMobileMenuToggle = document.getElementById('adminMobileMenuToggle');
const adminSidebar = document.getElementById('adminSidebar');
const adminSidebarOverlay = document.getElementById('adminSidebarOverlay');

if (adminMobileMenuToggle && adminSidebar) {
    let isAnimating = false; // Animasyon devam ediyor mu kontrolü
    let sidebarTimeout = null; // Timeout ID'si

    function toggleAdminSidebar() {

        if (isAnimating) {
            return;
        }

        if (sidebarTimeout) {
            clearTimeout(sidebarTimeout);
        }
        
        const isOpen = adminSidebar.classList.contains('mobile-open');

        isAnimating = true;
        
        if (isOpen) {

            adminSidebar.classList.remove('mobile-open');
            if (adminSidebarOverlay) {
                adminSidebarOverlay.classList.remove('active');
            }
            adminMobileMenuToggle.classList.remove('active');
            document.body.classList.remove('menu-open'); // Body scroll'u geri aç
        } else {

            adminSidebar.classList.add('mobile-open');
            if (adminSidebarOverlay) {
                adminSidebarOverlay.classList.add('active');
            }
            adminMobileMenuToggle.classList.add('active');
            document.body.classList.add('menu-open'); // Body scroll'u engelle
        }

        sidebarTimeout = setTimeout(function() {
            isAnimating = false;
            sidebarTimeout = null;
        }, 300);
    }

    let lastClickTime = 0;
    adminMobileMenuToggle.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const now = Date.now();

        if (now - lastClickTime < 150) {
            return;
        }
        lastClickTime = now;
        
        toggleAdminSidebar();
    });

    if (adminSidebarOverlay) {
        adminSidebarOverlay.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            if (!isAnimating) {
                toggleAdminSidebar();
            }
        });
    }

    const sidebarLinks = adminSidebar.querySelectorAll('a');
    sidebarLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 768 && !isAnimating) {
                toggleAdminSidebar();
            }
        });
    });
}
