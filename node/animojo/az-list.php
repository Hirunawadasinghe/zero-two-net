<!DOCTYPE html>
<html lang="en">

<head>
    <?php
    include '_inc/_config.php';
    $meta_preset = ['title' => "AZ list - $site_name"];
    include "layout/meta/def.php"; ?>
    <style>
        <?php echo minify('css/az-list.css') ?>
    </style>
</head>

<body>
    <?php include "layout/header/def.php" ?>
    <div class="content-wraper">
        <div class="content">
            <section>
                <div class="section-title">
                    <h1>A to Z Anime List</h1>
                </div>
                <div class="header-w">
                    <input type="text" placeholder="Search anime..." autocomplete="off" id="az-search">
                    <i class="fas fa-search"></i>
                </div>
                <div id="data-list">
                    <div><span class="load-l"></span><span class="load-l"></span></div>
                    <div><span class="load-l"></span><span class="load-l"></span></div>
                    <div><span class="load-l"></span><span class="load-l"></span></div>
                </div>
            </section>
            <div class="r-panel">
                <?php
                include 'layout/sidebar/genres.php';
                include 'layout/sidebar/new.php';
                ?>
            </div>
        </div>
    </div>
    <script><?php echo minify('js/az-list.js') ?></script>
    <?php include "layout/footer/def.php" ?>
</body>

</html>