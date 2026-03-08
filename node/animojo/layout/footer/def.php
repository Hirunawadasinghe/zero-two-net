<script>
    <?php
    echo 'const advance_settings=' . json_encode(['tags' => $advance_settings['tags']]) . ';';
    echo minify($_SERVER['DOCUMENT_ROOT'] . '/js/qtip.js');
    echo minify($_SERVER['DOCUMENT_ROOT'] . '/js/script.js');
    ?>
</script>
<footer>
    <div class="footer-w">
        <div>
            <p>Pages</p>
            <ul>
                <li><a href="/arcbot">AI Chat</a></li>
                <li><a href="/az-list">AZ List</a></li>
                <li><a href="/genre">Genres</a></li>
                <li><a href="/my-lists">My Lists</a></li>
                <li><a href="/request">Request</a></li>
                <li><a href="/sitemaps/sitemap.php">Sitemap</a></li>
            </ul>
        </div>
        <div>
            <p>Community</p>
            <ul>
                <li><a href="https://t.me/animojo_chat" target="_blank">Telegram Chat</a></li>
                <li><a href="https://web.facebook.com/groups/animearcadia" target="_blank">Facebook Group</a></li>
            </ul>
        </div>
        <div>
            <p>Other Links</p>
            <ul>
                <li><a href="/contact">Contact</a></li>
                <li><a href="https://monetag.com/?ref_id=ziJJ" target="_blank">Earn from Ads</a></li>
            </ul>
        </div>
        <div>
            <p>Social Media</p>
            <span>
                <a href="https://www.facebook.com/profile.php?id=61582445330626" aria-label="follow us on facebook"
                    target="_blank"><i class="fa-brands fa-square-facebook"></i></a>
                <a href="#" aria-label="follow us on instagram" target="_blank"><i
                        class="fa-brands fa-square-instagram"></i></a>
                <a href="https://t.me/animojo_lk" aria-label="join with us on telegram" target="_blank"><i
                        class="fa-brands fa-telegram"></i></a>
                <a href="https://whatsapp.com/channel/0029VbB5V6S3WHTXuJZGGd0J" aria-label="join with us on whatsapp"
                    target="_blank"><i class="fa-brands fa-square-whatsapp"></i></a>
            </span>
        </div>
        <div class="f-btm">
            <small>Copyright © <?= $site_name ?>. All Rights Reserved.</small>
            <small>This site does not store any files on its server. All contents are provided by non-affiliated third
                parties.</small>
        </div>
    </div>
</footer>