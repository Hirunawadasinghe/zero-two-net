<!DOCTYPE html>
<html lang="en">

<head>
    <?php
    include 'layout/meta/def.php';
    include '_inc/_config.php';
    ?>
    <style>
        <?php echo minify('css/intro.css'); ?>
    </style>
</head>

<body>
    <nav class="intro-nav">
        <a href="/type/movie" class="nav-list-itm">Movies</a>
        <a href="/type/tv" class="nav-list-itm">TV Series</a>
        <a href="/genre" class="nav-list-itm">Genres</a>
        <a href="/az-list" class="nav-list-itm">AZ List</a>
    </nav>
    <div class="intro-w">
        <div class="intro-rp"></div>
        <div class="intro-cw">
            <div class="intro-logo-w">
                <a href="/home" class="intro-logo"><img src="https://i.ibb.co/HLS4JYY0/logo.png" alt="<?= $site_name ?>"
                        loading="lazy"></a>
            </div>
            <form action="/search" class="intro-search">
                <input type="text" name="text" placeholder="Search anime..." required autocomplete="off">
                <button type="submit"><i class="fa fa-search"></i></button>
            </form>
            <div class="intro-tags">
                <span class="title">Top search:</span>
                <?php
                include '_inc/function.php';
                $ep_views_db = $database_path . '/action/og_ep_views';
                if (file_exists($ep_views_db)) {
                    $d = include $ep_views_db;
                    function get_view_count($in)
                    {
                        $max = 0;
                        foreach ($in as $ep => $count) {
                            if ($count > $max) {
                                $max = $count;
                            }
                        }
                        return $max;
                    }
                    usort($d, function ($a, $b) {
                        return get_view_count($b['ep']) <=> get_view_count($a['ep']);
                    });
                    for ($i = 0; $i < 10 && $i < count($d); $i++) {
                        $f = find_movie($d[$i]['id']);
                        if ($f) {
                            echo '<a href="/search?text=' . $f['name'] . '">' . $f['name'] . ($i + 1 < 10 ? ',' : '') . '</a>';
                        }
                    }
                }
                ?>
            </div>
            <div class="intro-btn-w">
                <a href="/home" class="watch">Watch anime<i class="fa-solid fa-circle-play"></i></a>
                <a href="/search">Discover<i class="fa-solid fa-angle-right"></i></a>
            </div>
        </div>
    </div>

    <div class="curve-w">
        <div class="c">
            <div class="curve"></div>
        </div>
    </div>

    <div class="content-wraper">
        <div class="content">
            <div class="dec">
                <?php
                if ($preff_lang === 'si') {
                    echo "
                    <section>
                        <h1>$site_name - Watch Anime Online Free in HD quality</h1>
                        <p><strong>$site_name</strong> යනු කිසිදු ලියාපදිංචි වීමකින් හෝ අමතර ගාස්තුවකින් තොරව, ඉතා වේගයෙන් සහ ආරක්ෂිතව Subbed හෝ Dubbed Anime නරඹන්නට සහ Download කරන්නට ඇති හොඳම තැනයි.</p>
                    </section>
                    <section>
                        <h2>1/ මොකක්ද මේ $site_name?</h2>
                        <p><strong>$site_name</strong> කියන්නේ කිසිම ලියාපදිංචි වීමකින් තොරව​, Full HD තත්වයේ Anime නොමිලේ බලන්න පුළුවන් වෙබ් අඩවියක්. Action, Romance, Comedy වගේ ඕනෑම වර්ගයක Anime විශාල එකතුවක් $site_name තුළ අඩංගු වෙනව​.</p>
                    </section>
                    <section>
                        <h2>2/ $site_name ආරක්ෂිතද?</h2>
                        <p>ඔව්, අනිවාර්යයෙන්ම. පරිශීලකයින්ගේ ආරක්ෂාව සුරකිමින්, කිසිදු බාධාවකින් තොර අත්දැකීම ගැන අපි නිරතුරුවම සැලකිලිමත් වෙනවා. මෙහි ඇති දැන්වීම් අවම මට්ටමක පවත්වාගෙන යන අතර, හානිකර Pop-ups හෝ සැකකටයුතු දේවල් ඉවත් කිරීමට අපි නිතරම වෙහෙසෙනවා. යම් හෙයකින් ඔබට නුසුදුසු දැන්වීමක් දිස් වුවහොත් අපට ඒ බව දන්වන්න, අපි එය ඉක්මනින්ම​ ඉවත් කරන්නම්.</p>
                    </section>
                    <section>
                        <h2>3/ නොමිලේ Anime බලන්න $site_name හොඳම තැන වෙන්නෙ ඇයි?</h2>
                        <p>We studied many anime platforms and built $site_name to keep the best features while removing clutter and annoyance. The result? A platform made <strong>by anime fans, for anime fans</strong>. Here’s a deeper look at why $site_name stands above the rest:</p>
                        <ul>
                            <li><strong>Massive Library:</strong> Whether you’re in the mood for heart-pounding action, romantic drama, laugh-out-loud comedy, or mind-bending fantasy, $site_name has it all. From shonen, shojo, and seinen to isekai, our library covers every anime niche imaginable.</li>
                            <li><strong>Complete Series & Movies:</strong> Never worry about missing an episode. $site_name ensures entire seasons and full-length anime movies are available. You can binge from start to finish without hunting for other sources.</li>
                            <li><strong>Subbed & Dubbed:</strong> Easily switch between English-subbed and dubbed versions.</li>
                            <li><strong>SD to HD Quality:</strong> Stream up to 1080p with adaptive quality settings for different connection speeds (480p, 720p, 1080p).</li>
                            <li><strong>Optimized for Speed:</strong> Our servers are optimized for ultra-fast streaming with minimal buffering, ensure a uninterrupted viewing experience.</li>
                            <li><strong>Daily Updates:</strong> Anime never stops, and neither do we. $site_name is updated daily with the latest episodes, seasonal releases, and fan-requested titles, making sure you’re always up-to-date with what’s trending.</li>
                            <li><strong>Device Compatibility:</strong> Stream anywhere — desktop, tablet, or mobile — with our fully responsive interface. Whether you’re at home or on the go, your favorite anime is just a click away.</li>
                        </ul>
                    </section>
                    <section>
                        <h2>4/ $site_name එකෙන් Anime Download කරන්න පුළුවන්ද?</h2>
                        <p>ඔව්. Internet නැතිව වුනත් බලන්න ඕනම Anime Episode එකක් හෝ Movie එකක් පහසුවෙන්ම $site_name එකෙන් Download කරගන්න පුලුවන්.</p>
                    </section>
                    <section>
                        <h2>5/ Anime වලට ආස කරන​ ඔයාල වෙනුවෙන් අපේ පොරොන්දුව</h2>
                        <p>$site_name කියන්නේ නිකම්ම වෙබ් අඩවියක් විතරක් නෙවෙයි, එය Anime රසිකයන්ගේ එකමුතුවක්. ඔබේ අදහස් වලට ඇහුම්කන් දෙමින්, වෙළඳ දැන්වීම් කරදරයක් නැතිව විශ්වාසවන්ත සේවාවක් සැපයීම අපේ අරමුණයි.</p>
                        <p>ඔබත් විශ්වාසවන්ත සහ වේගවත් Anime අඩවියක් සොයනවා නම්, අදම <strong>$site_name</strong> අත්හදා බලන්න.</p>
                    </section>";
                } else {
                    echo "
                    <section>
                        <h1>$site_name - Watch Anime Online Free in HD quality</h1>
                        <p><strong>$site_name</strong> is your free, safe, and fast destination for streaming and downloading subbed or dubbed anime in high quality — no registration, no hidden fees.</p>
                    </section>
                    <section>
                        <h2>1/ What is $site_name?</h2>
                        <p><strong>$site_name</strong> is a free anime streaming website where you can <strong>watch and download subbed or dubbed anime</strong> in Full HD without registration. From action and romance to comedy and fantasy, $site_name offers a wide selection of titles for every anime fan.</p>
                    </section>
                    <section>
                        <h2>2/ Is $site_name safe?</h2>
                        <p>Yes. $site_name prioritizes user safety and a clean viewing experience. Ads are kept minimal and monitored around the clock to reduce harmful pop-ups and suspicious content. If you spot an unsafe ad, please report it and we'll remove it promptly.</p>
                    </section>
                    <section>
                        <h2>3/ Why $site_name is the best place to watch anime online free</h2>
                        <p>We studied many anime platforms and built $site_name to keep the best features while removing clutter and annoyance. The result? A platform made <strong>by anime fans, for anime fans</strong>. Here’s a deeper look at why $site_name stands above the rest:</p>
                        <ul>
                            <li><strong>Massive Library:</strong> Whether you’re in the mood for heart-pounding action, romantic drama, laugh-out-loud comedy, or mind-bending fantasy, $site_name has it all. From shonen, shojo, and seinen to isekai, our library covers every anime niche imaginable.</li>
                            <li><strong>Complete Series & Movies:</strong> Never worry about missing an episode. $site_name ensures entire seasons and full-length anime movies are available. You can binge from start to finish without hunting for other sources.</li>
                            <li><strong>Subbed & Dubbed:</strong> Easily switch between English-subbed and dubbed versions.</li>
                            <li><strong>SD to HD Quality:</strong> Stream up to 1080p with adaptive quality settings for different connection speeds (480p, 720p, 1080p).</li>
                            <li><strong>Optimized for Speed:</strong> Our servers are optimized for ultra-fast streaming with minimal buffering, ensure a uninterrupted viewing experience.</li>
                            <li><strong>Daily Updates:</strong> Anime never stops, and neither do we. $site_name is updated daily with the latest episodes, seasonal releases, and fan-requested titles, making sure you’re always up-to-date with what’s trending.</li>
                            <li><strong>Device Compatibility:</strong> Stream anywhere — desktop, tablet, or mobile — with our fully responsive interface. Whether you’re at home or on the go, your favorite anime is just a click away.</li>
                        </ul>
                    </section>
                    <section>
                        <h2>4/ Can I download anime from $site_name?</h2>
                        <p>Yes. $site_name supports downloading episodes and movies for offline viewing so you can watch your favorites even without an internet connection.</p>
                    </section>
                    <section>
                        <h2>5/ Our Promise to the Anime Community</h2>
                        <p>$site_name is more than a streaming site — it’s a community. We actively improve the platform, respond to user feedback, and maintain a reliable, ad-light environment for anime fans worldwide.</p>
                        <p>If you're looking for a trustworthy, fast, and safe anime streaming site, give <strong>$site_name</strong> a try. Bookmark us and start your next anime journey today.</p>
                    </section>";
                }
                ?>
            </div>

            <div class="r-panel">
                <?php include 'layout/sidebar/top.php' ?>
            </div>
        </div>
    </div>

    <?php include "layout/footer/def.php" ?>
</body>

</html>