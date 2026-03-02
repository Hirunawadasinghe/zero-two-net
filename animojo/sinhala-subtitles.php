<!DOCTYPE html>
<html lang="en">

<head>
    <?php
    $meta_preset = [
        'title' => 'සිංහල උපසිරැසි සමඟ ඇනිමෙ - Anime with Sinhala Subtitles',
        'description' => 'සිංහල උපසිරැසි සමඟ ඇනිමෙ බලන්න ඩිරෙක්ට් ඩවුන්ලෝඩ් කරන්න',
        'keywords' => [
            "anime with sinhala subtitles",
            "සිංහල උපසිරැසි",
            "anime sinhala",
            "sinhala subtitles",
            "download sinhala subtitles",
            "ඇනිමෙ"
        ],
        'canonical' => false
    ];
    include 'layout/meta/def.php' ?>
</head>

<body>
    <?php include 'layout/header/def.php' ?>
    <div class="content-wraper">
        <div class="content">
            <section>
                <div class="section-title">
                    <h1>Anime with Sinhala Subtitles</h1>
                </div>
                <?php
                include '_inc/function.php';
                load_subtitle_data();
                $sub_ids = [];
                foreach ($subtitle_data['subtitles'] as $e) {
                    foreach ($e['sub'] as $s) {
                        if ($s['lan'] === 'si') {
                            $sub_ids[$e['id'][0]] = true;
                            break;
                        }
                    }
                }
                $main_lookup = [];
                $sub_count = count($sub_ids);
                foreach ($main_data as $e) {
                    if (isset($sub_ids[$e['movie_id']])) {
                        $main_lookup[$e['movie_id']] = $e;
                        if (count($main_lookup) === $sub_count) {
                            break;
                        }
                    }
                }
                $result = [];
                $found_count = 0;
                foreach (array_reverse($subtitle_data['subtitles']) as $s) {
                    $id = $s['id'][0];
                    if (isset($main_lookup[$id])) {
                        $result[] = $main_lookup[$id];
                        if (++$found_count === $sub_count) {
                            break;
                        }
                    }
                }
                echo '<div class="movie-cards-container">';
                $max_per_page = 30;
                $page = (isset($_GET['page']) && $_GET['page'] > 0 && $_GET['page'] <= count($result) / $max_per_page + 1) ? (int) $_GET['page'] : 1;
                $start_index = ($page * $max_per_page) - $max_per_page;
                for ($i = $start_index; $i < $page * $max_per_page && $i < count($result); $i++) {
                    load_card($result[$i]);
                }
                echo '</div>';
                print_bottom_nav(count($result), $max_per_page, $page, $_SERVER['REQUEST_URI']); ?>
            </section>

            <div class="r-panel">
                <?php
                include 'layout/sidebar/genres.php';
                include 'layout/sidebar/new.php';
                ?>
            </div>
        </div>
    </div>
    <?php include 'layout/footer/def.php' ?>
</body>

</html>