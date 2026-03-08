<!DOCTYPE html>
<html lang="en">

<head>
    <?php
    include '_inc/function.php';

    $page_genre = GET_URL(1);

    if ($page_genre) {
        $result = search([['type' => 'tags', 'values' => $page_genre]], 'main', $main_data);
        if (count($result) === 0)
            require 'error.php';

        usort($result, function ($a, $b) {
            $dateA = DateTime::createFromFormat('M d, Y', $a['release_year']);
            $dateB = DateTime::createFromFormat('M d, Y', $b['release_year']);
            return $dateB <=> $dateA;
        });

        $genre_text = ucwords(str_replace("-", " ", $page_genre));

        $meta_preset = [
            'title' => "Watch $genre_text Anime Online Free",
            'description' => "Watch $genre_text Anime online with English SUB and DUB Free",
            'keywords' => [
                "watch $page_genre anime",
                "free $page_genre anime",
                "download $page_genre anime",
                "latest $page_genre anime",
                "dubbed $page_genre anime",
                "subbed $page_genre anime",
                "most watched $page_genre anime"
            ],
            'canonical' => false
        ];
    }
    require 'layout/meta/def.php';
    ?>
    <style>
        <?php echo minify('css/genre.css'); ?>
    </style>
</head>

<body>
    <?php include 'layout/header/def.php' ?>
    <div class="content-wraper">
        <div class="content">
            <section <?= isset($genre_text) ? '' : 'class="box-section"' ?>>
                <div class="section-title">
                    <h1><?= isset($genre_text) ? $genre_text . ' Anime' : 'All Genres / Tags' ?></h1>
                </div>

                <?php
                if ($page_genre) {
                    $max_per_page = 30;
                    $page = (isset($_GET['page']) && $_GET['page'] > 0 && $_GET['page'] <= count($result) / $max_per_page + 1) ? (int) $_GET['page'] : 1;
                    $start_index = ($page * $max_per_page) - $max_per_page;
                    echo '<div class="movie-cards-container">';
                    for ($i = $start_index; $i < $page * $max_per_page && $i < count($result); $i++) {
                        load_card($result[$i]);
                    }
                    echo '</div>';
                    print_bottom_nav(count($result), $max_per_page, $page, $_SERVER['REQUEST_URI']);

                } else {
                    $genres = [];
                    foreach ($main_data as $e) {
                        foreach ($e['tags'] as $t) {
                            $key = array_search($t, array_column($genres, 'name'));
                            if ($key !== false) {
                                $genres[$key]['count']++;
                            } else {
                                $genres[] = [
                                    "name" => $t,
                                    "count" => 1
                                ];
                            }
                        }
                    }
                    usort($genres, function ($a, $b) {
                        return strcmp($a['name'], $b['name']);
                    });
                    echo '<div class="genre-tags">';
                    foreach ($genres as $g) {
                        echo '<a href="genre/' . format_url_str($g['name']) . '" class="g-tag"><p>' . $g['name'] . '</p><span>' . $g['count'] . '</span></a>';
                    }
                    echo '</div>';
                }
                ?>
            </section>

            <div class="r-panel">
                <?php
                if ($page_genre)
                    include 'layout/sidebar/genres.php';

                include 'layout/sidebar/new.php';
                ?>
            </div>
        </div>
    </div>
    <?php include 'layout/footer/def.php' ?>
</body>

</html>