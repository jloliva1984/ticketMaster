<?php
$locale      = service('request')->getLocale();
$currentUser = $currentUser ?? ['name' => 'Guest', 'role' => 'guest'];
$initials    = strtoupper(substr($currentUser['name'] ?? 'U', 0, 2));
?>

<div id="header">
    <div class="header-inner">

        <!-- Burger button -->
        <button class="burger-btn" aria-label="Toggle sidebar">
            <i class="bi bi-justify"></i>
        </button>

        <!-- Page title (mobile) -->
        <span class="header-title d-none d-md-block">
            <?= isset($pageTitle) ? esc($pageTitle) : 'TicketMaster.LT' ?>
        </span>

        <!-- Right side -->
        <div class="header-right">

            <!-- Language switcher -->
            <div class="d-flex gap-1">
                <a href="<?= base_url('lang/en') ?>"
                   class="lang-btn <?= $locale === 'en' ? 'active' : '' ?>"
                   title="English">
                    <i class="bi bi-translate"></i> EN
                </a>
                <a href="<?= base_url('lang/es') ?>"
                   class="lang-btn <?= $locale === 'es' ? 'active' : '' ?>"
                   title="Español">
                    <i class="bi bi-translate"></i> ES
                </a>
            </div>

            <!-- User dropdown -->
            <div class="dropdown">
                <button class="avatar" data-bs-toggle="dropdown" aria-expanded="false"
                        title="<?= esc($currentUser['name']) ?>">
                    <?= $initials ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2" style="min-width:200px">
                    <li class="px-3 py-2 border-bottom">
                        <div class="fw-bold text-dark"><?= esc($currentUser['name']) ?></div>
                        <div class="text-muted" style="font-size:.78rem"><?= esc(ucfirst($currentUser['role'] ?? '')) ?></div>
                    </li>
                    <li>
                        <a class="dropdown-item" href="<?= base_url('profile') ?>">
                            <i class="bi bi-person me-2"></i><?= lang('General.profile') ?>
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item text-danger" href="<?= base_url('auth/logout') ?>">
                            <i class="bi bi-box-arrow-right me-2"></i><?= lang('General.logout') ?>
                        </a>
                    </li>
                </ul>
            </div>

        </div><!-- /.header-right -->
    </div><!-- /.header-inner -->
</div><!-- /#header -->
