<?php

require __DIR__ . '/../config.php';

requireAdminLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';

    if ($action === 'approve') {

        $id = (int) ($_POST['id'] ?? 0);

        $statement = $pdo->prepare('UPDATE comments SET status = "approved" WHERE id = :id');

        $statement->execute(['id' => $id]);

        setFlash('admin_success', 'Yorum onaylandı.');

        redirect('comments.php');

    } elseif ($action === 'reject') {

        $id = (int) ($_POST['id'] ?? 0);

        $statement = $pdo->prepare('UPDATE comments SET status = "rejected" WHERE id = :id');

        $statement->execute(['id' => $id]);

        setFlash('admin_success', 'Yorum reddedildi.');

        redirect('comments.php');

    } elseif ($action === 'delete') {

        $id = (int) ($_POST['id'] ?? 0);
        $baseDir = realpath(__DIR__ . '/..') ?: (__DIR__ . '/..');
        if ($id > 0) {
            $sel = $pdo->prepare('SELECT id, images FROM comments WHERE id = :id');
            $sel->execute(['id' => $id]);
            $row = $sel->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                deleteCommentImageFiles([$row], $baseDir);
            }
        }
        $statement = $pdo->prepare('DELETE FROM comments WHERE id = :id');
        $statement->execute(['id' => $id]);
        setFlash('admin_success', 'Yorum silindi.');
        redirect('comments.php');

    } elseif ($action === 'bulk_delete') {

        $ids = isset($_POST['ids']) && is_array($_POST['ids']) ? array_map('intval', $_POST['ids']) : [];
        $ids = array_filter($ids, function ($id) { return $id > 0; });
        if (!empty($ids)) {
            $baseDir = realpath(__DIR__ . '/..') ?: (__DIR__ . '/..');
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $sel = $pdo->prepare("SELECT id, images FROM comments WHERE id IN ($placeholders)");
            $sel->execute(array_values($ids));
            $rows = $sel->fetchAll(PDO::FETCH_ASSOC);
            deleteCommentImageFiles($rows, $baseDir);
            $stmt = $pdo->prepare("DELETE FROM comments WHERE id IN ($placeholders)");
            $stmt->execute(array_values($ids));
            $count = count($ids);
            setFlash('admin_success', $count . ' yorum silindi.');
        } else {
            setFlash('admin_error', 'Silinecek yorum seçilmedi.');
        }
        $redirectStatus = isset($_POST['status']) ? $_POST['status'] : 'all';
        $allowed = ['all', 'pending', 'approved', 'rejected'];
        $redirectStatus = in_array($redirectStatus, $allowed, true) ? $redirectStatus : 'all';
        redirect('comments.php' . ($redirectStatus !== 'all' ? '?status=' . urlencode($redirectStatus) : ''));

    } elseif ($action === 'delete_all') {

        $baseDir = realpath(__DIR__ . '/..') ?: (__DIR__ . '/..');
        $sel = $pdo->query('SELECT id, images FROM comments');
        $rows = $sel ? $sel->fetchAll(PDO::FETCH_ASSOC) : [];
        deleteCommentImageFiles($rows, $baseDir);
        $stmt = $pdo->prepare('DELETE FROM comments');
        $stmt->execute();
        $count = $stmt->rowCount();
        setFlash('admin_success', 'Tüm yorumlar silindi. (' . $count . ' adet)');
        redirect('comments.php');
    }
}

$status = $_GET['status'] ?? 'all';

$allowedStatuses = ['all', 'pending', 'approved', 'rejected'];

$status = in_array($status, $allowedStatuses, true) ? $status : 'all';

$whereClause = $status !== 'all' ? "WHERE c.status = :status" : "";

$params = $status !== 'all' ? ['status' => $status] : [];

$statement = $pdo->prepare("SELECT c.*, p.name AS product_name FROM comments c LEFT JOIN products p ON p.id = c.product_id $whereClause ORDER BY c.id DESC");

$statement->execute($params);

$comments = $statement->fetchAll();

if (!empty($_GET['ajax']) && $_GET['ajax'] === '1') {
    header('Content-Type: text/html; charset=utf-8');
    echo '<div id="admin-comments-ajax-container">';
    include __DIR__ . '/partials/admin_comments_list_content.php';
    echo '</div>';
    exit;
}

include __DIR__ . '/partials/header.php';
?>

<!-- ============================================
     YORUM YÖNETİMİ ARAYÜZÜ
     ============================================
     Yorumları listeler, filtreler ve yönetir
-->
<section class="card" style="margin-top: 2rem; padding-top: 1.5rem; margin-bottom: 3rem; padding-bottom: 3rem;">
    <h1>Yorum Yönetimi</h1>
    
    <!-- Başarı mesajı gösterimi (flash message) -->
    <?php if ($message = getFlash('admin_success')): ?>
        <div class="alert alert-success"><?php echo sanitize($message); ?></div>
    <?php endif; ?>
    <?php if ($message = getFlash('admin_error')): ?>
        <div class="alert alert-error"><?php echo sanitize($message); ?></div>
    <?php endif; ?>

    <div id="admin-comments-ajax-container">
        <?php include __DIR__ . '/partials/admin_comments_list_content.php'; ?>
    </div>
    <form method="post" id="admin-comment-single-action-form" style="display: none;">
        <input type="hidden" name="action" id="admin-comment-single-action">
        <input type="hidden" name="id" id="admin-comment-single-id">
    </form>
