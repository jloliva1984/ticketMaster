<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TicketMaster.LT — Phase 1 OK</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #0f172a;
            color: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .card {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 12px;
            padding: 48px;
            max-width: 480px;
            text-align: center;
            box-shadow: 0 25px 50px rgba(0,0,0,0.4);
        }
        .logo { font-size: 2rem; font-weight: 700; color: #38bdf8; margin-bottom: 8px; }
        .subtitle { color: #94a3b8; margin-bottom: 32px; }
        .badge {
            display: inline-block;
            background: #022c22;
            color: #4ade80;
            border: 1px solid #166534;
            border-radius: 99px;
            padding: 6px 16px;
            font-size: 0.85rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            margin-bottom: 24px;
        }
        .info { font-size: 0.85rem; color: #64748b; }
        .info strong { color: #94a3b8; }
        footer { margin-top: 32px; font-size: 0.75rem; color: #475569; }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo">TicketMaster.LT</div>
        <div class="subtitle">Sistema de Gestión de Tickets y Facturas</div>
        <div class="badge">✓ FASE 1 — ESTRUCTURA BASE OK</div>
        <div class="info">
            <p><strong>Framework:</strong> CodeIgniter <?= \CodeIgniter\CodeIgniter::CI_VERSION ?></p>
            <p><strong>PHP:</strong> <?= PHP_VERSION ?></p>
            <p><strong>Ambiente:</strong> <?= ENVIRONMENT ?></p>
        </div>
        <footer>Ing: Jorge Luis Oliva Matos</footer>
    </div>
</body>
</html>
