<?php
$domainSuffix = getenv('DOMAIN_SUFFIX') ?: 'local';
$mysqlPort = getenv('MYSQL_PORT') ?: '3307';
$mariadbPort = getenv('MARIADB_PORT') ?: '3308';
$postgresPort = getenv('POSTGRES_PORT') ?: '5433';
$redisPort = getenv('REDIS_PORT') ?: '6379';
$pmaPort = getenv('PHPMYADMIN_PORT') ?: '8081';

// Scan projects
$projects = [];
$htdocs = '/var/www/html';
if (is_dir($htdocs)) {
    $items = scandir($htdocs);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..' || str_starts_with($item, '.')) continue;
        $path = $htdocs . '/' . $item;
        if (is_dir($path)) {
            $isLaravel = is_dir($path . '/public') || file_exists($path . '/artisan');
            $projects[] = [
                'name' => $item,
                'domain' => $item . '.' . $domainSuffix,
                'type' => $isLaravel ? 'Laravel' : 'Standard PHP',
                'doc_root' => $isLaravel ? 'public/' : './',
                'php_version' => '8.5',
                'updated' => date('Y-m-d H:i', filemtime($path))
            ];
        }
    }
}

// Database connectivity health check helper
function checkService($host, $port, $timeout = 1) {
    $fp = @fsockopen($host, $port, $errno, $errstr, $timeout);
    if ($fp) {
        fclose($fp);
        return true;
    }
    return false;
}

$services = [
    ['name' => 'Apache Server', 'internal_host' => '127.0.0.1', 'internal_port' => 80, 'info' => 'HTTP Server on Port 80'],
    ['name' => 'PHP 8.5+ FastCGI', 'internal_host' => 'php', 'internal_port' => 9000, 'info' => 'v' . PHP_VERSION . ' (Latest)'],
    ['name' => 'MySQL 8.x', 'internal_host' => 'mysql', 'internal_port' => 3306, 'info' => 'Port ' . $mysqlPort],
    ['name' => 'MariaDB 11.x', 'internal_host' => 'mariadb', 'internal_port' => 3306, 'info' => 'Port ' . $mariadbPort],
    ['name' => 'PostgreSQL 16+', 'internal_host' => 'postgresql', 'internal_port' => 5432, 'info' => 'Port ' . $postgresPort],
    ['name' => 'Redis Cache', 'internal_host' => 'redis', 'internal_port' => 6379, 'info' => 'Port ' . $redisPort],
];

