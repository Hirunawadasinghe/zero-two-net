<?php
include '_inc/function.php';

$movie_id = get_id_by_name(GET_URL(1));
$element = find_movie($movie_id);

if (!$element)
    require 'error.php';

include '_inc/encrypt.php';

$db = [];
$sub_entry = get_subtitle($movie_id);
if ($sub_entry && $sub_entry['id'] === $movie_id)
    $db = get_db_net_cache('subtitle', $movie_id, $db_net_path . '/subtitle?id=' . $movie_id, 60 * 60); // 1 hours

$sub_data = [];
foreach ($db as $entry) {
    $data = [];

    foreach ($entry['data'] as $file) {
        $data[] = [
            'title' => $file['file'],
            'date' => $file['date'],
            'downloads' => get_sub_downloads($movie_id, ['folder' => $entry['lan_code'] . '/' . $entry['folder'], 'file' => $file['file']])
        ];
    }

    $sub_data[] = [
        'author' => $entry['author'],
        'language' => $entry['language'],
        'encrypt' => '/subtitles/download.php?b=' . rawurlencode(encrypt_srt(json_encode([
            'i' => $movie_id,
            'p' => $entry['folder'],
            'b' => $entry['base'],
            'l' => $entry['lan_code'],
            't' => time()
        ], JSON_UNESCAPED_UNICODE), $encryption_key)),
        'data' => $data
    ];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php
    $title = 'Watch ' . $element['name'] . ' English Sub/Dub online Free on ' . $site_name;
    $description = 'Best site to watch ' . $element['name'] . ' English Sub/Dub online Free and download ' . $element['name'] . ' English Sub/Dub anime.';
    $meta_preset = [
        'title' => $element['name'] . ' - සිංහල උපසිරැසි',
        'description' => $element['name'] . ' සිංහල උපසිරැසි සමඟ බලන්න, උපසිරැසි ඩවුන්ලෝඩ් කරග​න්න පිවිසෙන්න​ ' . $stylish_url,
        'keywords' => [
            $element["name"] . " sinhala subtitles",
            $element["name"] . " සිංහල උපසිරැසි",
            $element["name"] . " subtitles download",
            "සිංහල උපසිරැසි"
        ],
        'url' => 'https://' . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"],
        'banner' => $element["thumbnail_image"]
    ];
    include 'layout/meta/def.php';
    ?>
    <style>
        <?php
        echo minify('css/subtitle.css');
        ?>
    </style>
</head>

<body>
    <?php include 'layout/header/def.php' ?>
    <div class="content-wraper">
        <div class="content">
            <div class="m-panel">
                <section>
                    <div class="section-title">
                        <h1><?= $element['name'] ?> Sinhala
                            Subtitles<?= $element['type'] !== 'Movie' ? ' – Full Series' : '' ?></h1>
                    </div>
                    <div class="thumb-prv">
                        <div style="background-image:url('<?= $element['thumbnail_image'] ?>')"></div>
                        <img src="<?= $element['thumbnail_image'] ?>" alt="<?= $element['name'] ?>">
                    </div>
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
                    <p class="description"><?= nl2br($element['description']) ?>
                        <br><br>
                        <?= $site_name ?> is the best place where you can download sinhala subtitles for
                        <?= $element['name'] ?> and watch <?= $element['name'] ?> online with sinhala subtitle for free.
                        <?= $element['name'] ?> සිංහල උපසිරැසි සමඟ බලන්න, උපසිරැසි ඩිරෙක්ට් ඩවුන්ලෝඩ් කරග​න්න.
                    </p>
                </section>

                <?php
                if ($preff_lang == 'si') {
                    echo '<details class="sub-note"><summary><h2>🎬 උපසිරැසි තෝරාගැනීමට අත්වැලක්</h2><i class="fa-solid fa-angle-down"></i></summary>
                    <div>
                        <p>පහලින් ඔයාලට උපසිරැසි ෆයිල් වර්ග දෙකක් දකින්න ලැබෙයි. ඔයාල බලන Device එක අනුව ගැලපෙන් උපසිරැසි වර්ගය​ ඩවුන්ලෝඩ් කරගන්න.</p>
                        <h2><u>.SRT උපසිරැසි</u></h2>
                        <p>මේ තමයි සාමාන්‍ය විදියට අපි හැමෝම පාවිච්චි කරන උපසිරැසි වර්ගය​. මේ උපසිරැසිවල​ අකුරුවල පාට හෝ ඩිසයින් මොකුත් නැහැ Text විතරයි තියෙන්නෙ. මේ උපසිරැසි පරන​ Phones, TV, Players ඕනම එක්ක කිසිම ප්‍රශ්නයක් නැතුව​ වැඩ කරනවා.</p>
                        <h2><u>.ASS උපසිරැසි</u></h2>
                        <p>මේක සංකීර්ණ​ උපසිරැසි වර්ගය​ක්. මේ උපසිරැසිවල​ අකුරු පාට පාටින්, ලස්සන Fonts වලින් වගේම තිරයෙ අවශ්‍ය තැන්වල පෙන්නන්න පුලුවන් විදියට හදලයි තියෙන්නෙ​. මේ උපසිරැසිවලින් බලනකොට ඔයාලට දෙබස් තේරුම් ගන්න පහසුයි. සමහර Phones සහ පරණ Players මේ වර්ගයෙ උපසිරැසි හරියට වැඩ කරන්නෙ නැතුව යන්න පුලුවන් (අකුරු කොටු කොටු වගේ පේන්න පුළුවන්).</p>
                        <img src="https://i.ibb.co/PGWg2hb3/sub-deff.jpg" alt="difference between srt and ass">
                    </div></details>';
                }
                ?>

                <section>
                    <div class="section-title">
                        <h2>Download Subtitles</h2>
                    </div>
                    <?php
                    function format_time($t)
                    {
                        $t = time() - $t;
                        $units = [
                            31536000 => 'y',
                            2592000 => 'm',
                            86400 => 'd',
                            3600 => 'h',
                            60 => 'min',
                            1 => 's'
                        ];
                        foreach ($units as $secs => $name) {
                            if ($t >= $secs) {
                                return floor($t / $secs) . ' ' . $name . ' ago';
                            }
                        }
                    }

                    if (!$sub_data) {
                        echo 'Subtitles not available.';
                    } else {
                        $str = [];
                        echo '<table id="sub-list">';
                        for ($i = 0; $i < count($sub_data); $i++) {
                            $e = $sub_data[$i];
                            $str[$i] = $e['encrypt'];
                            echo '<tr><th><span>' . $e['author'] . '</span></th><th><span>File name</span></th><th><span>Date</span></th><th class="center"><span>Download</span></th></tr>';
                            foreach ($e['data'] as $sub) {
                                echo '<tr><td><span>' . $e['language'] . '</span></td><td><span>' . $sub['title'] . '</span></td><td><span>' . format_time($sub['date'] / 1000) . '</span></td><td class="center"><a data-e="' . encrypt_srt($sub['title'], $encryption_key) . '" data-i="' . $i . '" target="_blank"><i class="fa-solid fa-download"></i> ' . $sub['downloads'] . '</a></td></tr>';
                            }
                        }
                        echo '</table><script>const subdl_base_url=' . json_encode($str) . ';' . minify('js/sub_list.js') . '</script>';
                    } ?>
                </section>
            </div>

            <div class="r-panel">
                <?php include 'layout/sidebar/new.php' ?>
            </div>
        </div>
    </div>

    <?php include 'layout/footer/def.php' ?>
</body>


</html>
