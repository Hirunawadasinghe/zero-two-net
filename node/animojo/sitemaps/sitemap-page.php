<?php
header('Content-Type: application/xml; charset=utf-8');

$urls = [
    ['path' => '', 'changefreq' => 'daily', 'priority' => '1.0'],
    ['path' => 'home', 'changefreq' => 'daily', 'priority' => '1.0'],
    ['path' => 'az-list', 'changefreq' => 'daily', 'priority' => '0.9'],
    ['path' => 'search', 'changefreq' => 'daily', 'priority' => '0.9'],
    ['path' => 'ongoing-anime', 'changefreq' => 'daily', 'priority' => '0.9'],
    ['path' => 'sinhala-subtitles', 'changefreq' => 'daily', 'priority' => '0.9'],
    ['path' => 'genre', 'changefreq' => 'daily', 'priority' => '0.9'],
    ['path' => 'arcbot', 'changefreq' => 'monthly', 'priority' => '0.9'],
    ['path' => 'request', 'changefreq' => 'monthly', 'priority' => '0.9'],
    ['path' => 'contact', 'changefreq' => 'monthly', 'priority' => '0.9']
];

echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:news="http://www.google.com/schemas/sitemap-news/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" xmlns:video="http://www.google.com/schemas/sitemap-video/1.1">' . PHP_EOL;
foreach ($urls as $url) {
    echo trim('
    <url>
        <loc>https://' . $_SERVER['HTTP_HOST'] . '/' . $url['path'] . '</loc>
        <changefreq>' . $url['changefreq'] . '</changefreq>
        <priority>' . $url['priority'] . '</priority>
    </url>') . PHP_EOL;
}
echo '</urlset>';