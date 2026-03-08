<?php
$ep_views_db = $database_path . '/action/og_ep_views';
if (!file_exists($ep_views_db))
    return;
$d = include $ep_views_db;
?>
<style>
    <?php echo minify('css/sidebar-top.css') ?>
</style>
<section>
    <div class="section-title">
        <h2>Top 10</h2>
    </div>
    <div class="rp-boxed">
        <ul class="top-sect">
            <?php
            include_once $_SERVER['DOCUMENT_ROOT'] . '/_inc/function.php';
            function top_get_view_count($in)
            {
                $max = 0;
                foreach ($in as $ep => $count) {
                    if ($count > $max) {
                        $max = $count;
                    }
                }
                return $max;
            }
            usort($d, function ($a, $b) {
                return top_get_view_count($b['ep']) <=> top_get_view_count($a['ep']);
            });
            for ($i = 0; $i < 10 && $i < count($d); $i++) {
                $f = find_movie($d[$i]['id']);
                if (!$f)
                    continue;
                $s = get_sources($f['movie_id']);
                switch (strtolower($f['language'])) {
                    case 'japanese':
                        $lan = 'SUB';
                        break;
                    case 'english':
                        $lan = 'DUB';
                        break;
                    case 'dual audio':
                        $lan = 'Dual Audio';
                        break;
                    default:
                        $lan = $f['language'] . ' Dub';
                        break;
                }
                echo '
                <li' . ($i < 9 && $i < count($d) - 1 ? ' class="ul"' : '') . '>
                    <div class="no' . ($i < 3 ? ' h' : '') . '"><span>' . sprintf("%02d", ($i + 1)) . '</span></div>
                    <div class="img-c qtip-tag" data-id="' . $f['movie_id'] . '"><img alt="' . $f['name'] . '" src="' . ($f['images'] ? $f['images']['webp']['image_url'] : $f['thumbnail_image']) . '" loading="lazy" onerror="imgError(this)"></div>
                    <div class="info">
                        <h3 class="title"><a href="/anime/' . format_title($f) . '" title="' . $f['name'] . '">' . $f['name'] . '</a></h3>
                        <div class="tick"><span class="ep">Ep ' . ($s ? array_key_last($s) : '...') . '</span><span class="lan">' . $lan . '</span></div>
                    </div>
                </li>';
            } ?>
        </ul>
    </div>
</section>