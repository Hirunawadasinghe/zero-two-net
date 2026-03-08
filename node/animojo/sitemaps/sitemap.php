<?php
header('Content-Type: application/xml; charset=utf-8');

$urls = [
    'sitemaps/sitemap-page.php',
    'sitemaps/sitemap-genre.php',
    'sitemaps/sitemap-type.php'
];

echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;
foreach ($urls as $p) {
    echo trim('
    <sitemap>
        <loc>https://' . $_SERVER['HTTP_HOST'] . '/' . $p . '</loc>
    </sitemap>') . PHP_EOL;
}
echo '</sitemapindex>';