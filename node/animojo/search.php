<!DOCTYPE html>
<html lang="en">

<head>
    <?php
    $search_text = empty($_GET['text']) ? null : htmlspecialchars($_GET['text'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

    if ($search_text) {
        include '_inc/_config.php';
        $meta_preset['title'] = "Search result for $search_text on $site_name";
        $meta_preset['description'] = "Find and watch $search_text anime in HD Quality for FREE";
        $meta_preset['keywords'] = [
            "$search_text English Sub/Dub",
            "free $search_text online",
            "watch $search_text online",
            "watch $search_text free",
            "download $search_text anime",
            "download $search_text free"
        ];
    }
    $meta_preset['canonical'] = false;
    include 'layout/meta/def.php';
    ?>
    <style>
        <?php echo minify('css/search.css') ?>
    </style>
</head>

<body>
    <?php include 'layout/header/def.php' ?>
    <div class="content-wraper">
        <?php
        include '_inc/function.php';

        $types = [];
        $audios = [];
        $tags = [];
        $years = [];
        $typeOptions = '';
        $audioOptions = '';
        $tagOptions = '';
        $yearOptions = '';

        foreach ($main_data as $e) {
            if (!in_array($e['type'], $types)) {
                $types[] = $e['type'];
                $typeOptions .= "<option value='" . strtolower($e['type']) . "'>" . $e['type'] . "</option>";
            }
            if (!in_array($e['language'], $audios)) {
                $audios[] = $e['language'];
                $audioOptions .= "<option value='" . strtolower($e['language']) . "'>" . $e['language'] . "</option>";
            }
            foreach ($e['tags'] as $t) {
                if (!in_array($t, $tags))
                    $tags[] = $t;
            }
            $date_data = explode(',', $e['release_year']);
            if (count($date_data) > 1) {
                $releaseYear = $date_data[0];
                if (!in_array($releaseYear, $years))
                    $years[] = $releaseYear;
            }
        }

        sort($tags, SORT_STRING);
        foreach ($tags as $t) {
            $tagOptions .= '<option value="' . format_url_str($t) . '">' . $t . '</option>';
        }

        usort($years, function ($a, $b) {
            return strtotime($b) - strtotime($a);
        });
        foreach ($years as $year) {
            $yearOptions .= '<option value="' . $year . '">' . $year . '</option>';
        }

        // array reversed to show new anime at the top
        $result = search(getAllQueryParams(), 'main', !$search_text ? array_reverse($main_data) : $main_data);

        // remove explicit entries with no available sources
        $result = array_values(array_filter($result, function ($e) {
            if (strtolower($_GET['tags'] ?? '') != 'hentai' && in_array('hentai', array_map('strtolower', $e['tags']))) {
                $s = get_sources($e['movie_id']);
                if (!$s)
                    return false;
            }
            return true;
        }));
        ?>

        <div class="content">
            <div>
                <div class="box-section">
                    <div class="section-title">
                        <h1>Filter</h1>
                    </div>
                    <form id="search-form" class="filter-c">
                        <input type="text" placeholder="Search Text" name="text" data-key="text" id="search-in-text"
                            class="filter-inputs">
                        <select name="type" data-key="type" id="search-in-type" class="filter-inputs">
                            <option value="">Type: All</option>
                            <?php echo $typeOptions ?>
                        </select>
                        <select name="audio" data-key="audio" id="search-in-audio" class="filter-inputs">
                            <option value="">Audio: All</option>
                            <?php echo $audioOptions ?>
                        </select>
                        <select name="tags" data-key="tags" id="search-in-tags" class="filter-inputs">
                            <option value="">Genre: All</option>
                            <?php echo $tagOptions ?>
                        </select>
                        <select name="year" data-key="year" id="search-in-year" class="filter-inputs">
                            <option value="">Year: All</option>
                            <?php echo $yearOptions ?>
                        </select>
                        <select name="sort" data-key="sort" id="search-in-sort" class="filter-inputs">
                            <option value="">Sort by: Relevance</option>
                            <option value="newest">Newest First</option>
                            <option value="oldest">Oldest First</option>
                            <option value="name">A to Z</option>
                        </select>
                        <a href="request">
                            <button type="button" style="width:100%"><i
                                    class="fa-solid fa-circle-plus"></i>Request</button>
                        </a>
                        <button type="submit"><i class="fa-solid fa-magnifying-glass"></i>Search</button>
                    </form>
                </div>

                <h2 class="search-text">
                    <?php echo $search_text && count($result) > 0 ? 'Search results for: <i>' . $search_text . '</i>' : ''; ?>
                </h2>

                <div>
                    <?php
                    if (count($result) > 0) {
                        echo '<div class="movie-cards-container">';
                        $max_per_page = 30;
                        $page = (isset($_GET['page']) && $_GET['page'] > 0 && $_GET['page'] <= count($result) / $max_per_page + 1) ? (int) $_GET['page'] : 1;
                        $start_index = ($page * $max_per_page) - $max_per_page;
                        for ($i = $start_index; $i < $page * $max_per_page && $i < count($result); $i++) {
                            load_card($result[$i]);
                        }
                        echo '</div>';

                        print_bottom_nav(count($result), $max_per_page, $page, $_SERVER['REQUEST_URI']);

                    } else {
                        echo '<div class="search-none"><img src="/images/characters/confused.png"><h2>No Results Found</h2><p>It seems we couldn’t find any results for your search<br>But don’t worry—just make a request!</p></div>';
                    }
                    ?>
                </div>
            </div>
            <div class="r-panel"><?php include 'layout/sidebar/new.php' ?></div>
        </div>
    </div>
    <script>
        <?php echo minify('js/search.js') ?>
    </script>
    <?php include 'layout/footer/def.php' ?>
</body>

</html>