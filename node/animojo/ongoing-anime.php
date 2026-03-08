<!DOCTYPE html>
<html lang="en">

<head>
    <?php
    include '_inc/_config.php';

    $meta_preset = [
        'title' => "Ongoing Anime - $site_name",
        'canonical' => false
    ];
    include 'layout/meta/def.php';
    ?>
</head>

<body>
    <?php include 'layout/header/def.php'; ?>
    <div class="content-wraper">
        <div class="content">
            <section>
                <div class="section-title">
                    <h1>Ongoing Anime</h1>
                </div>
                <?php
                include '_inc/function.php';

                $result = filter_duplicates($main_data);
                $result = array_filter($result, function ($e) {
                    $ep = get_sources($e['movie_id']);
                    if (!$ep)
                        return;
                    if ($e['episodes'])
                        return count($ep) < $e['episodes'];
                    return true;
                });

                foreach ($result as &$e) {
                    $e['type'] = count(get_sources($e['movie_id']));
                }

                usort($result, function ($a, $b) {
                    $dateA = DateTime::createFromFormat('M d, Y', $a['release_year']);
                    $dateB = DateTime::createFromFormat('M d, Y', $b['release_year']);
                    return $dateB <=> $dateA;
                });

                echo '<div class="movie-cards-container">';

                $max_per_page = 30;
                $page = (isset($_GET['page']) && $_GET['page'] > 0 && $_GET['page'] <= count($result) / $max_per_page + 1) ? (int) $_GET['page'] : 1;
                $start_index = ($page * $max_per_page) - $max_per_page;

                for ($i = $start_index; $i < $page * $max_per_page && $i < count($result); $i++) {
                    load_card($result[$i]);
                }

                echo '</div>';

                print_bottom_nav(count($result), $max_per_page, $page, $_SERVER['REQUEST_URI']);
                ?>
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