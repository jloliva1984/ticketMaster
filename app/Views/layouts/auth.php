<!DOCTYPE html>
<html lang="<?= service('request')->getLocale() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token-name" content="<?= csrf_token() ?>">
    <meta name="csrf-token"      content="<?= csrf_hash() ?>">
    <title><?= isset($pageTitle) ? esc($pageTitle) . ' — ' : '' ?>TicketMaster.LT</title>

    <!-- Bootstrap 5.3 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #435ebe;
            --primary-dark: #3550b0;
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Nunito', sans-serif;
            background: linear-gradient(135deg, #1e3a8a 0%, #312e81 50%, #1e1b4b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .auth-card {
            background: #fff;
            border-radius: 16px;
            padding: 48px 40px 40px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 25px 60px rgba(0,0,0,.25);
        }
        .auth-brand {
            text-align: center;
            margin-bottom: 32px;
        }
        .auth-brand .brand-icon {
            width: 56px; height: 56px;
            background: var(--primary);
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.6rem;
            margin-bottom: 12px;
        }
        .auth-brand h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }
        .auth-brand h1 span { color: var(--primary); }
        .auth-brand p { color: #64748b; font-size: .9rem; margin-top: 4px; }

        .form-label { font-weight: 600; font-size: .85rem; color: #475569; }
        .form-control {
            border-color: #e2e8f0;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: .9rem;
        }
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67,94,190,.15);
        }
        .input-group-text {
            background: #f8fafc;
            border-color: #e2e8f0;
            color: #94a3b8;
            border-radius: 8px 0 0 8px;
        }
        .input-group .form-control { border-radius: 0 8px 8px 0; }

        .btn-auth {
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 11px;
            font-weight: 700;
            font-size: .95rem;
            width: 100%;
            transition: background .2s;
        }
        .btn-auth:hover { background: var(--primary-dark); color: #fff; }

        .lang-switch {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-top: 20px;
        }
        .lang-switch a {
            font-size: .78rem;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: .05em;
            text-decoration: none;
            padding: 3px 10px;
            border-radius: 99px;
            border: 1px solid #e2e8f0;
            transition: all .15s;
        }
        .lang-switch a.active,
        .lang-switch a:hover {
            color: var(--primary);
            border-color: var(--primary);
            background: #eef1fb;
        }
        .auth-footer {
            text-align: center;
            margin-top: 28px;
            font-size: .78rem;
            color: #94a3b8;
        }
        /* Password toggle */
        .password-wrapper { position: relative; }
        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #94a3b8;
            font-size: 1rem;
            background: none;
            border: none;
            padding: 0;
        }
        .password-toggle:hover { color: var(--primary); }
    </style>
</head>
<body>

    <div class="auth-card">

        <!-- Brand -->
        <div class="auth-brand">
            <div class="brand-icon"><i class="bi bi-ticket-perforated-fill"></i></div>
            <h1>TicketMaster<span>.LT</span></h1>
            <p><?= isset($pageTitle) ? esc($pageTitle) : 'Sistema de Gestión' ?></p>
        </div>

        <!-- Flash messages -->
        <?php if ($error = session()->getFlashdata('error')): ?>
            <div class="alert alert-danger d-flex align-items-center gap-2 mb-3 py-2">
                <i class="bi bi-exclamation-circle-fill"></i>
                <span><?= esc($error) ?></span>
            </div>
        <?php endif; ?>

        <?php if ($success = session()->getFlashdata('success')): ?>
            <div class="alert alert-success d-flex align-items-center gap-2 mb-3 py-2">
                <i class="bi bi-check-circle-fill"></i>
                <span><?= esc($success) ?></span>
            </div>
        <?php endif; ?>

        <?php if ($errors = session()->getFlashdata('errors')): ?>
            <div class="alert alert-danger mb-3 py-2">
                <ul class="mb-0 ps-3">
                    <?php foreach ($errors as $err): ?>
                        <li><?= esc($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Main content section -->
        <?= $this->renderSection('content') ?>

        <!-- Language switcher -->
        <div class="lang-switch">
            <?php $locale = service('request')->getLocale(); ?>
            <a href="<?= base_url('lang/en') ?>" class="<?= $locale === 'en' ? 'active' : '' ?>">
                <i class="bi bi-translate"></i> EN
            </a>
            <a href="<?= base_url('lang/es') ?>" class="<?= $locale === 'es' ? 'active' : '' ?>">
                <i class="bi bi-translate"></i> ES
            </a>
        </div>

        <div class="auth-footer">
            <?= lang('General.footer_author') ?>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password visibility toggle
        document.querySelectorAll('.password-toggle').forEach(btn => {
            btn.addEventListener('click', () => {
                const input = btn.previousElementSibling;
                if (input.type === 'password') {
                    input.type = 'text';
                    btn.innerHTML = '<i class="bi bi-eye-slash"></i>';
                } else {
                    input.type = 'password';
                    btn.innerHTML = '<i class="bi bi-eye"></i>';
                }
            });
        });
    </script>
    <?= $this->renderSection('scripts') ?>
</body>
</html>