foreach ($services as &$srv) {
    $srv['status'] = checkService($srv['internal_host'], $srv['internal_port'], 0.5);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LocalDevServer</title>
    <style>
        :root {
            --bg: #ffffff;
            --surface: #ffffff;
            --surface-subtle: #f8fafc;
            --border: #e2e8f0;
            --border-strong: #000000;
            --text-main: #000000;
            --text-muted: #52525b;
            --text-subtle: #a1a1aa;
            --btn-bg: #000000;
            --btn-text: #ffffff;
            --btn-hover: #27272a;
            --btn-outline-bg: #ffffff;
            --btn-outline-border: #e2e8f0;
            --btn-outline-hover: #f4f4f5;
            --badge-bg: #000000;
            --badge-text: #ffffff;
            --code-bg: #f8fafc;
            --code-border: #e2e8f0;
            --card-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.04);
            --dot-active: #10b981;
            --dot-inactive: #ef4444;
        }

        [data-theme="dark"] {
            --bg: #000000;
            --surface: #0a0a0a;
            --surface-subtle: #121214;
            --border: #27272a;
            --border-strong: #ffffff;
            --text-main: #ffffff;
            --text-muted: #a1a1aa;
            --text-subtle: #71717a;
            --btn-bg: #ffffff;
            --btn-text: #000000;
            --btn-hover: #e4e4e7;
            --btn-outline-bg: #0a0a0a;
            --btn-outline-border: #27272a;
            --btn-outline-hover: #18181b;
            --badge-bg: #ffffff;
            --badge-text: #000000;
            --code-bg: #121214;
            --code-border: #27272a;
            --card-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.5);
            --dot-active: #34d399;
            --dot-inactive: #f87171;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, sans-serif; transition: background-color 0.15s ease, color 0.15s ease, border-color 0.15s ease; }
        body { background-color: var(--bg); color: var(--text-main); min-height: 100vh; padding: 2.5rem 1.5rem; }
        .container { max-width: 1160px; margin: 0 auto; }
        
        header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2.5rem; border-bottom: 1px solid var(--border); padding-bottom: 1.75rem; }
        .logo-area { display: flex; align-items: center; gap: 1rem; }
        .logo-icon { width: 42px; height: 42px; background: var(--text-main); color: var(--bg); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.25rem; }
        h1 { font-size: 1.6rem; font-weight: 700; letter-spacing: -0.02em; }
        p.subtitle { color: var(--text-muted); font-size: 0.9rem; margin-top: 0.2rem; }
        
        .header-actions { display: flex; align-items: center; gap: 0.75rem; }
        .btn { display: inline-flex; align-items: center; gap: 0.45rem; padding: 0.55rem 1.1rem; background: var(--btn-bg); color: var(--btn-text); border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 0.85rem; border: 1px solid transparent; cursor: pointer; }
        .btn:hover { background: var(--btn-hover); }
        .btn-outline { background: var(--btn-outline-bg); border: 1px solid var(--btn-outline-border); color: var(--text-main); }
        .btn-outline:hover { background: var(--btn-outline-hover); border-color: var(--border-strong); }
        .btn-theme { padding: 0.55rem 0.8rem; font-size: 0.95rem; }

        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; margin-top: 2rem; }
        .section-title { font-size: 1.15rem; font-weight: 700; letter-spacing: -0.01em; }
        .section-subtitle { font-size: 0.85rem; color: var(--text-muted); }

        .grid-services { display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 0.85rem; }
        .service-card { background: var(--surface); border: 1px solid var(--border); border-radius: 8px; padding: 1rem 1.25rem; display: flex; justify-content: space-between; align-items: center; box-shadow: var(--card-shadow); }
        .service-name { font-weight: 600; font-size: 0.9rem; }
        .service-info { font-size: 0.78rem; color: var(--text-muted); margin-top: 0.15rem; }
        .status-pill { display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.72rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; padding: 0.2rem 0.55rem; border-radius: 999px; border: 1px solid var(--border); background: var(--surface-subtle); color: var(--text-main); }
        .status-dot { width: 7px; height: 7px; border-radius: 50%; display: inline-block; }
        .dot-online { background-color: var(--dot-active); }
        .dot-offline { background-color: var(--dot-inactive); }

        .card { background: var(--surface); border: 1px solid var(--border); border-radius: 8px; overflow: hidden; box-shadow: var(--card-shadow); }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { background: var(--surface-subtle); padding: 0.8rem 1.2rem; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.05em; font-weight: 600; border-bottom: 1px solid var(--border); }
        td { padding: 0.95rem 1.2rem; border-bottom: 1px solid var(--border); font-size: 0.88rem; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: var(--surface-subtle); }
        
        .project-title { font-weight: 600; font-size: 0.92rem; color: var(--text-main); }
        .domain-link { color: var(--text-main); text-decoration: underline; text-underline-offset: 3px; font-weight: 600; }
        .domain-link:hover { opacity: 0.7; }
        .links-sub { margin-top: 0.35rem; display: flex; flex-direction: column; gap: 0.2rem; }
        .links-sub a { font-size: 0.78rem; color: var(--text-muted); text-decoration: none; }
        .links-sub a:hover { text-decoration: underline; color: var(--text-main); }
        .links-sub code { font-family: monospace; font-size: 0.75rem; background: var(--surface-subtle); padding: 0.1rem 0.3rem; border-radius: 3px; border: 1px solid var(--border); }

        .tag { display: inline-block; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.72rem; font-weight: 600; }
        .tag-black { background: var(--badge-bg); color: var(--badge-text); }
        .tag-outline { border: 1px solid var(--border); color: var(--text-muted); background: var(--surface-subtle); }

        .dbeaver-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1rem; }
        .dbeaver-card { background: var(--surface); border: 1px solid var(--border); border-radius: 8px; padding: 1.25rem; box-shadow: var(--card-shadow); }
        .dbeaver-card h3 { font-size: 0.95rem; font-weight: 700; margin-bottom: 0.75rem; color: var(--text-main); display: flex; align-items: center; gap: 0.4rem; }
        .code-box { background: var(--code-bg); border: 1px solid var(--code-border); border-radius: 6px; padding: 0.85rem; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 0.82rem; color: var(--text-main); line-height: 1.6; }
        
        .empty-state { text-align: center; padding: 3rem 1rem; color: var(--text-muted); }
        .empty-state code { background: var(--surface-subtle); padding: 0.2rem 0.4rem; border-radius: 4px; color: var(--text-main); border: 1px solid var(--border); }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div class="logo-area">
                <div class="logo-icon">&#9632;</div>
                <div>
                    <h1>LocalDevServer</h1>
                    <p class="subtitle">Minimalist Local Development Environment for macOS &bull; PHP 8.5+</p>
                </div>
            </div>
            <div class="header-actions">
                <button id="themeToggle" class="btn btn-outline btn-theme" title="Toggle White/Black Theme" aria-label="Toggle Theme">
                    &#9728;
                </button>
                <a href="/htdocs/" class="btn btn-outline">
                    Browse /htdocs/
                </a>
                <a href="http://localhost:<?= htmlspecialchars($pmaPort) ?>" target="_blank" class="btn">
                    phpMyAdmin &nearr;
                </a>
            </div>
        </header>

        <div class="section-header">
            <h2 class="section-title">System &amp; Service Status</h2>
        </div>
        <div class="grid-services">
            <?php foreach ($services as $srv): ?>
                <div class="service-card">
                    <div>
                        <div class="service-name"><?= htmlspecialchars($srv['name']) ?></div>
                        <div class="service-info"><?= htmlspecialchars($srv['info']) ?></div>
                    </div>
                    <div>
                        <?php if ($srv['status']): ?>
                            <span class="status-pill"><span class="status-dot dot-online"></span>Online</span>
                        <?php else: ?>
                            <span class="status-pill"><span class="status-dot dot-offline"></span>Offline</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="section-header">
            <h2 class="section-title">Detected Projects (<?= count($projects) ?>)</h2>
            <div class="section-subtitle">
                Directory: <code>./htdocs</code> &bull; Zero-Config Routing
            </div>
        </div>
        <div class="card">
            <?php if (empty($projects)): ?>
                <div class="empty-state">
                    <p>No projects found in <code>htdocs/</code>.</p>
                    <p style="margin-top: 0.5rem; font-size: 0.85rem;">Create a project using <code>./localdev create php myapp</code> or <code>./localdev create laravel myapp</code></p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Project</th>
                            <th>URLs &amp; Access Paths</th>
                            <th>Framework / Type</th>
                            <th>PHP</th>
                            <th>DocumentRoot</th>
                            <th>Last Modified</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($projects as $proj): ?>
                            <tr>
                                <td><span class="project-title"><?= htmlspecialchars($proj['name']) ?></span></td>
                                <td>
                                    <a class="domain-link" href="http://<?= htmlspecialchars($proj['domain']) ?>" target="_blank">
                                        http://<?= htmlspecialchars($proj['domain']) ?> &nearr;
                                    </a>
                                    <div class="links-sub">
                                        <a href="http://<?= htmlspecialchars($proj['name']) ?>.localhost" target="_blank">
                                            &bull; VirtualHost: <code>http://<?= htmlspecialchars($proj['name']) ?>.localhost</code>
                                        </a>
                                        <a href="/htdocs/<?= htmlspecialchars($proj['name']) ?>/" target="_blank">
                                            &bull; XAMPP Path: <code>/htdocs/<?= htmlspecialchars($proj['name']) ?>/</code>
                                        </a>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($proj['type'] === 'Laravel'): ?>
                                        <span class="tag tag-black">Laravel</span>
                                    <?php else: ?>
                                        <span class="tag tag-outline">Standard PHP</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="tag tag-outline">PHP <?= htmlspecialchars($proj['php_version']) ?></span></td>
                                <td><code style="font-family: monospace; font-size: 0.8rem;"><?= htmlspecialchars($proj['doc_root']) ?></code></td>
                                <td style="color: var(--text-muted); font-size: 0.82rem;"><?= htmlspecialchars($proj['updated']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="section-header">
            <h2 class="section-title">Database Connections (DBeaver)</h2>
        </div>
        <div class="dbeaver-grid">
            <div class="dbeaver-card">
                <h3>MySQL 8.x</h3>
                <div class="code-box">
                    Host: 127.0.0.1<br>
                    Port: <?= htmlspecialchars($mysqlPort) ?><br>
                    Database: localdev_mysql<br>
                    User: devuser (or root)<br>
                    Pass: devpass (or rootpass)
                </div>
            </div>
            <div class="dbeaver-card">
                <h3>MariaDB 11.x</h3>
                <div class="code-box">
                    Host: 127.0.0.1<br>
                    Port: <?= htmlspecialchars($mariadbPort) ?><br>
                    Database: localdev_mariadb<br>
                    User: devuser (or root)<br>
                    Pass: devpass (or rootpass)
                </div>
            </div>
            <div class="dbeaver-card">
                <h3>PostgreSQL 16+</h3>
                <div class="code-box">
                    Host: 127.0.0.1<br>
                    Port: <?= htmlspecialchars($postgresPort) ?><br>
                    Database: localdev_postgres<br>
                    User: devuser<br>
                    Pass: devpass
                </div>
            </div>
        </div>
    </div>

    <script>
        // White / Black Theme Toggle with localStorage persistence
        const toggleBtn = document.getElementById('themeToggle');
        const currentTheme = localStorage.getItem('localdev_theme') || 'light';
        
        if (currentTheme === 'dark') {
            document.documentElement.setAttribute('data-theme', 'dark');
            toggleBtn.innerHTML = '&#9790;';
        } else {
            document.documentElement.removeAttribute('data-theme');
            toggleBtn.innerHTML = '&#9728;';
        }

        toggleBtn.addEventListener('click', () => {
            const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
            if (isDark) {
                document.documentElement.removeAttribute('data-theme');
                localStorage.setItem('localdev_theme', 'light');
                toggleBtn.innerHTML = '&#9728;';
            } else {
                document.documentElement.setAttribute('data-theme', 'dark');
                localStorage.setItem('localdev_theme', 'dark');
                toggleBtn.innerHTML = '&#9790;';
            }
        });
    </script>
</body>
</html>
