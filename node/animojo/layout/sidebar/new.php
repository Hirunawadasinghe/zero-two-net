<style>
    <?php echo minify('css/sidebar-new.css') ?>
</style>
<section>
    <div class="section-title">
        <h2>New on <?= $site_name ?></h2>
    </div>
    <div class="rp-boxed">
        <ul class="top-sect">
            <?php
            include_once $_SERVER['DOCUMENT_ROOT'] . '/_inc/function.php';
            $d = filter_duplicates($main_data);
            $d = array_reverse(array_slice($d, count($d) - 10, count($d)));

            for ($i = 0; $i < count($d); $i++) {
                $s = get_sources($d[$i]['movie_id']);
                switch (strtolower($d[$i]['language'])) {
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
                        $lan = $d[$i]['language'] . ' Dub';
                        break;
                }
                echo '
                <li' . ($i < 9 && $i < count($d) - 1 ? ' class="ul"' : '') . '>
                    <div class="img-c qtip-tag" data-id="' . $d[$i]['movie_id'] . '"><img alt="' . $d[$i]['name'] . '" src="' . ($d[$i]['images'] ? $d[$i]['images']['webp']['image_url'] : $d[$i]['thumbnail_image']) . '" loading="lazy" onerror="imgError(this)"></div>
                    <div class="info">
                        <h3 class="title"><a href="/anime/' . format_title($d[$i]) . '" title="' . $d[$i]['name'] . '">' . $d[$i]['name'] . '</a></h3>
                        <div class="inf"><div class="tick"><span class="ep">Ep ' . ($s ? array_key_last($s) : '...') . '</span><span class="lan">' . $lan . '</span></div><i></i>' . $d[$i]['type'] . '</div>
                    </div>
                </li>';
            } ?>
        </ul>
    </div>
</section>