</section>

<!-- Yorum fotoğrafı tam ekran lightbox (geri dönüş için) -->
<div id="admin-comment-lightbox" style="display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(0,0,0,0.9); align-items: center; justify-content: center; padding: 2rem; box-sizing: border-box;" role="dialog" aria-modal="true" aria-label="Yorum fotoğrafı">
    <button type="button" id="admin-comment-lightbox-close" style="position: absolute; top: 1rem; right: 1rem; padding: 0.5rem 1rem; font-size: 1rem; background: #fff; border: 1px solid #ccc; border-radius: 0.5rem; cursor: pointer; z-index: 1;">Geri</button>
    <img id="admin-comment-lightbox-img" src="" alt="Yorum fotoğrafı" style="max-width: 100%; max-height: 100%; object-fit: contain;">
</div>

<script>
(function() {
    var container = document.getElementById('admin-comments-ajax-container');
    if (!container) return;

    // Lightbox: yorum fotoğrafına tıklanınca tam ekran göster, Geri ile kapat
    document.addEventListener('click', function(e) {
        var link = e.target.closest('a.admin-comment-image-link');
        if (link) {
            e.preventDefault();
            var src = link.getAttribute('data-fullimg') || link.getAttribute('href') || '';
            if (!src) return;
            var lb = document.getElementById('admin-comment-lightbox');
            var lbImg = document.getElementById('admin-comment-lightbox-img');
            if (lb && lbImg) {
                lbImg.src = src;
                lb.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        }
    });
    var lb = document.getElementById('admin-comment-lightbox');
    var lbClose = document.getElementById('admin-comment-lightbox-close');
    if (lb && lbClose) {
        function closeLightbox() {
            lb.style.display = 'none';
            document.body.style.overflow = '';
        }
        lbClose.addEventListener('click', closeLightbox);
        lb.addEventListener('click', function(e) {
            if (e.target === lb) closeLightbox();
        });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && lb.style.display === 'flex') closeLightbox();
        });
    }

    function updateCommentsBulkBtn() {
        var form = container.querySelector('#admin-comments-bulk-form');
        var bulkBtn = container.querySelector('#admin-comments-bulk-btn');
        if (!form || !bulkBtn) return;
        var checkboxes = form.querySelectorAll('.admin-comment-cb');
        var any = Array.prototype.some.call(checkboxes, function(cb) { return cb.checked; });
        bulkBtn.disabled = !any;
    }
    container.addEventListener('change', function(e) {
        var target = e.target;
        if (target && target.id === 'admin-comments-select-all') {
            var form = container.querySelector('#admin-comments-bulk-form');
            if (form) {
                var checkboxes = form.querySelectorAll('.admin-comment-cb');
                checkboxes.forEach(function(cb) { cb.checked = target.checked; });
            }
            updateCommentsBulkBtn();
        } else if (target && target.classList && target.classList.contains('admin-comment-cb')) {
            updateCommentsBulkBtn();
        }
    });
    container.addEventListener('click', function(e) {
        var actionBtn = e.target && e.target.classList && e.target.classList.contains('admin-comment-action-btn') ? e.target : (e.target && e.target.closest ? e.target.closest('.admin-comment-action-btn') : null);
        if (actionBtn) {
            e.preventDefault();
            var act = actionBtn.getAttribute('data-action');
            var id = actionBtn.getAttribute('data-id');
            if (!act || !id) return;
            if (act === 'delete' && !confirm('Silmek istediğinize emin misiniz?')) return;
            var singleForm = document.getElementById('admin-comment-single-action-form');
            var actionInput = document.getElementById('admin-comment-single-action');
            var idInput = document.getElementById('admin-comment-single-id');
            if (singleForm && actionInput && idInput) {
                actionInput.value = act;
                idInput.value = id;
                singleForm.submit();
            }
            return;
        }
        var btn = e.target && e.target.id === 'admin-comments-delete-all-btn' ? e.target : (e.target && e.target.closest ? e.target.closest('#admin-comments-delete-all-btn') : null);
        if (btn) {
            e.preventDefault();
            if (confirm('Tüm yorumları silmek istediğinize emin misiniz? Bu işlem geri alınamaz.')) {
                var form = document.getElementById('admin-comments-delete-all-form');
                if (form) form.submit();
            }
            return;
        }
        var link = e.target.closest('a.admin-comments-filter-link');
        if (!link) return;
        e.preventDefault();
        var href = link.getAttribute('href') || '';
        if (!href) return;
        var url = href + (href.indexOf('?') >= 0 ? '&' : '?') + 'ajax=1';
        container.style.opacity = '0.6';
        container.style.pointerEvents = 'none';
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(r) { return r.text(); })
            .then(function(html) {
                var parser = new DOMParser();
                var doc = parser.parseFromString(html, 'text/html');
                var wrap = doc.getElementById('admin-comments-ajax-container');
                if (wrap) container.innerHTML = wrap.innerHTML;
                container.style.opacity = '';
                container.style.pointerEvents = '';
            })
            .catch(function() {
                container.style.opacity = '';
                container.style.pointerEvents = '';
                window.location.href = href;
            });
        history.replaceState(null, '', href);
    });
})();
</script>

<?php 
include __DIR__ . '/partials/footer.php'; 
?>

