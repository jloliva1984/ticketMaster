<?php
/**
 * Sidebar partial
 * Active item detection: $activeMenu variable set by controller
 * e.g. $data['activeMenu'] = 'dashboard';
 */
$activeMenu = $activeMenu ?? '';
$locale     = service('request')->getLocale();
?>

<div id="sidebar">
    <div class="sidebar-wrapper">

        <!-- ── Brand ──────────────────────────────────────── -->
        <div class="sidebar-header">
            <a href="<?= base_url('/') ?>" class="sidebar-brand">
                <div class="brand-icon">
                    <i class="bi bi-ticket-perforated-fill"></i>
                </div>
                <span>TicketMaster<span style="color:#6c7fd8">.LT</span></span>
            </a>
            <button class="sidebar-hide-btn" aria-label="Close sidebar">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <!-- ── Menu ───────────────────────────────────────── -->
        <div class="sidebar-menu">
            <ul class="menu ps-0">

                <!-- Main -->
                <li class="menu-title"><?= lang('General.menu_main') ?></li>

                <!-- Dashboard -->
                <li class="<?= $activeMenu === 'dashboard' ? 'active' : '' ?>">
                    <a href="<?= base_url('/') ?>" class="sidebar-link">
                        <i class="bi bi-grid-fill menu-icon"></i>
                        <span><?= lang('General.dashboard') ?></span>
                    </a>
                </li>

                <!-- ── Operations ─────────────────────────── -->
                <li class="menu-title"><?= lang('General.menu_operations') ?></li>

                <!-- Tareas / Tasks (Phase 6) -->
                <li class="<?= $activeMenu === 'tasks' ? 'active' : '' ?> has-sub">
                    <a href="#">
                        <i class="bi bi-truck menu-icon"></i>
                        <span><?= lang('General.tasks') ?></span>
                        <i class="bi bi-chevron-right menu-arrow"></i>
                    </a>
                    <ul class="submenu">
                        <li><a href="<?= base_url('tasks') ?>"><i class="bi bi-list-ul"></i> <?= lang('General.list') ?></a></li>
                        <li><a href="<?= base_url('tasks/create') ?>"><i class="bi bi-plus-circle"></i> <?= lang('General.new_task') ?></a></li>
                    </ul>
                </li>

                <!-- Facturas / Invoices (Phase 5) -->
                <li class="<?= $activeMenu === 'invoices' ? 'active' : '' ?> has-sub">
                    <a href="#">
                        <i class="bi bi-receipt menu-icon"></i>
                        <span><?= lang('General.invoices') ?></span>
                        <i class="bi bi-chevron-right menu-arrow"></i>
                    </a>
                    <ul class="submenu">
                        <li><a href="<?= base_url('invoices') ?>"><i class="bi bi-list-ul"></i> <?= lang('General.list') ?></a></li>
                        <li><a href="<?= base_url('invoices/create') ?>"><i class="bi bi-plus-circle"></i> <?= lang('General.new_invoice') ?></a></li>
                    </ul>
                </li>

                <!-- Tickets sin Factura (Phase 8) -->
                <li class="<?= $activeMenu === 'unmatched' ? 'active' : '' ?>">
                    <a href="<?= base_url('unmatched-tickets') ?>" class="sidebar-link">
                        <i class="bi bi-exclamation-triangle menu-icon text-warning"></i>
                        <span><?= lang('General.unmatched_tickets') ?></span>
                    </a>
                </li>

                <!-- ── Catalog ─────────────────────────────── -->
                <li class="menu-title"><?= lang('General.menu_catalog') ?></li>

                <!-- Canteras / Quarries (Phase 4) -->
                <li class="<?= $activeMenu === 'quarries' ? 'active' : '' ?>">
                    <a href="<?= base_url('quarries') ?>" class="sidebar-link">
                        <i class="bi bi-geo-alt menu-icon"></i>
                        <span><?= lang('General.quarries') ?></span>
                    </a>
                </li>

                <!-- Camiones / Trucks (Phase 4) -->
                <li class="<?= $activeMenu === 'trucks' ? 'active' : '' ?>">
                    <a href="<?= base_url('trucks') ?>" class="sidebar-link">
                        <i class="bi bi-truck-front menu-icon"></i>
                        <span><?= lang('General.trucks') ?></span>
                    </a>
                </li>

                <!-- ── Reports ─────────────────────────────── -->
                <li class="menu-title"><?= lang('General.menu_reports') ?></li>

                <!-- Reportes (Phase 7) -->
                <li class="<?= $activeMenu === 'reports' ? 'active' : '' ?>">
                    <a href="<?= base_url('reports') ?>" class="sidebar-link">
                        <i class="bi bi-bar-chart-fill menu-icon"></i>
                        <span><?= lang('General.reports') ?></span>
                    </a>
                </li>

                <!-- ── Admin ───────────────────────────────── -->
                <?php if (isset($currentUser) && ($currentUser['role'] ?? '') === 'admin'): ?>
                <li class="menu-title"><?= lang('General.menu_admin') ?></li>

                <li class="<?= $activeMenu === 'users' ? 'active' : '' ?>">
                    <a href="<?= base_url('users') ?>" class="sidebar-link">
                        <i class="bi bi-people-fill menu-icon"></i>
                        <span><?= lang('General.users') ?></span>
                    </a>
                </li>
                <?php endif; ?>

            </ul>
        </div><!-- /.sidebar-menu -->

    </div><!-- /.sidebar-wrapper -->
</div><!-- /#sidebar -->
