<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Srin Engine Documentation', ENT_QUOTES, 'UTF-8') ?></title>
    
    <link rel="icon" type="image/svg+xml" href="{{asset('favicon.svg')}}">
    <link rel="alternate icon" href="{{asset('favicon.ico')}}">

    <style>
        :root {
            --bg-body: #0b0f19;
            --bg-sidebar: #0f172a;
            --bg-card: #131d35;
            --bg-card-alt: #1e293b;
            --border-color: rgba(255, 255, 255, 0.08);
            --border-focus: #38bdf8;
            --text-main: #f1f5f9;
            --text-muted: #94a3b8;
            --text-dim: #64748b;
            --primary: #38bdf8;
            --primary-glow: rgba(56, 189, 248, 0.15);
            --primary-dark: #0284c7;
            --accent: #818cf8;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --code-bg: #090d16;
            --radius-sm: 6px;
            --radius-md: 10px;
            --radius-lg: 16px;
            --sidebar-width: 290px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            line-height: 1.65;
            -webkit-font-smoothing: antialiased;
        }

        a {
            color: var(--primary);
            text-decoration: none;
            transition: color 0.15s ease;
        }

        a:hover {
            color: #7dd3fc;
        }

        /* Top Header */
        .docs-header {
            position: sticky;
            top: 0;
            z-index: 50;
            background: rgba(11, 15, 25, 0.88);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border-color);
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .brand-link {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.2rem;
            font-weight: 700;
            color: #fff;
        }

        .brand-logo {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 800;
            font-size: 1.1rem;
            box-shadow: 0 0 15px var(--primary-glow);
        }

        .version-badge {
            font-size: 0.72rem;
            padding: 2px 8px;
            background: rgba(56, 189, 248, 0.15);
            color: var(--primary);
            border: 1px solid rgba(56, 189, 248, 0.3);
            border-radius: 999px;
            font-weight: 600;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .search-box {
            position: relative;
        }

        .search-box input {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-color);
            color: #fff;
            border-radius: 8px;
            padding: 8px 14px 8px 34px;
            font-size: 0.85rem;
            width: 220px;
            outline: none;
            transition: all 0.2s;
        }

        .search-box input:focus {
            width: 280px;
            border-color: var(--primary);
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 10px var(--primary-glow);
        }

        .search-icon {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-dim);
            font-size: 0.85rem;
            pointer-events: none;
        }

        .nav-btn {
            padding: 6px 14px;
            font-size: 0.85rem;
            font-weight: 600;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }

        .nav-btn-ghost {
            color: var(--text-muted);
        }

        .nav-btn-ghost:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.05);
        }

        .nav-btn-primary {
            background: var(--primary-dark);
            color: #fff;
        }

        .nav-btn-primary:hover {
            background: var(--primary);
            color: #0b0f19;
            box-shadow: 0 0 12px var(--primary-glow);
        }

        .mobile-toggle {
            display: none;
            background: transparent;
            border: none;
            color: var(--text-main);
            font-size: 1.5rem;
            cursor: pointer;
        }

        /* Container & Layout */
        .docs-wrapper {
            display: flex;
            min-height: calc(100vh - 64px);
            max-width: 1440px;
            margin: 0 auto;
        }

        /* Sidebar Navigation */
        .docs-sidebar {
            width: var(--sidebar-width);
            flex-shrink: 0;
            background: var(--bg-sidebar);
            border-right: 1px solid var(--border-color);
            position: sticky;
            top: 64px;
            height: calc(100vh - 64px);
            overflow-y: auto;
            padding: 24px 16px 48px;
        }

        .docs-sidebar::-webkit-scrollbar {
            width: 5px;
        }

        .docs-sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
        }

        .sidebar-section {
            margin-bottom: 24px;
        }

        .sidebar-heading {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--text-dim);
            font-weight: 700;
            padding: 6px 12px;
            margin-bottom: 4px;
        }

        .sidebar-links {
            list-style: none;
        }

        .sidebar-links li a {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 12px;
            font-size: 0.88rem;
            color: var(--text-muted);
            border-radius: var(--radius-sm);
            transition: all 0.15s ease;
            margin-bottom: 2px;
        }

        .sidebar-links li a:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.04);
            padding-left: 16px;
        }

        .sidebar-links li a.active {
            color: var(--primary);
            font-weight: 600;
            background: rgba(56, 189, 248, 0.1);
            border-left: 3px solid var(--primary);
            padding-left: 14px;
        }

        /* Main Content */
        .docs-main {
            flex-grow: 1;
            padding: 36px 48px 64px;
            max-width: calc(100% - var(--sidebar-width));
            overflow-x: hidden;
        }

        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.82rem;
            color: var(--text-dim);
            margin-bottom: 16px;
        }

        .breadcrumb span {
            color: var(--text-muted);
        }

        .doc-header {
            margin-bottom: 32px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 24px;
        }

        .doc-header h1 {
            font-size: 2.3rem;
            font-weight: 800;
            color: #fff;
            letter-spacing: -0.02em;
            margin-bottom: 12px;
            background: linear-gradient(135deg, #fff, #94a3b8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .doc-header .doc-lead {
            font-size: 1.15rem;
            color: var(--text-muted);
            line-height: 1.6;
        }

        /* Content Styling */
        .doc-content h2 {
            font-size: 1.55rem;
            font-weight: 700;
            color: #fff;
            margin: 40px 0 16px;
            padding-top: 16px;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .doc-content h3 {
            font-size: 1.2rem;
            font-weight: 600;
            color: #e2e8f0;
            margin: 28px 0 12px;
        }

        .doc-content p {
            margin-bottom: 16px;
            color: #cbd5e1;
            font-size: 0.98rem;
        }

        .doc-content ul, .doc-content ol {
            margin: 0 0 20px 24px;
            color: #cbd5e1;
        }

        .doc-content li {
            margin-bottom: 8px;
        }

        .doc-content strong {
            color: #fff;
        }

        /* Callouts / Alerts */
        .callout {
            padding: 16px 20px;
            border-radius: var(--radius-md);
            margin: 24px 0;
            font-size: 0.93rem;
            border-left: 4px solid;
            background: var(--bg-card);
        }

        .callout-note {
            border-color: var(--primary);
            background: rgba(56, 189, 248, 0.08);
        }

        .callout-tip {
            border-color: var(--success);
            background: rgba(16, 185, 129, 0.08);
        }

        .callout-warning {
            border-color: var(--warning);
            background: rgba(245, 158, 11, 0.08);
        }

        .callout-important {
            border-color: #a855f7;
            background: rgba(168, 85, 247, 0.08);
        }

        .callout-title {
            font-weight: 700;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .callout-note .callout-title { color: var(--primary); }
        .callout-tip .callout-title { color: var(--success); }
        .callout-warning .callout-title { color: var(--warning); }
        .callout-important .callout-title { color: #c084fc; }

        /* Code Blocks */
        pre {
            background: var(--code-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 18px 20px;
            margin: 20px 0 24px;
            overflow-x: auto;
            position: relative;
        }

        pre code {
            font-family: "JetBrains Mono", "Fira Code", Consolas, Menlo, monospace;
            font-size: 0.88rem;
            color: #e2e8f0;
            line-height: 1.6;
        }

        p code, li code, td code {
            font-family: "JetBrains Mono", Consolas, monospace;
            font-size: 0.85em;
            background: rgba(255, 255, 255, 0.08);
            color: #38bdf8;
            padding: 2px 6px;
            border-radius: 4px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .copy-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid var(--border-color);
            color: var(--text-dim);
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.75rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        pre:hover .copy-btn {
            color: var(--text-muted);
            background: rgba(255, 255, 255, 0.12);
        }

        .copy-btn:hover {
            color: #fff !important;
            background: var(--primary-dark) !important;
        }

        /* Tables */
        .table-wrapper {
            overflow-x: auto;
            margin: 24px 0 32px;
            border-radius: var(--radius-md);
            border: 1px solid var(--border-color);
            background: var(--bg-card);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
            text-align: left;
        }

        th {
            background: rgba(255, 255, 255, 0.03);
            color: var(--text-muted);
            font-weight: 600;
            padding: 12px 16px;
            border-bottom: 1px solid var(--border-color);
        }

        td {
            padding: 12px 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
            color: #cbd5e1;
            vertical-align: top;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover td {
            background: rgba(255, 255, 255, 0.02);
        }

        /* Previous / Next Navigation */
        .docs-pagination {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            margin-top: 56px;
            padding-top: 32px;
            border-top: 1px solid var(--border-color);
        }

        .page-card {
            flex: 1;
            padding: 18px 24px;
            border-radius: var(--radius-md);
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            transition: all 0.2s;
            display: flex;
            flex-direction: column;
        }

        .page-card:hover {
            border-color: var(--primary);
            background: rgba(56, 189, 248, 0.05);
            transform: translateY(-2px);
        }

        .page-card-sub {
            font-size: 0.75rem;
            color: var(--text-dim);
            text-transform: uppercase;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .page-card-title {
            font-size: 1.05rem;
            font-weight: 600;
            color: var(--text-main);
        }

        .page-card.next {
            text-align: right;
            margin-left: auto;
        }

        /* Footer */
        .docs-footer {
            margin-top: 64px;
            padding-top: 24px;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            font-size: 0.85rem;
            color: var(--text-dim);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .docs-sidebar {
                position: fixed;
                top: 64px;
                left: -100%;
                width: 300px;
                z-index: 99;
                transition: left 0.25s ease;
                background: #0f172a;
                box-shadow: 10px 0 30px rgba(0, 0, 0, 0.5);
            }

            .docs-sidebar.open {
                left: 0;
            }

            .mobile-toggle {
                display: block;
            }

            .docs-main {
                max-width: 100%;
                padding: 24px 20px;
            }

            .search-box input {
                width: 150px;
            }
        }
    </style>
</head>
<body>

    <!-- Header -->
    <header class="docs-header">
        <div class="header-left">
            <button class="mobile-toggle" onclick="toggleSidebar()" aria-label="Toggle navigation">&#9776;</button>
            <a href="{{route('home')}}" class="brand-link">
                <div class="brand-logo">S</div>
                <span>Srin Engine</span>
            </a>
            <span class="version-badge">v<?= config('app.version', '1.1.1') ?></span>
        </div>

        <div class="header-right">
            <div class="search-box">
                <span class="search-icon">&#128269;</span>
                <input type="text" id="docSearch" placeholder="Search docs..." onkeyup="filterTopics()">
            </div>
            <a href="{{route('home')}}" class="nav-btn nav-btn-ghost">Home</a>
            <a href="https://github.com/deadxfire/srin" target="_blank" rel="noopener" class="nav-btn nav-btn-ghost">GitHub</a>
            <a href="{{route('docs.show', ['topic' => 'installation'])}}" class="nav-btn nav-btn-primary">Quickstart &rarr;</a>
        </div>
    </header>

    <div class="docs-wrapper">
        <!-- Sidebar Navigation -->
        <aside class="docs-sidebar" id="sidebar">
            <?php foreach ($navigation as $sectionTitle => $topics): ?>
                <div class="sidebar-section">
                    <div class="sidebar-heading"><?= htmlspecialchars($sectionTitle, ENT_QUOTES, 'UTF-8') ?></div>
                    <ul class="sidebar-links">
                        <?php foreach ($topics as $slug => $label): ?>
                            <li>
                                <a href="<?= route('docs.show', ['topic' => $slug]) ?>" 
                                   class="<?= ($currentTopic === $slug) ? 'active' : '' ?>"
                                   data-topic="<?= strtolower($slug . ' ' . $label) ?>">
                                    <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        </aside>

        <!-- Main Content Area -->
        <main class="docs-main">
            <div class="breadcrumb">
                <a href="{{route('docs')}}">Documentation</a>
                <span>&rsaquo;</span>
                <span><?= htmlspecialchars($category ?? 'General', ENT_QUOTES, 'UTF-8') ?></span>
                <span>&rsaquo;</span>
                <span style="color: #fff;"><?= htmlspecialchars($topicTitle ?? '', ENT_QUOTES, 'UTF-8') ?></span>
            </div>

            <div class="doc-header">
                <h1><?= htmlspecialchars($topicTitle ?? 'Documentation', ENT_QUOTES, 'UTF-8') ?></h1>
                <?php if (isset($lead)): ?>
                    <p class="doc-lead"><?= htmlspecialchars($lead, ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
            </div>

            <div class="doc-content">
                @content
            </div>

            <!-- Previous / Next Navigation Links -->
            <div class="docs-pagination">
                <?php if ($prev): ?>
                    <a href="<?= route('docs.show', ['topic' => $prev['slug']]) ?>" class="page-card prev">
                        <span class="page-card-sub">&larr; Previous Chapter</span>
                        <span class="page-card-title"><?= htmlspecialchars($prev['title'], ENT_QUOTES, 'UTF-8') ?></span>
                    </a>
                <?php endif; ?>

                <?php if ($next): ?>
                    <a href="<?= route('docs.show', ['topic' => $next['slug']]) ?>" class="page-card next">
                        <span class="page-card-sub">Next Chapter &rarr;</span>
                        <span class="page-card-title"><?= htmlspecialchars($next['title'], ENT_QUOTES, 'UTF-8') ?></span>
                    </a>
                <?php endif; ?>
            </div>

            <footer class="docs-footer">
                <div>
                    Srin Engine is architected by <strong>Arindam Makar</strong>. Open source under MIT License.
                </div>
                <div>
                    <a href="https://github.com/deadxfire/srin" target="_blank">deadxfire/srin</a>
                </div>
            </footer>
        </main>
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
        }

        function filterTopics() {
            const query = document.getElementById('docSearch').value.toLowerCase().trim();
            const links = document.querySelectorAll('.sidebar-links li');
            const sections = document.querySelectorAll('.sidebar-section');

            links.forEach(li => {
                const a = li.querySelector('a');
                const text = a.getAttribute('data-topic') || a.innerText.toLowerCase();
                if (text.includes(query)) {
                    li.style.display = '';
                } else {
                    li.style.display = 'none';
                }
            });

            sections.forEach(sec => {
                const visible = sec.querySelectorAll('.sidebar-links li:not([style*="display: none"])');
                sec.style.display = (visible.length > 0) ? '' : 'none';
            });
        }

        // Add copy button to all pre blocks automatically
        document.querySelectorAll('pre').forEach(pre => {
            const btn = document.createElement('button');
            btn.className = 'copy-btn';
            btn.innerText = 'Copy';
            btn.onclick = () => {
                const code = pre.querySelector('code') ? pre.querySelector('code').innerText : pre.innerText;
                navigator.clipboard.writeText(code).then(() => {
                    btn.innerText = 'Copied!';
                    setTimeout(() => { btn.innerText = 'Copy'; }, 2000);
                });
            };
            pre.appendChild(btn);
        });
    </script>
</body>
</html>
