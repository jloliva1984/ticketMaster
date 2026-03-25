<!DOCTYPE html>
<html lang="<?= service('request')->getLocale() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <!-- CSRF meta tags for AJAX -->
    <meta name="csrf-token-name" content="<?= csrf_token() ?>">
    <meta name="csrf-token"      content="<?= csrf_hash() ?>">

    <title><?= isset($pageTitle) ? esc($pageTitle) . ' — ' : '' ?>TicketMaster.LT</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?= base_url('assets/img/favicon.svg') ?>">

    <!-- Bootstrap 5.3 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

    <!-- Bootstrap Icons 1.11 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- DataTables + Bootstrap 5 theme -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.2/css/responsive.bootstrap5.min.css">

    <!-- App CSS (Mazer-inspired) -->
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">

    <!-- Page-specific styles injected by child views -->
    <?= $this->renderSection('styles') ?>
</head>
<body>

<!-- ═══════════════════════════════════════════════════════════
     APP WRAPPER
═══════════════════════════════════════════════════════════ -->
<div id="app">

    <!-- ── Sidebar ──────────────────────────────────────────── -->
    <?= $this->include('layouts/_sidebar') ?>

    <!-- Mobile overlay -->
    <div id="sidebar-overlay"></div>

    <!-- ── Main ─────────────────────────────────────────────── -->
    <div id="main">

        <!-- Topbar / Navbar -->
        <?= $this->include('layouts/_navbar') ?>

        <!-- Page content -->
        <div id="main-content" class="fade-in">

            <!-- Page heading -->
            <?php if (isset($pageTitle)): ?>
            <div class="page-heading">
                <h3><?= esc($pageTitle) ?></h3>
                <?php if (isset($breadcrumbs)): ?>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="<?= base_url('/') ?>"><?= lang('General.home') ?></a>
                        </li>
                        <?php foreach ($breadcrumbs as $label => $url): ?>
                            <?php if ($url): ?>
                                <li class="breadcrumb-item"><a href="<?= $url ?>"><?= esc($label) ?></a></li>
                            <?php else: ?>
                                <li class="breadcrumb-item active"><?= esc($label) ?></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ol>
                </nav>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Child view content -->
            <?= $this->renderSection('content') ?>

        </div><!-- /#main-content -->

        <!-- Footer -->
        <?= $this->include('layouts/_footer') ?>

    </div><!-- /#main -->
</div><!-- /#app -->

<!-- ── Scripts ─────────────────────────────────────────────── -->
<!-- jQuery (required by DataTables) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- Bootstrap 5 bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- DataTables -->
<script src="https://cdn.datatables.net/2.0.7/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.0.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/3.0.2/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/3.0.2/js/responsive.bootstrap5.min.js"></script>

<!-- App JS -->
<script src="<?= base_url('assets/js/app.js') ?>"></script>

<!-- Page-specific scripts injected by child views -->
<?= $this->renderSection('scripts') ?>

</body>
</html>
