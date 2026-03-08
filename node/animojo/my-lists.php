<!DOCTYPE html>
<html lang="en">

<head>
    <?php
    include '_inc/_config.php';
    $meta_preset = ['title' => "My Lists - $site_name"];
    include "layout/meta/def.php" ?>
    <style>
        <?php echo minify('css/my-lists.css') ?>
    </style>
</head>

<body>
    <?php include "layout/header/def.php" ?>
    <div class="content-wraper">
        <div class="main-content-container">
            <div class="m-title">
                <h1><i class="fa-regular fa-bookmark"></i> My Lists</h1>
            </div>
            <div class="btn-container-w">
                <div class="title-btn-container">
                    <button onclick="loadList('watching')" class="title-btn" data-name="watching">Watching</button>
                    <button onclick="loadList('bookmarks')" class="title-btn" data-name="bookmarks">Bookmarks</button>
                    <button onclick="loadList('completed')" class="title-btn" data-name="completed">Completed</button>
                    <button onclick="loadList('history')" class="title-btn" data-name="history">History</button>
                </div>
            </div>
            <div class="details-preview">
                <p id="section-alt-text">...</p>
                <div class="movie-cards-container" id="cards-container"></div>
            </div>
        </div>
    </div>
    <script><?php echo minify('js/my-lists.js') ?></script>
    <?php include "layout/footer/def.php" ?>
</body>

</html>