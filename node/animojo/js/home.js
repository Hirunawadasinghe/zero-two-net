// recommend
async function get_recommendation() {
    const ts = document.getElementById('tab-section');
    let watchHistory = JSON.parse(localStorage.getItem('watchHistory')) || false;
    let a = true;
    if (watchHistory && watchHistory.length > 0) {
        let ids = [];
        for (let i = watchHistory.length - 1; i >= watchHistory.length - 3 && i >= 0; i--) { ids.push(watchHistory[i].id); }
        const r = await fetch(`api/suggest.php?limit=5&d=${encodeURIComponent(JSON.stringify(ids))}`);
        const d = await r.json();
        if (d.status) {
            const s = create_section("Recommended for You");
            ts.innerHTML += s.html;
            d.data.forEach(e => loadCard(e, s.id));
            a = false;
        }
    }
    if (a) {
        const r = await fetch(`api/section.php?id=1`);
        const d = await r.json();
        if (d.status) {
            const s = create_section('Anime for Beginners');
            ts.innerHTML += s.html;
            d.data.forEach(e => loadCard(e, s.id));
        }
    }
}
get_recommendation();

// slides
let fav_slide = 0;
const fav_slide_c = document.getElementById('fave-slides-c');
const fav_slide_nav = document.querySelectorAll('.fave-slide-nav-c-i');
const fav_slide_count = document.getElementsByClassName('fave-slide-w').length;
let slide_timeout;
function fave_slide_push(i, auto = false) {
    fav_slide = i;
    fav_slide_c.style.transform = `translateX(${0 - (fav_slide_c.offsetWidth / fav_slide_count * i)}px)`;
    fav_slide_nav.forEach((e, c) => { e.classList.toggle('active', c === i); });
    clearTimeout(slide_timeout);
    if (auto) { slide_timeout = setTimeout(() => { fave_slide_push(fav_slide < fav_slide_count - 1 ? fav_slide + 1 : 0, true) }, 4000); }
}
fave_slide_push(0, true);

// schedule live time
function update_sch_time() {
    var t = new Date();
    var hour = t.getHours();
    var min = t.getMinutes();
    var sec = t.getSeconds();
    var am_pm = "AM";
    if (hour > 12) {
        hour -= 12;
        am_pm = "PM";
    }
    if (hour === 0) {
        hour = 12;
        am_pm = "AM";
    }
    hour = hour < 10 ? "0" + hour : hour;
    min = min < 10 ? "0" + min : min;
    sec = sec < 10 ? "0" + sec : sec;
    document.getElementById('sch-clock').innerHTML = `${hour}:${min}:${sec} ${am_pm}`;
}
setInterval(update_sch_time, 1000);

var sch_date = new Date();
var timezone = sch_date.toString().split(" ")[5];
document.getElementById('sch-timezone').innerHTML = `(${timezone.slice(0, timezone.length - 2)}:${timezone.slice(-2)})`;
document.getElementById('sch-date').innerHTML = sch_date.toLocaleDateString();

// schedule list
const sch_top_c_w = document.querySelector('.top-b-w');
const sch_top_c = document.getElementById('sch-top-h-w');
const sch_h_btns = document.querySelectorAll('.sch-h-d');
const sch_list = document.getElementById('sch-list');
const sch_sh_txt = document.getElementById('sch-sh-txt');
const sch_err = document.getElementById('sch-err');
let sch_scroll = sch_top_c_w.scrollWidth;
const sch_max_scroll = sch_top_c_w.scrollWidth;
let sch_tmp;

let sch_scroll_idx = 0;
const sch_btn_width = sch_h_btns[0].offsetWidth + (sch_top_c.offsetWidth - sch_h_btns[0].offsetWidth * sch_h_btns.length) / (sch_h_btns.length - 1);
function sch_scroll_f(n) {
    if (n > 0) {
        if (!(0 - sch_scroll + sch_top_c_w.clientWidth + 1 < sch_max_scroll)) { return }
    } else {
        if (sch_scroll_idx <= 0) { return }
    }
    sch_scroll_idx += n;
    sch_scroll = sch_btn_width * (0 - sch_scroll_idx);
    sch_top_c.style.left = sch_scroll + 'px';
}
document.getElementById('sch-s-l').addEventListener('click', () => { sch_scroll_f(-1) });
document.getElementById('sch-s-r').addEventListener('click', () => { sch_scroll_f(1) });

function load_sch_list(d) {
    sch_list.innerHTML = '';
    d.forEach(e => { sch_list.innerHTML += `<li><a href="${e.link}" class="sch-l-itm"><span>${e.time}</span><div><p>${e.name}</p><button><i class="fa-solid fa-play"></i>Episode ${e.episode}</button></div></a></li>`; });
}

