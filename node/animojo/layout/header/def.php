<header class="header-section-w">
    <div class="header-section-c">
        <div class="header-section">
            <div class="header-l">
                <a href="/home" class="header-logo"><img src="https://i.ibb.co/HLS4JYY0/logo.png" alt="<?= $site_name ?>"
                        loading="lazy"></a>
                <div tabindex="0" class="main-search-w">
                    <form action="/search" class="search-c">
                        <input type="text" name="text" autocomplete="off" placeholder="Search anime..." required
                            id="main-search">
                        <div>
                            <button type="submit" aria-label="search anime">
                                <i class="fas fa-search"></i>
                            </button>
                            <a href="/search">Filter</a>
                        </div>
                    </form>
                    <nav id="search-suggest"></nav>
                </div>
            </div>

            <div class="header-r">
                <nav class="navigation-list">
                    <a href="/genre" class="nav-list-itm">Genres</a>
                    <a href="/arcbot" class="nav-list-itm">AI Chat</a>
                    <a href="/az-list" class="nav-list-itm">AZ List</a>
                    <a href="/my-lists" class="nav-list-itm">My Lists</a>
                </nav>
                <div class="header-btns">
                    <a href="/search" aria-label="Go to search" class="search">
                        <i class="fas fa-search"></i>
                    </a>
                    <div class="user" id="user-menu-btn">
                        <div class="img">
                            <img src="https://i.postimg.cc/6pQm2hNs/none-profile.jpg" alt="user-image">
                        </div>
                        <i class="fa-solid fa-caret-down"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="menu-panel">
        <div class="back" onclick="toggle_user_menu()"></div>
        <div class="con">
            <div class="h">
                <div class="menu-pan-lg">
                    <img src="https://i.postimg.cc/6pQm2hNs/none-profile.jpg" alt="user-image">
                </div>
                <h4 class="menu-pan-un">Anonymous User</h4>
            </div>
            <span class="hr"></span>
            <div class="btn-c">
                <a href="/my-lists?p=bookmarks" class="itm"><i class="fa-regular fa-bookmark"></i>Bookmarks</a>
                <a href="/my-lists?p=watching" class="itm"><i class="fa-solid fa-list-ul"></i>Watching</a>
                <a href="/my-lists?p=history" class="itm"><i class="fa-solid fa-clock-rotate-left"></i>History</a>
            </div>
            <span class="hr"></span>
            <div class="btn-c">
                <a href="/search" class="itm"><i class="fa-solid fa-magnifying-glass"></i>Search</a>
                <a href="/arcbot" class="itm"><i class="fa-regular fa-comment"></i>AI Chat</a>
            </div>
        </div>
    </div>
</header>

<div id="totop"><i class="fa-solid fa-angle-up"></i></div>

<div id="qtip">
    <div id="qtip-load" style="display: flex; justify-content: center;"><img src="/images/assets/buffer-c.gif"></div>
    <div id="qtip-con"></div>
</div>

<?php
// get user language
if (!isset($_COOKIE['preferredLanguage']))
    echo '<form class="lang-popup" method="POST"><div class="lang-msg-box"><h3>Select your language</h3><p>Please select your preferred language to continue. Some features may differ based on your selection.</p><div><button type="submit" name="preferredLanguage" value="en"><img src="/images/assets/uk-flag.png" alt="english">English</button><button type="submit" name="preferredLanguage" value="si"><img src="/images/assets/lk-flag.png" alt="sinhala">Sinhala</button></div></div></form>';

// christmas animation
if ((int)date('n') === 12) {
    include_once $_SERVER['DOCUMENT_ROOT'] . '/_inc/minify.php';
    echo '<style>#snow-canvas{position:fixed;inset:0;pointer-events:none;z-index:-1;}</style><canvas id="snow-canvas"></canvas><script>' . minify($_SERVER['DOCUMENT_ROOT'] . '/js/snow.js').'</script>';
}
?>