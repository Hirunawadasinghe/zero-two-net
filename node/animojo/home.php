<!DOCTYPE html>
<html lang="en">

<head>
    <?php
    include 'layout/meta/def.php';
    ?>
    <style>
        <?php echo minify('css/home.css') ?>
    </style>
    <!-- Share this -->
    <script type='text/javascript'
        src='https://platform-api.sharethis.com/js/sharethis.js#property=6765674fa0922d001f32805a&product=inline-share-buttons'
        async='async'></script>
</head>

<body>
    <?php include "layout/header/def.php"; ?>
    <div class="main-fave-slide-c">
        <div class="fave-slides-c-w">
            <div id="fave-slides-c">
                <?php
                include '_inc/function.php';
                $slide_data = get_db_net_cache('database', 'featured');

                $slides_found = 0;
                foreach ($slide_data as $e) {
                    $n = find_movie($e['id']);
                    if ($n) {
                        $slides_found++;

                        switch (strtolower($n['language'])) {
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
                                $lan = $n['language'] . ' Dub';
                                break;
                        }

                        echo '<div class="fave-slide-w"><div class="fave-slide-details-c"><h3>#' . $slides_found . ' Spotlight</h3><h1>' . $n['name'] . '</h1><div class="fave-slide-properties-c"><span><i class="fa-solid fa-circle-play"></i>' . $n['type'] . '</span><span><i class="fa-solid fa-language"></i>' . $lan . '</span><span><i class="fa-solid fa-calendar"></i>' . $n['release_year'] . '</span></div><p>' . $n['description'] . '</p><div class="btn-w"><a href="/watch/' . format_title($n) . '" class="w"><i class="fa-solid fa-play"></i>Watch now</a><a href="/anime/' . format_title($n) . '">Details<i class="fa-solid fa-angle-right"></i></a></div></div><div class="fave-slide-img-w"><div class="fave-slide-imgs-c"><img src="' . $e['img'] . '" alt="' . $n['name'] . '" loading="lazy"></div></div></div>';
                    }
                }
                ?>
            </div>
        </div>
        <div class="fave-slide-nav-c">
            <?php
            for ($i = 0; $i < $slides_found; $i++)
                echo '<span class="fave-slide-nav-c-i" onclick="fave_slide_push(' . $i . ')"></span>';
            ?>
        </div>
    </div>

    <div class="content-wraper">
        <section id="cw-section" style="display:none">
            <div class="section-title">
                <h2>Continue Watching</h2>
                <a href="my-lists?p=history"><span>View History</span><i class="fas fa-angle-right"></i></a>
            </div>
            <div class="hc-w" id="cw-section-w"></div>
        </section>
    </div>

    <section class="content-wraper sharethis-inline-share-buttons-c">
        <div class="sharethis-inline-d">
            <div>
                <img src="https://i.postimg.cc/mgrgZ4nB/zero-two-smile.gif" alt="zero two smiling" loading="lazy">
            </div>
            <div>
                <div>
                    <p>Share <b><?= $site_name ?></b></p>
                    <span>to your friends</span>
                </div>
            </div>
        </div>
        <div class="sharethis-inline-share-buttons"></div>
    </section>

    <?php
    $filter = array_values(array_filter($main_data, function ($e) {
        global $advance_settings;
        return isset($e['trailer']) && strlen($e['description']) > 150 && empty(array_intersect($advance_settings['tags']['visibility_reduced'], $e['tags']));
    }));
    $r_itm = $filter[array_rand($filter)];
    ?>
    <section class="ran-preview-w">
        <div class="ran-bg">
            <div style="background-image:url('https://img.youtube.com/vi/<?= $r_itm['trailer'] ?>/mqdefault.jpg')">
            </div>
        </div>
        <div class="ran-preview">
            <div class="ran-details">
                <a href="/anime/<?= format_title($r_itm) ?>" class="ht"><?= $r_itm['name'] ?></a>
                <p><?= nl2br($r_itm['description']) ?></p>
                <div class="ran-btn-c">
                    <a href="/watch/<?= format_title($r_itm) ?>" class="b1"><i class="fa-solid fa-play"></i>Watch
                        Now</a>
                    <a href="/anime/<?= format_title($r_itm) ?>" class="b2">Details<i
                            class="fa-solid fa-angle-right"></i></a>
                </div>
            </div>
            <div class="ran-vid">
                <iframe src="https://www.youtube.com/embed/<?= $r_itm['trailer'] ?>?showinfo=0&rel=0" frameborder="0"
                    title="<?= $r_itm['name'] ?> - Trailer" allow="accelerometer; autoplay; encrypted-media; gyroscope;"
                    allowfullscreen=""></iframe>
            </div>
        </div>
    </section>

    <div class="content-wraper">
        <div class="content">
            <div>
                <div id="tab-section" class="m-panel">
                    <section>
                        <div class="section-title">
                            <h2>Ongoing Anime</h2>
                            <a href="ongoing-anime"><span>View More</span><i class="fas fa-angle-right"></i></a>
                        </div>
                        <div class="movie-cards-container">
                            <?php
                            $d = filter_duplicates($main_data);
                            $d = array_filter($d, function ($e) {
                                $ep = get_sources($e['movie_id']);
                                if (!$ep)
                                    return;
                                if ($e['episodes'])
                                    return count($ep) < $e['episodes'];
                                return true;
                            });
                            usort($d, function ($a, $b) {
                                $dateA = DateTime::createFromFormat('M d, Y', $a['release_year']);
                                $dateB = DateTime::createFromFormat('M d, Y', $b['release_year']);
                                return $dateB <=> $dateA;
                            });
                            $d = array_slice($d, 0, 10);
                            foreach ($d as $e) {
                                load_card($e);
                            }
                            ?>
                        </div>
                    </section>

                    <?php
                    if ($preff_lang === 'si') {
                        load_subtitle_data();
                        if (count($subtitle_data['subtitles']) > 0) {
                            $d = $subtitle_data['subtitles'];

                            echo '<section><div class="section-title"><h2>Anime with Sinhala Subtitles</h2><a href="sinhala-subtitles"><span>View More</span><i class="fas fa-angle-right"></i></a></div><div class="movie-cards-container">';

                            $c = 0;
                            for ($i = count($d) - 1; $c < 5 && $i > 0; $i--) {
                                $si = false;
                                foreach ($d[$i]['sub'] as $e) {
                                    if ($e['lan'] === 'si') {
                                        $si = true;
                                        break;
                                    }
                                }
                                if ($si) {
                                    load_card(find_movie($d[$i]['id'][0]));
                                    $c++;
                                }
                            }

                            echo '</div></section>';
                        }
                    }
                    ?>
                </div>

                <section class="schedule">
                    <div class="h">
                        <div class="section-title">
                            <h2>Estimated Schedule</h2>
                        </div>
                        <div class="c">
                            <span id="sch-timezone"></span>
                            <span id="sch-date"></span>
                            <span id="sch-clock"></span>
                        </div>
                    </div>
                    <div class="top-w">
                        <div class="top-b-w">
                            <div id="sch-top-h-w">
                                <?php
                                for ($day = 1; $day <= date('t'); $day++) {
                                    $t_stamp = mktime(0, 0, 0, date('m'), $day, date('Y'));
                                    echo '
                                    <div class="sch-h-d" data-date="' . str_split(date('j', $t_stamp), 10)[0] . '">
                                        <span>' . date('D', $t_stamp) . '</span>
                                        <div>' . str_split(date('F Y'), 3)[0] . ' ' . $day . '</div>
                                    </div>';
                                } ?>
                            </div>
                        </div>
                        <button class="s-btn l" id="sch-s-l" aria-label="schedule scroll left"><i
                                class="fas fa-angle-left"></i></button>
                        <button class="s-btn r" id="sch-s-r" aria-label="schedule scroll right"><i
                                class="fas fa-angle-right"></i></button>
                    </div>
                    <ul id="sch-list"></ul>
                    <p id="sch-sh-txt"></p>
                    <p id="sch-err">No episode scheduled</p>
                </section>
            </div>
            <div class="r-panel">
                <?php
                include 'layout/sidebar/genres.php';
                include 'layout/sidebar/top.php' ?>
            </div>
        </div>
    </div>
    <script><?php echo minify('js/home.js') ?></script>
    <?php include "layout/footer/def.php" ?>
</body>

</html>