let sch_list_expand_c;
function sch_list_expand(c = false) {
    sch_err.style.display = 'none';
    sch_list_expand_c = c;
    if (c) {
        load_sch_list(sch_tmp);
        sch_sh_txt.innerText = 'Show less';
    } else {
        load_sch_list((sch_tmp).slice(0, 7));
        sch_sh_txt.innerText = 'Show more';
    }
    sch_sh_txt.style.display = sch_tmp.length > 7 ? 'block' : 'none';
}

sch_sh_txt.addEventListener('click', () => {
    sch_list_expand_c = !sch_list_expand_c;
    sch_list_expand(sch_list_expand_c);
});

sch_h_btns.forEach((e, i) => {
    const c = () => {
        sch_h_btns.forEach(e => e.classList.remove('select'));
        sch_h_btns[i].classList.add('select');
        fetch(`/api/schedule.php?timezone=${new Date().getTimezoneOffset()}&date=${e.dataset.date}`).then(r => r.json()).then(d => {
            sch_list.innerHTML = '';
            if (d.status) {
                sch_tmp = d.data;
                sch_list_expand();
            } else {
                sch_err.style.display = 'block';
                sch_sh_txt.style.display = 'none';
            }
        });
    };
    e.addEventListener('click', () => { c(); });
    const d = new Date().getDate();
    if (i + 1 == d) {
        c();
        for (let i = 0; i < d - 1; i++) { sch_scroll_f(1); }
    }
});

// continue watching section
const cw_section = document.getElementById('cw-section');
const cw_section_w = document.getElementById('cw-section-w');
const watch_history = (JSON.parse(localStorage.getItem('watchHistory')) || []).reverse();
if (watch_history.length > 0) {
    let req = [];
    for (let i = 0; i < watch_history.length && i < 4; i++) { req.push(watch_history[i].id); }
    fetch(`/api/anime.php?${new URLSearchParams({ link: 1, name: 1, language: 1, thumbnail_image: 1, images: 1, mal_id: 1, d: JSON.stringify(req) })}`).then(r => r.json()).then(d => {
        if (!d.status) { return }
        const promises = d.data.map((e, c) => {
            let lan;
            switch (e.language.toLowerCase()) {
                case 'japanese':
                    lan = 'Subbed';
                    break;
                case 'english':
                    lan = 'Dubbed';
                    break;
                case 'dual audio':
                    lan = 'Dual Audio';
                    break;
                default:
                    lan = e.language + ' Dubbed';
                    break;
            }
            return fetch(`https://api.jikan.moe/v4/anime/${e.mal_id}/videos`)
                .then(r => r.json())
                .then(ep_d => {
                    let t = `E${watch_history[c].ep} - `;
                    let thumb;
                    const ep = ep_d?.data?.episodes?.find(ep => ep.episode.includes(`Episode ${watch_history[c].ep}`) || ep.episode.includes(`Ep. ${watch_history[c].ep}`));
                    if (ep) {
                        t += ep.title;
                        if (ep.images.jpg.image_url) { thumb = ep.images.jpg.image_url; }
                    } else {
                        t += e.name;
                    }
                    if (!thumb) {
                        thumb = e.images ? e.images.webp.large_image_url : e.thumbnail_image;
                    }
                    return `
                    <div class="hc-c" data-id="${watch_history[c].id}">
                        <a href="watch/${e.link}?episode=${watch_history[c].ep}" class="prv"><img src="${thumb}" alt="${t}" class="thumb-error" loading="lazy"><div class="play"><i class="fa-solid fa-play"></i></div><div class="ep"><span>Ep ${watch_history[c].ep}</span></div></a>
                        <div class="info">
                            <div class="t"><small>${e.name}</small><a href="watch/${e.link}?episode=${watch_history[c].ep}">${t}</a></div>
                            <div class="b"><span>${lan}</span><div class="popup-list" tabindex="0"><i class="fa-solid fa-ellipsis-vertical"></i><ul><li onclick="remove_from_watch_history('${watch_history[c].id}')">Remove</li></ul></div></div>
                        </div>
                    </div>`;
                });
        });
        Promise.all(promises).then(r => {
            cw_section_w.innerHTML = r.join('');
            cw_section.style.display = 'block';
        });
    });
}

function remove_from_watch_history(id) {
    let watch_history = JSON.parse(localStorage.getItem('watchHistory')) || [];
    watch_history = watch_history.filter(e => e.id !== id);
    localStorage.setItem('watchHistory', JSON.stringify(watch_history));
    document.querySelectorAll('.hc-c').forEach(e => { if (e.dataset.id === id) { e.remove(); } });
    if (cw_section_w.children.length == 0) { cw_section.style.display = 'none'; }
}