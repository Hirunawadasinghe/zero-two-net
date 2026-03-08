<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="robots" content="index,follow">
<meta http-equiv="content-language" content="en">
<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/_inc/_config.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/_inc/minify.php';
$page_title = $site_name;
function print_meta_data($d)
{
    global $site_name, $site_url, $meta_keywords, $page_title;
    $page_title = $d['title'] ?? "$site_name - Watch Anime online with English SUB and DUB Free";
    $page_description = $d['description'] ?? "$site_name is a Free anime streaming website which you can watch English Subbed and Dubbed Anime online with No Account and Daily update. WATCH NOW!";
    $page_banner = $d['banner'] ?? "$site_url/images/share-banner.jpg";
    $page_banner_w = $d['banner_w'] ?? '650';
    $page_banner_h = $d['banner_h'] ?? '350';
    $page_keywords = $d['keywords'] ?? $meta_keywords;
    $page_url = $d['url'] ?? "$site_url/home";
    $page_canonical = $d['canonical'] ?? $site_url . strtok($_SERVER['REQUEST_URI'], '?');
    echo trim('
    <title>' . $page_title . '</title>
    <meta name="description" content="' . $page_description . '">
    <meta name="keywords" content="' . implode(', ', $page_keywords) . '">
    <meta name="author" content="' . $site_name . ' Team">
    ' . ($page_canonical ? '<link rel="canonical" href="' . $page_canonical . '">' : '') . '
    <meta property="og:locale" content="en_US">
    <meta property="og:type" content="website">
    <meta property="og:url" content="' . $page_url . '">
    <meta property="og:title" content="' . $page_title . '">
    <meta property="og:image" content="' . $page_banner . '">
    <meta property="og:image:width" content="' . $page_banner_w . '">
    <meta property="og:image:height" content="' . $page_banner_h . '">
    <meta property="og:description" content="' . $page_description . '">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="' . $page_title . '">
    <meta name="twitter:description" content="' . $page_description . '">
    <meta name="twitter:image" content="' . $page_banner . '">');
}
print_meta_data(isset($meta_preset) ? $meta_preset : []); ?>
<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=5">
<link rel="icon" type="image/png" sizes="32x32" href="<?= $site_url ?>/images/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="<?= $site_url ?>/images/favicon-16x16.png">
<link rel="icon" sizes="192x192" href="<?= $site_url ?>/images/android-chrome-192x192.png">
<link rel="icon" sizes="512x512" href="<?= $site_url ?>/images/android-chrome-512x512.png">
<link rel="apple-touch-icon" sizes="180x180" href="<?= $site_url ?>/images/apple-touch-icon.png">
<link rel="shortcut icon" href="<?= $site_url ?>/images/favicon.png?v=<?= $version ?>" type="image/x-icon">
<link rel="mask-icon" href="<?= $site_url ?>/images/safari-pinned-tab.svg" color="#5bbad5">
<meta name="msapplication-TileColor" content="#da532c">
<meta name="apple-mobile-web-app-status-bar" content="#201F32">
<meta name="theme-color" content="#201F32">
<link rel="manifest" href="<?= $site_url ?>/manifest.json?v=<?= $version ?>">

<?php
echo '<script type="application/ld+json">' . json_encode([
    "@context" => "https://schema.org",
    "@graph" => [
        [
            "@type" => "Person",
            "@id" => "$site_url#person",
            "name" => "$site_name Team"
        ],
        [
            "@type" => "WebSite",
            "@id" => "$site_url#website",
            "url" => $site_url,
            "name" => $site_name,
            "alternateName" => $site_alt_name,
            "publisher" => ["@id" => "$site_url#person"],
            "inLanguage" => "en-US",
            "potentialAction" => [
                "@type" => "SearchAction",
                "target" => "$site_url/search?text={search_term_string}",
                "query-input" => "required name=search_term_string"
            ]
        ],
        [
            "@type" => "WebPage",
            "@id" => "$site_url#webpage",
            "url" => $site_url,
            "name" => $page_title,
            "about" => ["@id" => "$site_url#person"],
            "isPartOf" => ["@id" => "$site_url#website"],
            "inLanguage" => "en-US"
        ]
    ]
], JSON_UNESCAPED_UNICODE) . '</script>';
?>

<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-YVBV56JW1F"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-YVBV56JW1F');
</script>

<!-- Bing tag -->
<script>
    (function(c,l,a,r,i,t,y){
        c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
        t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i+"?ref=bwt";
        y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
    })(window, document, "clarity", "script", "tdjwkwdd4k");
</script>

<link rel="stylesheet" href="https://use.fontawesome.com/releases/v7.0.0/css/all.css">
<style>
    <?php echo minify($_SERVER['DOCUMENT_ROOT'] . '/css/style.css') ?>
    <?php echo minify($_SERVER['DOCUMENT_ROOT'] . '/css/qtip.css') ?>
</style>

<?php
$update_db = '';
foreach ($db_net_api_endpoints as $e) {
    $cp = $_SERVER['DOCUMENT_ROOT'] . '/cache/' . $e['path'] . '/' . $e['file'];
    if (!file_exists($cp) || filemtime($cp) + $e['time'] <= time()) {
        $update_db = 'fetch("/api/db_sw.php");';
        break;
    }
} ?>
<script><?= $update_db ?>function imgError(i) { i.onerror = null; i.src = '/images/assets/no_poster.png'; }</script>

<noscript>You need to enable JavaScript to run this app.</noscript>
