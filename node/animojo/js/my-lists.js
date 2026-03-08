const list_btns = document.querySelectorAll('.title-btn');
const alt_text = document.getElementById("section-alt-text");

function get_store_data(s) { return JSON.parse(localStorage.getItem(s)) || false; }
function preview_cards(d) { alt_text.style.display = "none"; d.forEach(e => { loadCard(e, "cards-container"); }); }
function check_cache(i) {
    if (!cache_data[i]) { return false }
    cache_data[i] !== null ? preview_cards(cache_data[i]) : alt_text.innerHTML = 'api error';
    return true
}

let c_list = null;
let cache_data = {};
function loadList(list) {
    if (c_list === list) { return }
    c_list = list;
    list_btns.forEach(b => {
        b.classList.toggle('button-select', b.dataset.name === list);
        if (b.dataset.name === list) { window.history.replaceState(null, '', `${window.location.pathname}?p=${list}`); }
    });
    document.getElementById("cards-container").innerHTML = '';
    switch (list) {
        case 'completed':
            if (!check_cache('completed')) {
                let d = get_store_data("watchStatus");
                d = d ? d.filter(i => i.s === 'Completed') : [];
                if (d.length > 0) {
                    let in_d = [];
                    d.reverse().forEach(e => { in_d.push(e.id); });
                    fetch(`/api/anime.php?${new URLSearchParams({ link: 1, name: 1, language: 1, thumbnail_image: 1, episodes: 1, type: 1, tags: 1, subtitle: 1, d: JSON.stringify(in_d) })}`).then(r => r.json()).then(fd => {
                        cache_data['completed'] = fd.status ? fd.data : null;
                        check_cache('completed');
                    });
                } else {
                    alt_text.innerHTML = "Haven't you finished any anime yet? <br>Then it's time to fill it up with your masterpiece list.";
                    alt_text.style.display = "block";
                }
            }
            break;

        case 'bookmarks':
            if (!check_cache('bookmarks')) {
                const d = get_store_data("bookmarks");
                if (d.length > 0) {
                    let in_d = [];
                    d.reverse().forEach(e => { in_d.push(e.id); });
                    fetch(`/api/anime.php?${new URLSearchParams({ link: 1, name: 1, language: 1, thumbnail_image: 1, episodes: 1, type: 1, tags: 1, subtitle: 1, d: JSON.stringify(in_d) })}`).then(r => r.json()).then(fd => {
                        cache_data['bookmarks'] = fd.status ? fd.data : null;
                        check_cache('bookmarks');
                    });
                } else {
                    alt_text.innerHTML = "Your Watchlist is looking lonely, <br>Let’s add some epic anime to spice it up!";
                    alt_text.style.display = "block";
                }
            }
            break;

        case 'history':
            if (!check_cache('watchHistory')) {
                const d = get_store_data("watchHistory");
                if (d.length > 0) {
                    let in_d = [];
                    d.reverse().forEach(e => { in_d.push(e.id); });
                    fetch(`/api/anime.php?${new URLSearchParams({ link: 1, name: 1, language: 1, thumbnail_image: 1, episodes: 1, type: 1, tags: 1, subtitle: 1, d: JSON.stringify(in_d) })}`).then(r => r.json()).then(fd => {
                        cache_data['watchHistory'] = fd.status ? fd.data : null;
                        check_cache('watchHistory');
                    });
                } else {
                    alt_text.innerHTML = "Your history book is empty. <br>Start watching to fill this feed.";
                    alt_text.style.display = "block";
                }
            }
            break;

        default:
            if (!check_cache('watching')) {
                let d = get_store_data("watchStatus");
                d = d ? d.filter(i => i.s === 'Watching') : [];
                if (d.length > 0) {
                    let in_d = [];
                    d.reverse().forEach(e => { in_d.push(e.id); });
                    fetch(`/api/anime.php?${new URLSearchParams({ link: 1, name: 1, language: 1, thumbnail_image: 1, episodes: 1, type: 1, tags: 1, subtitle: 1, d: JSON.stringify(in_d) })}`).then(r => r.json()).then(fd => {
                        cache_data['watching'] = fd.status ? fd.data : null;
                        check_cache('watching');
                    });
                } else {
                    alt_text.innerHTML = "Looks like you're not watching anything right now. <br>Find an anime to let the adventure begin!";
                    alt_text.style.display = "block";
                }
            }
            break;
    }
}
loadList(new URLSearchParams(window.location.search).get('p') ?? "watching");