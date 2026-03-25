<?= $this->extend('layouts/auth') ?>

<?= $this->section('content') ?>

<form action="<?= base_url('auth/login') ?>" method="POST" novalidate>
    <?= csrf_field() ?>

    <!-- Email -->
    <div class="mb-3">
        <label for="email" class="form-label">
            <?= lang('General.email') ?>
        </label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
            <input
                type="email"
                id="email"
                name="email"
                class="form-control <?= session()->getFlashdata('errors') ? 'is-invalid' : '' ?>"
                value="<?= esc(old('email')) ?>"
                placeholder="admin@ticketmaster.lt"
                autocomplete="email"
                required
            >
        </div>
    </div>

    <!-- Password -->
    <div class="mb-3">
        <label for="password" class="form-label">
            <?= lang('General.password') ?>
        </label>
        <div class="input-group password-wrapper">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input
                type="password"
                id="password"
                name="password"
                class="form-control"
                placeholder="••••••••"
                autocomplete="current-password"
                required
            >
            <button type="button" class="password-toggle" tabindex="-1">
                <i class="bi bi-eye"></i>
            </button>
        </div>
    </div>

    <!-- Remember me -->
    <div class="mb-4 d-flex align-items-center justify-content-between">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
            <label class="form-check-label" for="remember" style="font-size:.85rem;color:#64748b">
                <?= lang('General.remember') ?>
            </label>
        </div>
    </div>

    <!-- Submit -->
    <button type="submit" class="btn-auth">
        <i class="bi bi-box-arrow-in-right me-2"></i>
        <?= lang('General.login') ?>
    </button>

</form>

<?= $this->endSection() ?>
