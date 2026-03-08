<style>
    <?php echo minify('css/sidebar-genres.css') ?>
</style>
<section>
    <div class="section-title">
        <h2>Genres</h2>
    </div>
    <div class="rp-boxed">
        <div>
            <div class="rp-genre-list">
                <?php
                include_once $_SERVER['DOCUMENT_ROOT'] . '/_inc/function.php';
                $tags_arr = ["Action", "Adventure", "Cars", "Comedy", "Dementia", "Demons", "Drama", "Ecchi", "Fantasy", "Game", "Harem", "Historical", "Horror", "Isekai", "Josei", "Kids", "Magic", "Martial Arts", "Mecha", "Military", "Music", "Mystery", "Parody", "Police"];
                for ($i = 0; $i < count($tags_arr); $i++) {
                    echo '<a href="/genre/' . format_url_str($tags_arr[$i]) . '" style="--i:' . $i + 1 . '">' . $tags_arr[$i] . '</a>';
                }
                ?>
            </div>
            <a href="/genre"><button class="rp-genre-btn">Show more</button></a>
        </div>
    </div>
</section>