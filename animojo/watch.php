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
    $anime_sources = get_sources($element['movie_id']);
    $anime_condition['status'] = $anime_sources ? true : false;

    $page_title = 'Watch ' . $anime_title . ' English Sub/Dub online Free on ' . $site_name;
    $page_description = 'Best site to watch ' . $anime_title . ' English Sub/Dub online Free and download ' . $anime_title . ' English Sub/Dub anime.';

    $meta_preset = [
        'title' => $page_title,
        'description' => $page_description,
        'keywords' => [
            $element["name"] . " English Sub/Dub",
            "free " . $element["name"] . " online",
            "watch " . $element["name"] . " online",
            "watch " . $element["name"] . " free",
            "download " . $element["name"] . " anime",
            "download " . $element["name"] . " free",
            $element["name"] . " sinhala subtitles"
        ],
        'url' => 'https://' . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"],
        'banner' => $element["thumbnail_image"]
    ];

    include 'layout/meta/def.php';

    if (!$element['duration'] || $element['duration'] === 'Unknown') {
        $durationISO = '';
    } else {
        $clean = trim(preg_replace('/per ep.*/i', '', $element['duration']));
        $clean = str_replace([' hr', ' min', ' '], ['H', 'M', ''], $clean);
        $durationISO = 'PT' . $clean;
    }

    $videoSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'VideoObject',
        'name' => $page_title,
        'description' => $page_description,
        'thumbnailUrl' => $element['thumbnail_image'] ?? '',
        'uploadDate' => date('Y-m-d', strtotime($element['release_year'])),
        'duration' => $durationISO,
        'contentUrl' => 'https://' . $_SERVER['HTTP_HOST'] . '/watch/' . $title_id,
        'embedUrl' => 'https://' . $_SERVER['HTTP_HOST'] . '/watch/' . $title_id
    ];

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
                'item' => 'https://' . $_SERVER['HTTP_HOST'] . '/watch/' . $title_id
            ]
        ]
    ];

    echo '<script type="application/ld+json">' . json_encode($videoSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
    echo '<script type="application/ld+json">' . json_encode($breadcrumbSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';

    // bot comment
    if ($advance_settings['bot_comment'] && rand(1, 5) > 3)
        echo '<script>fetch("/api/bcmt.php?id=' . $element['movie_id'] . '")</script>';
    ?>
    <style>
        <?php echo minify('css/watch.css') ?>
    </style>
</head>

<body>
    <?php
    include "layout/header/def.php";

    if ($anime_condition['status']) {
        $compressed_source = [
            'host' => [],
            'url' => []
        ];

        $host_index = [];
        $host_id = 0;

        foreach ($anime_sources as $ep => $servers) {
            $ep_urls = [];

            foreach ($servers as $host => $link) {
                $url_parts = parse_url($link);
                if (empty($url_parts['host']) || empty($url_parts['path']))
                    continue;

                if (!isset($host_index[$host])) {
                    $host_id++;
                    $host_index[$host] = $host_id;
                    $compressed_source['host'][$host_id] = [$host, $url_parts['host']];
                }

                $path = $url_parts['path'];
                if (isset($url_parts['query']))
                    $path .= '?' . $url_parts['query'];
                if (isset($url_parts['fragment']))
                    $path .= '#' . $url_parts['fragment'];
                $ep_urls[$host_index[$host]] = $path;
            }

            $compressed_source['url'][] = [$ep, $ep_urls];
        }
    }

    echo '<script id="srcd">const anime_details=' . json_encode([
        'id' => $element['movie_id'],
        'title' => $anime_title,
        'status' => $anime_condition['status'],
        'source' => isset($compressed_source) ? $compressed_source : null
    ], JSON_UNESCAPED_UNICODE) . ';document.getElementById("srcd").remove();</script>';
    ?>

    <div class="ani-cover-w">
        <div class="ani-cover" style="background-image:url('<?= $element['thumbnail_image'] ?>')"></div>

        <div class="wp-notice">
            <i class="fa-solid fa-star"></i> Pro Tip: Bookmark <a href="https://animojo.zya.me" target="_blank">AniMojo.zya.me</a>, <a href="https://animojo.ct.ws" target="_blank">AniMojo.ct.ws</a> as alternative domains <i class="fa-solid fa-arrow-right"></i> Backup entrance to <?= $site_name ?>
        </div>

        <div class="page-top-nav" class="dsp-none">
            <a href="/home">Home</a>
            <i class="fa-solid fa-angle-right"></i>
            <a href="/type/<?= format_url_str($element['type']) ?>"><?= $element['type'] ?></a>
            <i class="fa-solid fa-angle-right"></i>
            <a href="/watch/<?= format_title($element) ?>"><?= $anime_title ?></a>
        </div>

        <div class="pre-w">
            <?php
            if (!$anime_condition['status']) {
                echo '<div class="player-notice"><div class="d1" style="background-image: url(\'' . $element['thumbnail_image'] . '\')"></div><div class="d2">Coming Soon...</div></div>';
            }
            ?>
            <div<?= !$anime_condition['status'] ? ' style="display:none"' : '' ?>>
                <div class="main-preview-w">
                    <div class="playlist-container-w">
                        <div class="pl-header-c">
                            <div class="pl-header">
                                <span>List of episodes:</span>
                                <div>
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                    <input type="text" placeholder="Number of Ep" autocomplete="off" id="pl-search">
                                </div>
                            </div>
                            <div id="episode-sector"></div>
                        </div>
                        <div class="playlist-container">
                            <div id="playlist"></div>
                        </div>
                    </div>

                    <div class="player-and-controls-w">
                        <div id="player-iframe-w">
                            <div id="player-back-drop"></div>
                            <div class="player-preview">
                                <div class="buffering-circle player-buffer"></div>
                                <iframe id="iframe-preview" title="<?= $anime_title ?> Video" allowfullscreen
                                    allowtransparency allow="autoplay" scrolling="no" frameborder="0"></iframe>
                            </div>
                        </div>

                        <div class="player-and-controls-bottom">
                            <div class="player-pre-control-w">
                                <div class="d0">
                                    <div class="d1" id="player-light-btn">
                                        <span class="name"><i class="fa-solid fa-lightbulb"></i>Light</span>
                                        <span class="con">On</span>
                                    </div>
                                    <div class="d1" id="player-expland-btn">
                                        <span class="name"><i class="fa-solid fa-expand"></i>Expland</span>
                                    </div>
                                </div>
                                <div class="d0">
                                    <div class="ep-user-int">
                                        <!-- <div class="btn">
                                            <div><span>12</span><i class="fa-regular fa-thumbs-up"></i></div>
                                        </div>
                                        <span class="bl"></span>
                                        <div class="btn">
                                            <i class="fa-regular fa-thumbs-down"></i>
                                        </div>
                                        <span class="bl"></span> -->
                                        <div class="btn">
                                            <div><span id="ep-view-count"></span>views</div>
                                        </div>
                                    </div>
                                    <div id="player-add" class="popup-list" tabindex="0">
                                        <div class="d1"><i class="fa-solid fa-plus" id="watch-status-btn"></i></div>
                                        <ul>
                                            <li data-id="1" class="ws-btn">Watching</li>
                                            <li data-id="2" class="ws-btn">On-Hold</li>
                                            <li data-id="3" class="ws-btn">Plan to watch</li>
                                            <li data-id="4" class="ws-btn">Dropped</li>
                                            <li data-id="5" class="ws-btn">Completed</li>
                                        </ul>
                                    </div>
                                    <div class="d1"><i class="fa-solid fa-flag" id="player-report"></i></div>
                                </div>
                            </div>

                            <div class="player-control-w">
                                <div class="player-control" id="prev-btn">
                                    <button class="control"><i class="fa-solid fa-caret-left"></i></button>
                                </div>
                                <div class="player-control show max" tabindex="0">
                                    <div class="p-l-dropdown-list" id="download-opt"></div>
                                    <button class="control">Download</button>
                                </div>
                                <div class="player-control show max" tabindex="0">
                                    <div class="p-l-dropdown-list" id="switch-opt"></div>
                                    <button class="control">Switch Player</button>
                                </div>
                                <div class="player-control" id="next-btn">
                                    <button class="control"><i class="fa-solid fa-caret-right"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        </div>

        <div class="details-container">
            <div class="details-left-section">
                <div class="thumbnail-c">
                    <img src="<?= $element['thumbnail_image'] ?>" alt="<?= $anime_title ?>" onerror="imgError(this)">
                    <?= array_intersect(['Ecchi', 'Erotica', 'Hentai'], $element['tags']) ? '<div class="movie-card-top-row"><span class="thumbnail-info tl">18+</span></div>' : '' ?>
                </div>

                <div class="anime-action-btn-c">
                    <button class="anime-action-btn" style="background-color: var(--light-blue-color)"
                        id="bookmark-btn"></button>
                    <?php
                    $sub_data = get_subtitle($element['movie_id']);
                    if ($sub_data) {
                        $sub_url = $title_id;
                        if ($sub_data['id'] !== $element['movie_id']) {
                            $sub_url = format_title(find_movie($sub_data['id']));
                        }
                        echo '<a href="/subtitle/' . $sub_url . '"><button class="anime-action-btn" style="background-color: rgba(255, 255, 255, .15)"><i class="fa-solid fa-closed-captioning"></i>Subtitles</button></a>';
                    }
                    ?>
                </div>

                <div class="rating-c">
                    <p id="rating-text">Rating N/A</p>
                    <div class="rating-section no-highlight">
                        <div id="ratings-stars">★★★★★</div>
                        <span id="rating-tooltip"></span>
                    </div>
                </div>
            </div>
            <div>
                <?php
                $schedule_db_path = 'cache/database/schedule';
                $schedule_db = file_exists($schedule_db_path) ? include $schedule_db_path : null;
                if ($schedule_db) {
                    $entry_sch = array_filter($schedule_db, function ($e) {
                        global $element;
                        return $e['mal_id'] === $element['mal_id'];
                    });
                    if (count($entry_sch) > 0) {
                        usort($entry_sch, function ($a, $b) {
                            return $a['airingAt'] <=> $b['airingAt'];
                        });
                        $placed = false;
                        foreach ($entry_sch as $e) {
                            if ($e['airingAt'] > time()) {
                                echo '<div class="next-sch-sect"><p id="next-sch-p">Countdown to Episode ' . $e['episode'] . ' : <span id="next-sch-s" data-time="' . $e['airingAt'] . '">ᓚᘏᗢ</span></p></div>';
                                $placed = true;
                                break;
                            }
                        }
                        if (!$placed && !isset($anime_sources[end($entry_sch)['episode']])) {
                            echo '<div class="next-sch-sect"><p>Waiting for Episode: <span>' . end($entry_sch)['episode'] . '</span></p></div>';
                        }
                    }
                }
                ?>
                <h1 class="video-title"><?= $anime_title ?></h1>
                <div class="details-right-section">
                    <div class="data-container">
                        <div>
                            <i class="fa-solid fa-circle-play"></i>
                            <a
                                href="/type/<?= strtolower($element['type']) ?>"><?= htmlspecialchars($element['type']) ?></a>
                        </div>
                        <div>
                            <i class="fa-solid fa-calendar"></i>
                            <a
                                href="/search?year=<?= trim(explode(',', $element['release_year'])[1]) ?>"><?= htmlspecialchars($element['release_year']) ?></a>
                        </div>
                        <div>
                            <i class="fa-solid fa-language"></i>
                            <a
                                href="/search?language=<?= urlencode($element['language']) ?>"><?= htmlspecialchars($element['language']) ?></a>
                        </div>
                        <div>
                            <i class="fa-solid fa-hashtag"></i>Ep
                            <span><?= htmlspecialchars($element['episodes'] ?? 'N/A') ?></span>
                        </div>
                        <div>
                            <i class="fa-solid fa-clock"></i>
                            <span><?= htmlspecialchars($element['duration']) ?></span>
                        </div>
                        <div>
                            <i class="fa-solid fa-e"></i>:
                            <span><?= htmlspecialchars($element['rating'] ?? 'N/A') ?></span>
                        </div>
                    </div>
                    <div class="video-description">
                        <p>
                            <?= nl2br($element['description']) ?>
                            <br><br>
                            <?= $site_name ?> is the best site to watch <?= $anime_title ?> online and
                            download
                            <?= $anime_title ?> for free. You can also find
                            <?= $element['studios'] ? ' ' . $element['studios'][0] : $anime_title ?> anime on
                            <?= $site_name ?> website with English Sub/Dub.
                        </p>
                    </div>
                    <div id="info-c"></div>
                    <div class="video-tags">
                        <?php
                        foreach ($element['tags'] as $t) {
                            echo '<a href="/genre/' . format_url_str($t) . '">' . $t . '</a>';
                        } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

    <div class="content-wraper">
        <div class="content">
            <div class="m-panel">
                <section>
                    <?php
                    // comment section script
                    include '_inc/dbh.php';
                    $comments = [];
                    try {
                        $r = $conn->query("SELECT name, comment, created_at FROM ep_comments WHERE page_id='" . $element['movie_id'] . "' ORDER BY id DESC");
                        $comments = $r->fetch_all(MYSQLI_ASSOC);
                    } catch (err) {
                    }
                    ?>
                    <div class="section-title">
                        <h2>Comments</h2>
                        <span class="comment-count">
                            <i class="fa-regular fa-message"></i> <?php echo count($comments) ?>
                        </span>
                    </div>
                    <div class="comment-sect">
                        <div class="comment-split">
                            <img src="https://i.postimg.cc/6pQm2hNs/none-profile.jpg" alt="profile image" loading="lazy"
                                class="comment-u-img comment-form-u-img">
                            <form id="comment-form">
                                <textarea name="comment" placeholder="Enter your comment here..." required></textarea>
                                <div class="b-row">
                                    <div class="in-c">
                                        <div>
                                            <span><i class="fa-solid fa-user"></i></span>
                                            <input type="name" name="username" placeholder="name" required>
                                        </div>
                                        <div>
                                            <span><i class="fa-solid fa-at"></i></span>
                                            <input type="email" name="email" placeholder="email" required>
                                        </div>
                                    </div>
                                    <button type="submit" id="cmt-post-btn"><img src="/images/assets/buffer-c.gif">
                                        Comment</button>
                                </div>
                                <p id="cmt-error-msg"></p>
                            </form>
                        </div>
                        <div id="comments">
                            <?php
                            function format_time($t)
                            {
                                $stp = new DateTime($t);
                                $t = time() - $stp->getTimestamp();
                                $units = [
                                    31536000 => 'years',
                                    2592000 => 'months',
                                    86400 => 'days',
                                    3600 => 'hours',
                                    60 => 'minutes',
                                    1 => 'seconds'
                                ];
                                foreach ($units as $secs => $name) {
                                    if ($t >= $secs) {
                                        return floor($t / $secs) . ' ' . $name . ' ago';
                                    }
                                }
                                return 'Just Now';
                            }
                            if (count($comments) > 0) {
                                foreach ($comments as $row) {
                                    echo '
                                    <div class="comment">
                                        <div class="comment-split">
                                            <img src="https://i.postimg.cc/6pQm2hNs/none-profile.jpg" alt="profile image" loading="lazy" class="comment-u-img">
                                            <div><div class="header"><h3>' . htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') . '</h3><i class="dot"></i><span class="time">' . format_time($row['created_at']) . '</span></div><p>' . nl2br(htmlspecialchars($row['comment'], ENT_QUOTES, 'UTF-8')) . '</p></div>
                                        </div>
                                    </div>';
                                }
                            } else {
                                echo '<p id="no-cmt-msg">No comments yet...</p>';
                            }
                            ?>
                        </div>
                    </div>
                </section>

                <section>
                    <div class="section-title">
                        <h2>Recommended for you</h2>
                    </div>
                    <div class="movie-cards-container">
                        <?php
                        foreach (get_suggestions([$element['movie_id']], 10) as $e) {
                            load_card($e);
                        }
                        ?>
                    </div>
                </section>
            </div>
            <div class="r-panel"><?php include 'layout/sidebar/new.php' ?></div>
        </div>

        <div id="popup-window">
            <div class="popup-window-back" onclick="toggle_popup_window()"></div>
            <form class="popup-window-itm" id="report-popup">
                <div class="report-popup-h">
                    <span>Report Video</span>
                </div>
                <div class="report-popup-in-w">
                    <div class="report-popup-input-c">
                        <label for="report-opt">Select error:</label>
                        <select id="report-opt">
                            <option value="Video won't load" select>Video won't load</option>
                            <option value="Subtitle out of sync">Subtitle out of sync</option>
                            <option value="Subtitle Quality Issue">Subtitle Quality Issue</option>
                            <option value="Wrong Video">Wrong Video</option>
                            <option value="">Other</option>
                        </select>
                    </div>

                    <div class="report-popup-input-c">
                        <label for="report-other-opt">Other problem:</label>
                        <div id="report-other-opt-c">
                            <textarea style="resize: none" id="report-other-opt"></textarea>
                            <span>Other problem cannot be blank.</span>
                        </div>
                    </div>

                    <div class="report-popup-input-c">
                        <label for="report-email">Email address:</label>
                        <div id="report-email-c">
                            <input type="email" id="report-email" placeholder="name@email.com" required>
                            <span>Enter a valid email address.</span>
                        </div>
                    </div>
                </div>
                <div class="report-popup-btn-w">
                    <input type="submit" value="Report" id="vid-report-btn">
                    <input type="reset" value="Reset">
                </div>
            </form>
        </div>
    </div>

    <script>
        const vid_ad_link = "<?= $ad_link['video_popup'] ?>";
        <?php echo minify('js/watch.js') ?>
        <?php echo minify('js/dvt-k.js') ?>
    </script>

    <?php include "layout/footer/def.php" ?>
</body>

</html>