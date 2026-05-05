<?php

require __DIR__ . '/config.php';

logVisit($pdo, $_SERVER['REQUEST_URI'] ?? '');

$pageTitle = 'İletişim';
$page = findPageBySlug($pdo, 'iletisim');
if ($page === null) {
    redirect(BASE_URL . '/');
    exit;
}
$heroTitle = getSetting($pdo, 'content_contact_hero_title', 'İletişim');
$heroSubtitle = getSetting($pdo, 'content_contact_hero_subtitle', 'Bizimle iletişime geçin, sorularınızı yanıtlayalım.');
$sectionTitle = getSetting($pdo, 'content_contact_section_title', 'İletişim Bilgileri');
$emailLabel = getSetting($pdo, 'content_contact_email_label', 'E-posta');
$emailDesc = getSetting($pdo, 'content_contact_email_desc', 'Sorularınız için bize yazın');
$emailValue = getSetting($pdo, 'content_contact_email_value', 'info@manroman.local');
$phoneLabel = getSetting($pdo, 'content_contact_phone_label', 'Telefon');
$phoneDesc = getSetting($pdo, 'content_contact_phone_desc', 'Bizi arayın');
$phoneValue = getSetting($pdo, 'content_contact_phone_value', '0 212 000 00 00');
$phoneMobile = getSetting($pdo, 'content_contact_phone_mobile', '0 555 555 55 55');
$addressTitle = getSetting($pdo, 'content_contact_address_title', 'Adres');
$addressCompany = getSetting($pdo, 'content_contact_address_company', 'ManRoMan Kitapçılık');
$addressLine1 = getSetting($pdo, 'content_contact_address_line1', 'Bağdat Caddesi No: 123/A');
$addressLine2 = getSetting($pdo, 'content_contact_address_line2', 'Balıkesir Gönen');
$addressCountry = getSetting($pdo, 'content_contact_address_country', 'Türkiye');
$hoursTitle = getSetting($pdo, 'content_contact_hours_title', 'Çalışma Saatleri');
$hoursWeekdaysLabel = getSetting($pdo, 'content_contact_hours_weekdays_label', 'Pazartesi - Cuma');
$hoursWeekdays = getSetting($pdo, 'content_contact_hours_weekdays', '09:00 - 18:00');
$hoursSaturdayLabel = getSetting($pdo, 'content_contact_hours_saturday_label', 'Cumartesi');
$hoursSaturday = getSetting($pdo, 'content_contact_hours_saturday', '10:00 - 16:00');
$hoursSundayLabel = getSetting($pdo, 'content_contact_hours_sunday_label', 'Pazar');
$hoursSunday = getSetting($pdo, 'content_contact_hours_sunday', 'Kapalı');
$socialTitle = getSetting($pdo, 'content_contact_social_title', 'Sosyal Medya');
$socialDesc = getSetting($pdo, 'content_contact_social_desc', 'Bizi sosyal medyada takip edin!');
$socialFacebook = getSetting($pdo, 'content_contact_social_facebook', '#');
$socialInstagram = getSetting($pdo, 'content_contact_social_instagram', '#');
$socialTwitter = getSetting($pdo, 'content_contact_social_twitter', '#');

include __DIR__ . '/partials/header.php';
?>

<section class="hero">
    <h1><?php echo sanitize($heroTitle); ?></h1>
    <p><?php echo sanitize($heroSubtitle); ?></p>
</section>

