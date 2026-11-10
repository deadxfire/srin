<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? '419 - Page Expired', ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="icon" type="image/svg+xml" href="[[ asset('/favicon.svg') ]]">
    <link rel="alternate icon" href="[[ asset('/favicon.ico') ]]">
    <style>
        body { font-family: system-ui, -apple-system, sans-serif; background: #0f172a; color: #f8fafc; min-height: 100vh; display: flex; align-items: center; justify-content: center; margin: 0; padding: 20px; text-align: center; }
        .card { max-width: 500px; background: #1e293b; border-radius: 16px; padding: 40px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.4); border: 1px solid #334155; }
        .badge { font-size: 3.5rem; font-weight: 800; color: #fbbf24; font-family: monospace; line-height: 1; margin-bottom: 12px; }
        h1 { margin: 0 0 12px; font-size: 1.5rem; }
        p { color: #94a3b8; font-size: 0.95rem; margin-bottom: 24px; }
        .btn { display: inline-block; background: #2563eb; color: #fff; text-decoration: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; font-size: 0.9rem; }
        .btn:hover { background: #1d4ed8; }
        .credit { margin-top: 24px; font-size: 0.8rem; color: #64748b; }
    </style>
</head>
<body>
    <div class="card">
        <div class="badge">419</div>
        <h1>Page Expired</h1>
        <p><?= htmlspecialchars($message ?? 'Your session or CSRF security token has expired. Please refresh the page and try submitting again.', ENT_QUOTES, 'UTF-8') ?></p>
        <a href="javascript:location.reload()" class="btn">&#x21bb; Refresh Page</a>
        <div class="credit">Srin Engine &bull; Developed by <a href="https://github.com/deadxfire" target="_blank" style="color: inherit; text-decoration: underline;">Arindam Makar</a></div>
    </div>
</body>
</html>

