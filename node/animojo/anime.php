<?php
include '_inc/function.php';

$title_id = GET_URL(1);
$element = find_movie(get_id_by_name($title_id));

if (!$element)
    require 'error.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php
    $anime_title = $element['language'] == 'Japanese' ? $element['alt_name'] : $element['name'];
    $page_title = "$anime_title English Sub/Dub online Free on $site_name";
    $page_description = "Best site to watch $anime_title English Sub/Dub online Free and download $anime_title English Sub/Dub anime.";

    $meta_preset = [
        'title' => $page_title,
        'description' => $page_description,
        'keywords' => [
            "$anime_title English Sub/Dub",
            "free $anime_title online",
            "watch $anime_title online",
            "watch $anime_title free",
            "download $anime_title anime",
            "download $anime_title free",
            "$anime_title sinhala subtitles"
        ],
        'url' => 'https://' . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"],
        'banner' => $element["thumbnail_image"]
    ];

    include 'layout/meta/def.php';

    $breadcrumbSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => 'Home',
                'item' => 'https://' . $_SERVER['HTTP_HOST'] . '/home'
            ],
            [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => $element['type'],
                'item' => 'https://' . $_SERVER['HTTP_HOST'] . '/type/' . format_url_str($element['type'])
            ],
            [
                '@type' => 'ListItem',
                'position' => 3,
                'name' => $page_title,
                'item' => 'https://' . $_SERVER['HTTP_HOST'] . '/anime/' . $title_id
            ]
        ]
    ];

    echo '<script type="application/ld+json">' . json_encode($breadcrumbSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
    ?>
    <style>
        <?php echo minify('css/anime.css') ?>
    </style>
</head>

<body>
    <?php
    include "layout/header/def.php";

    switch (strtolower($element['language'])) {
        case 'japanese':
            $anime_lang_code = 'SUB';
            break;
        case 'english':
            $anime_lang_code = 'DUB';
            break;
        case 'dual audio':
            $anime_lang_code = 'Dual Audio';
            break;
        default:
            $anime_lang_code = $element['language'] . ' Dub';
            break;
    }

    $anime_sources = get_sources($element['movie_id']);

    echo '<script id="srcd">const anime_details=' . json_encode([
        'id' => $element['movie_id']
    ], JSON_UNESCAPED_UNICODE) . ';document.getElementById("srcd").remove();</script>';
    ?>

    <div class="content-wraper content ani-details">
        <div class="ani-cover" style="background-image:url('<?= $element['thumbnail_image'] ?>')"></div>
        <div class="lp">
            <div class="thumb">
                <img src="<?= $element['thumbnail_image'] ?>" alt="<?= $anime_title ?>">
                <?= array_intersect(['Ecchi', 'Erotica', 'Hentai'], $element['tags']) ? '<div class="movie-card-top-row"><span class="thumbnail-info tl">18+</span></div>' : '' ?>
            </div>

            <div class="cont">
                <nav>
                    <a href="/home">Home</a>
                    <span></span>
                    <a href="/type/<?= format_url_str($element['type']) ?>"><?= $element['type'] ?></a>
                    <span></span>
                    <a href="/anime/<?= $title_id ?>"><?= $anime_title ?></a>
                </nav>

                <h1><?= $anime_title ?></h1>

                <div class="inf">
                    <div class="tick">
                        <span class="ep">Ep <?= array_key_last($anime_sources) ?></span>
                        <span class="lan"><?= $anime_lang_code ?></span>
                    </div>
                    <i></i><?= $element['type'] ?><i></i><?= $element['duration'] ?>
                </div>

                <div class="btn-w">
                    <a href="/watch/<?= $title_id ?>"><button class="w"><i class="fa-solid fa-play"></i>Watch
                            now</button></a>
                    <div class="popup-list" tabindex="0">
                        <button><i class="fa-solid fa-plus"></i>Add to List</button>
                        <ul>
                            <li data-id="1" class="ws-btn">Watching</li>
                            <li data-id="2" class="ws-btn">On-Hold</li>
                            <li data-id="3" class="ws-btn">Plan to watch</li>
                            <li data-id="4" class="ws-btn">Dropped</li>
                            <li data-id="5" class="ws-btn">Completed</li>
                        </ul>
                    </div>
                </div>

                <div class="des">
                    <p id="entry-des"><?= nl2br($element['description']) ?></p>
                    <span id="entry-des-btn">More Details</span>
                </div>

                <p><?= $site_name ?> is the best site to watch <?= $anime_title ?> online and download
                    <?= $anime_title ?> for free. You can also find
                    <?= $element['studios'] ? ' ' . $element['studios'][0] : $anime_title ?> anime on
                    <?= $site_name ?> website with English Sub/Dub.
                </p>
            </div>
        </div>

        <div class="rp">
            <div class="rpc-w">
                <?php
                $is_ongoing = false;
                foreach ($main_data as $e) {
                    if ($e['movie_id'] !== $element['movie_id'])
                        continue;

                    $ep = get_sources($e['movie_id']);
                    if (!$ep)
                        continue;

                    if (!$e['episodes'] || count($ep) < $e['episodes']) {
                        $is_ongoing = true;
                        break;
                    }
                }

                echo '<div><span>English:</span>' . $element['name'] . '</div>';
                if ($element['name'] != $element['alt_name'])
                    echo '<div><span>Japanese:</span>' . $element['alt_name'] . '</div>';

                echo '<span class="hl"></span>';

                $rd = [];
                $rd['Type'] = $element['type'];
                $rd['Audio'] = $element['language'];
                $rd['Aired'] = $element['release_year'];
                $rd['Season'] = $element['season'];
                $rd['Episodes'] = $element['episodes'];
                $rd['Duration'] = $element['duration'];
                $rd['Status'] = $is_ongoing ? 'Currently Airing' : 'Finished Airing';
                $rd['Rating'] = $element['rating'];

                foreach ($rd as $key => $val) {
                    if (!$val)
                        $val = 'N/A';
                    echo "<div><span>$key:</span>$val</div>";
                }

                echo '<span class="hl"></span>';

                echo '<div><span>Genres:</span>';
                foreach ($element['tags'] as $i => $tag)
                    echo '<a href="/genre/' . format_url_str($tag) . '">' . $tag . '</a>' . ($i + 1 < count($element['tags']) ? ', ' : '');
                echo '</div>';

                echo '<span class="hl"></span>';

                echo '<div><span>Studios:</span>' . implode(', ', $element['studios']) . '</div>';

                if ($element['mal_id'])
                    echo '<div><span>Mal ID:</span><a href="https://myanimelist.net/anime/' . $element['mal_id'] . '" target="_blank">' . $element['mal_id'] . '</a></div>';
                ?>
            </div>
        </div>
    </div>

    <div class="content-wraper">
        <div class="content">
            <div class="m-panel">
                <section id="related-sect-w">
                    <div class="section-title">
                        <h2>Related Anime</h2>
                    </div>
                    <div class="movie-cards-container" id="related-sect"></div>
                </section>
                <section id="character-sect-w">
                    <div class="section-title">
                        <h2>Characters & Voice Actors</h2>
                    </div>
                    <div id="character-sect"></div>
                </section>
                <?php
                if ($element['trailer']) {
                    echo '
                    <section>
                        <div class="section-title">
                            <h2>Promotion Videos</h2>
                        </div>
                        <div class="promo-vid-w">
                            <a href="https://www.youtube.com/watch?v=' . $element['trailer'] . '" target="_blank" class="promo-vid">
                                <div class="p">
                                    <img src="https://img.youtube.com/vi/' . $element['trailer'] . '/mqdefault.jpg" alt="' . $anime_title . ' - Trailer">
                                    <div class="play"><i class="fa-solid fa-play"></i></div>
                                </div>
                                <div class="t">' . $anime_title . ' - Trailer</div>
                            </a>
                        </div>
                    </section>';
                }
                ?>
                <section>
                    <div class="section-title">
                        <h2>More Like This</h2>
                    </div>
                    <div class="movie-cards-container">
                        <?php
                        foreach (get_suggestions([$element['movie_id']]) as $e) {
                            load_card($e);
                        }
                        ?>
                    </div>
                </section>
            </div>
            <div class="r-panel"><?php include 'layout/sidebar/new.php' ?></div>
        </div>
    </div>

    <script>
        <?php echo minify('js/anime.js') ?>
    </script>

    <?php include "layout/footer/def.php" ?>
</body>

</html>