<?php
/**
 * TEI Manuscript Viewer — Main Entry Point
 *
 * A lightweight PHP application that transforms TEI/XML encoded
 * manuscripts into readable, styled HTML pages. Demonstrates the
 * same XML → XSLT → HTML pipeline used in Digital Humanities
 * projects like the Early Ruskin Manuscripts (erm.selu.edu).
 *
 * Routes:
 *   /                    → Catalog listing of all manuscripts
 *   /view/{slug}         → Full rendered view of a manuscript
 *   /api/manuscripts     → JSON list of all manuscripts (API)
 *   /api/manuscripts/{s} → JSON metadata for a single manuscript
 *
 * @author  Nikhil Shah
 * @license MIT
 */

// ─── Bootstrap ───────────────────────────────────────────────
$baseDir = dirname(__DIR__);

require_once $baseDir . '/src/TEITransformer.php';
require_once $baseDir . '/src/ManuscriptCatalog.php';

$xsltPath = $baseDir . '/data/xslt/tei-to-html.xsl';
$xmlDir   = $baseDir . '/data/xml';

$transformer = new TEITransformer($xsltPath);
$catalog     = new ManuscriptCatalog($xmlDir, $transformer);

// ─── Simple Router ───────────────────────────────────────────
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$path       = parse_url($requestUri, PHP_URL_PATH);

if ($path !== '/' && substr($path, -1) === '/') {
    $path = rtrim($path, '/');
}

// Route: JSON API — list all manuscripts
if ($path === '/api/manuscripts') {
    header('Content-Type: application/json; charset=utf-8');
    $data = array_map(function ($entry) {
        return [
            'slug'     => $entry['slug'],
            'metadata' => $entry['metadata'],
            'url'      => '/view/' . $entry['slug'],
        ];
    }, $catalog->getAll());
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// Route: JSON API — single manuscript metadata
if (preg_match('#^/api/manuscripts/([a-z0-9_-]+)$#', $path, $m)) {
    header('Content-Type: application/json; charset=utf-8');
    $entry = $catalog->findBySlug($m[1]);
    if (!$entry) {
        http_response_code(404);
        echo json_encode(['error' => 'Manuscript not found']);
        exit;
    }
    echo json_encode([
        'slug'     => $entry['slug'],
        'metadata' => $entry['metadata'],
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// Route: View a specific manuscript
if (preg_match('#^/view/([a-z0-9_-]+)$#', $path, $m)) {
    $slug  = $m[1];
    $entry = $catalog->findBySlug($slug);

    if (!$entry) {
        http_response_code(404);
        renderPage('404 — Manuscript Not Found', '<div class="error-page"><h2>Manuscript Not Found</h2><p>The requested manuscript could not be located in the archive.</p><a href="/" class="btn">Return to Catalog</a></div>');
        exit;
    }

    try {
        $html  = $transformer->transform($entry['path']);
        $title = $entry['metadata']['title'];
        $nav   = '<div class="viewer-nav"><a href="/" class="btn btn-back">&#8592; Back to Catalog</a></div>';
        renderPage($title . ' — TEI Manuscript Viewer', $nav . $html);
    } catch (Exception $e) {
        http_response_code(500);
        renderPage('Error', '<div class="error-page"><h2>Transformation Error</h2><p>' . htmlspecialchars($e->getMessage()) . '</p><a href="/" class="btn">Return to Catalog</a></div>');
    }
    exit;
}

// Route: Search
if ($path === '/search') {
    $query   = $_GET['q'] ?? '';
    $results = $query ? $catalog->search($query) : $catalog->getAll();
    renderPage('Search — TEI Manuscript Viewer', renderCatalog($results, $query));
    exit;
}

// Route: Home — Catalog listing
renderPage('TEI Manuscript Viewer', renderCatalog($catalog->getAll()));
exit;


// ─── Rendering Functions ─────────────────────────────────────

/**
 * Render the full HTML page with layout.
 */
function renderPage(string $title, string $content): void
{
    $cssPath = '/assets/css/style.css';
    echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title}</title>
    <link rel="stylesheet" href="{$cssPath}">
</head>
<body>
    <header class="site-header">
        <div class="container">
            <a href="/" class="site-title">TEI Manuscript Viewer</a>
            <p class="site-subtitle">A PHP + XML/TEI + XSLT Digital Humanities Demo</p>
        </div>
    </header>
    <main class="container">
        {$content}
    </main>
    <footer class="site-footer">
        <div class="container">
            <p>Built by <strong>Nikhil Shah</strong> &mdash;
            Demonstrating the XML/TEI &rarr; XSLT &rarr; PHP pipeline used in
            Digital Humanities projects.</p>
            <p class="tech-stack">PHP &middot; XML/TEI P5 &middot; XSLT 1.0 &middot; DOMDocument &middot; XSLTProcessor</p>
        </div>
    </footer>
    <script>
    // Highlight named entities on hover
    document.querySelectorAll('.entity').forEach(function(el) {
        el.addEventListener('mouseenter', function() {
            var ref = this.dataset.ref;
            if (ref) {
                document.querySelectorAll('[data-ref="' + ref + '"]').forEach(function(e) {
                    e.classList.add('entity-highlight');
                });
            }
        });
        el.addEventListener('mouseleave', function() {
            document.querySelectorAll('.entity-highlight').forEach(function(e) {
                e.classList.remove('entity-highlight');
            });
        });
    });
    </script>
</body>
</html>
HTML;
}

/**
 * Render the catalog listing / search results.
 */
function renderCatalog(array $manuscripts, string $query = ''): string
{
    $html = '<div class="catalog">';

    $escapedQuery = htmlspecialchars($query);
    $html .= <<<SEARCH
    <div class="search-bar">
        <form action="/search" method="get">
            <input type="text" name="q" value="{$escapedQuery}"
                   placeholder="Search manuscripts by title, author, or keyword..." />
            <button type="submit" class="btn">Search</button>
        </form>
    </div>
SEARCH;

    if (empty($manuscripts)) {
        $html .= '<p class="no-results">No manuscripts found matching your query.</p>';
        $html .= '</div>';
        return $html;
    }

    $html .= '<h2 class="catalog-heading">Manuscript Catalog</h2>';
    $html .= '<div class="catalog-grid">';

    foreach ($manuscripts as $entry) {
        $meta  = $entry['metadata'];
        $slug  = htmlspecialchars($entry['slug']);
        $title = htmlspecialchars($meta['title'] ?: 'Untitled');
        $sub   = htmlspecialchars($meta['subtitle'] ?: '');
        $auth  = htmlspecialchars($meta['author'] ?: 'Unknown');
        $ms    = htmlspecialchars($meta['manuscript'] ?: '—');
        $date  = htmlspecialchars($meta['date'] ?: '—');
        $desc  = htmlspecialchars($meta['description'] ?: '');

        $html .= <<<CARD
        <div class="catalog-card">
            <h3 class="card-title"><a href="/view/{$slug}">{$title}</a></h3>
            <p class="card-subtitle">{$sub}</p>
            <div class="card-meta">
                <span class="meta-tag">Author: {$auth}</span>
                <span class="meta-tag">MS: {$ms}</span>
                <span class="meta-tag">Date: {$date}</span>
            </div>
            <p class="card-desc">{$desc}</p>
            <a href="/view/{$slug}" class="btn btn-view">View Manuscript</a>
        </div>
CARD;
    }

    $html .= '</div></div>';
    return $html;
}
