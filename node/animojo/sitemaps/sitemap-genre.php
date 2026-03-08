<?php
header('Content-Type: application/xml; charset=utf-8');
include $_SERVER['DOCUMENT_ROOT'] . '/_inc/function.php';

$tags = [];
foreach ($main_data as $e) {
    foreach ($e['tags'] as $t) {
        if (!in_array($t, $tags)) {
            $tags[] = $t;
        }
    }
}

echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:news="http://www.google.com/schemas/sitemap-news/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" xmlns:video="http://www.google.com/schemas/sitemap-video/1.1">' . PHP_EOL;
foreach ($tags as $t) {
    echo trim('
    <url>
        <loc>https://' . $_SERVER['HTTP_HOST'] . '/genre/' . format_url_str($t) . '</loc>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>') . PHP_EOL;
}
echo '</urlset>';