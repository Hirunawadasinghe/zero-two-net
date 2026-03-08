<?php
$page_type = GET_URL(1);
if ($page_type === '')
    require 'error.php';

$page_type = ucfirst(str_replace('-', ' ', $page_type));

include '_inc/function.php';

$result = search([['type' => 'type', 'values' => $page_type]], 'main', $main_data);
if (count($result) === 0)
    require 'error.php';

usort($result, function ($a, $b) {
    $dateA = DateTime::createFromFormat('M d, Y', $a['release_year']);
    $dateB = DateTime::createFromFormat('M d, Y', $b['release_year']);
    return $dateB <=> $dateA;
});
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php
    $meta_preset = [
        'title' => "Watch $page_type Anime Online Free",
        'description' => "Watch $page_type Anime online with English SUB and DUB Free",
        'keywords' => [
            "watch $page_type anime",
            "free $page_type anime",
            "download $page_type anime",
            "latest $page_type anime",
            "dubbed $page_type anime",
            "subbed $page_type anime",
            "most watched $page_type anime"
        ],
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
                    <h1><?= $page_type ?> Anime</h1>
                </div>
                <div class="movie-cards-container">
                    <?php
                    $max_per_page = 30;
                    $page = (isset($_GET['page']) && $_GET['page'] > 0 && $_GET['page'] <= count($result) / $max_per_page + 1) ? (int) $_GET['page'] : 1;
                    $start_index = $page * $max_per_page - $max_per_page;
                    for ($i = $start_index; $i < $page * $max_per_page && $i < count($result); $i++) {
                        load_card($result[$i]);
                    }
                    ?>
                </div>
                <?php print_bottom_nav(count($result), $max_per_page, $page, $_SERVER['REQUEST_URI']); ?>
            </section>

            <div class="r-panel">
                <?php
                include 'layout/sidebar/genres.php';
                include 'layout/sidebar/new.php'; ?>
            </div>
        </div>
    </div>

    <?php include 'layout/footer/def.php'; ?>
</body>

</html>