<section class="card" style="max-width: 900px; margin: 2rem auto;">
    <h2 style="text-align: center; color: var(--primary); margin-bottom: 2rem;"><?php echo sanitize($sectionTitle); ?></h2>
    <div class="contact-info" style="max-width: 800px; margin: 0 auto;">
        <?php if ($page && !empty($page['content'])): ?>
            <div style="margin-bottom: 2rem; padding: 2rem; background: var(--light); border-radius: 0.5rem; border: 1px solid var(--border);">
                <div class="page-content" style="text-align: center;">
                    <?php echo nl2br(sanitize($page['content'])); ?>
                </div>
            </div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; margin-bottom: 2rem;">
            <?php if ($emailLabel !== '' || $emailDesc !== '' || $emailValue !== ''): ?>
            <div style="padding: 2rem; background: var(--light); border-radius: 0.5rem; text-align: center; border: 1px solid var(--border);">
                <div style="font-size: 3rem; margin-bottom: 1rem;">📧</div>
                <h3 style="color: var(--primary); margin-bottom: 0.5rem;"><?php echo sanitize($emailLabel); ?></h3>
                <?php if ($emailDesc !== ''): ?><p style="color: var(--muted); margin-bottom: 1rem;"><?php echo sanitize($emailDesc); ?></p><?php endif; ?>
                <?php if ($emailValue !== ''): ?><a href="mailto:<?php echo sanitize($emailValue); ?>" style="color: var(--primary); text-decoration: none; font-weight: 600; font-size: 1.1rem;"><?php echo sanitize($emailValue); ?></a><?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if ($phoneLabel !== '' || $phoneDesc !== '' || $phoneValue !== '' || $phoneMobile !== ''): ?>
            <div style="padding: 2rem; background: var(--light); border-radius: 0.5rem; text-align: center; border: 1px solid var(--border);">
                <div style="font-size: 3rem; margin-bottom: 1rem;">📞</div>
                <h3 style="color: var(--primary); margin-bottom: 0.5rem;"><?php echo sanitize($phoneLabel); ?></h3>
                <?php if ($phoneDesc !== ''): ?><p style="color: var(--muted); margin-bottom: 1rem;"><?php echo sanitize($phoneDesc); ?></p><?php endif; ?>
                <?php if ($phoneValue !== ''): ?><a href="tel:<?php echo preg_replace('/\s+/', '', sanitize($phoneValue)); ?>" style="color: var(--primary); text-decoration: none; font-weight: 600; font-size: 1.1rem;"><?php echo sanitize($phoneValue); ?></a><?php endif; ?>
                <?php if ($phoneMobile !== ''): ?><p style="color: var(--muted); font-size: 0.9rem; margin-top: 0.5rem;">Mobil: <?php echo sanitize($phoneMobile); ?></p><?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <?php if ($addressTitle !== '' || $addressCompany !== '' || $addressLine1 !== '' || $addressLine2 !== '' || $addressCountry !== ''): ?>
        <div style="padding: 2rem; background: var(--light); border-radius: 0.5rem; margin-bottom: 2rem; border: 1px solid var(--border);">
            <div style="text-align: center; margin-bottom: 1.5rem;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">📍</div>
                <h3 style="color: var(--primary); margin-bottom: 0.5rem;"><?php echo sanitize($addressTitle); ?></h3>
            </div>
            <div style="text-align: center; line-height: 1.8; color: var(--muted);">
                <?php if ($addressCompany !== ''): ?><p style="font-size: 1.1rem; font-weight: 600; color: var(--text); margin-bottom: 0.5rem;"><?php echo sanitize($addressCompany); ?></p><?php endif; ?>
                <?php if ($addressLine1 !== ''): ?><p><?php echo sanitize($addressLine1); ?></p><?php endif; ?>
                <?php if ($addressLine2 !== ''): ?><p><?php echo sanitize($addressLine2); ?></p><?php endif; ?>
                <?php if ($addressCountry !== ''): ?><p><?php echo sanitize($addressCountry); ?></p><?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($hoursTitle !== '' || $hoursWeekdaysLabel !== '' || $hoursWeekdays !== '' || $hoursSaturdayLabel !== '' || $hoursSaturday !== '' || $hoursSundayLabel !== '' || $hoursSunday !== ''): ?>
        <div class="contact-hours-section" style="padding: 2rem; background: var(--light); border-radius: 0.5rem; margin-bottom: 2rem; border: 1px solid var(--border);">
            <div style="text-align: center; margin-bottom: 1.5rem;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">🕒</div>
                <h3 style="color: var(--primary); margin-bottom: 0.5rem;"><?php echo sanitize($hoursTitle); ?></h3>
            </div>
            <div class="contact-hours-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; text-align: center;">
                <?php if ($hoursWeekdaysLabel !== '' || $hoursWeekdays !== ''): ?>
                <div>
                    <p style="font-weight: 600; color: var(--text); margin-bottom: 0.5rem;"><?php echo sanitize($hoursWeekdaysLabel); ?></p>
                    <p style="color: var(--muted);"><?php echo sanitize($hoursWeekdays); ?></p>
                </div>
                <?php endif; ?>
                <?php if ($hoursSaturdayLabel !== '' || $hoursSaturday !== ''): ?>
                <div>
                    <p style="font-weight: 600; color: var(--text); margin-bottom: 0.5rem;"><?php echo sanitize($hoursSaturdayLabel); ?></p>
                    <p style="color: var(--muted);"><?php echo sanitize($hoursSaturday); ?></p>
                </div>
                <?php endif; ?>
                <?php if ($hoursSundayLabel !== '' || $hoursSunday !== ''): ?>
                <div>
                    <p style="font-weight: 600; color: var(--text); margin-bottom: 0.5rem;"><?php echo sanitize($hoursSundayLabel); ?></p>
                    <p style="color: var(--muted);"><?php echo sanitize($hoursSunday); ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($socialTitle !== '' || $socialDesc !== '' || $socialFacebook !== '' || $socialInstagram !== '' || $socialTwitter !== ''): ?>
        <div style="padding: 2rem; background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); border-radius: 0.5rem; text-align: center; color: white;">
            <h3 style="color: white; margin-bottom: 1rem;"><?php echo sanitize($socialTitle); ?></h3>
            <?php if ($socialDesc !== ''): ?><p style="margin-bottom: 1.5rem; opacity: 0.9;"><?php echo sanitize($socialDesc); ?></p><?php endif; ?>
            <div style="display: flex; justify-content: center; gap: 1.5rem; flex-wrap: wrap; align-items: center;">
                <?php if ($socialFacebook !== ''): ?>
                <a href="<?php echo sanitize($socialFacebook); ?>" target="_blank" rel="noopener noreferrer" aria-label="Facebook" style="color: white; text-decoration: none; padding: 0.6rem 1rem; background: rgba(255,255,255,0.2); border-radius: 0.5rem; transition: background 0.3s; display: inline-flex; align-items: center; gap: 0.5rem;" onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    <span>Facebook</span>
                </a>
                <?php endif; ?>
                <?php if ($socialInstagram !== ''): ?>
                <a href="<?php echo sanitize($socialInstagram); ?>" target="_blank" rel="noopener noreferrer" aria-label="Instagram" style="color: white; text-decoration: none; padding: 0.6rem 1rem; background: rgba(255,255,255,0.2); border-radius: 0.5rem; transition: background 0.3s; display: inline-flex; align-items: center; gap: 0.5rem;" onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
                    <span>Instagram</span>
                </a>
                <?php endif; ?>
                <?php if ($socialTwitter !== ''): ?>
                <a href="<?php echo sanitize($socialTwitter); ?>" target="_blank" rel="noopener noreferrer" aria-label="X (Twitter)" style="color: white; text-decoration: none; padding: 0.6rem 1rem; background: rgba(255,255,255,0.2); border-radius: 0.5rem; transition: background 0.3s; display: inline-flex; align-items: center; gap: 0.5rem;" onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                    <span>X</span>
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>
