const page_title = document.getElementsByTagName("title")[0];
const iframe = document.getElementById("iframe-preview");
const player_prev_btn = document.getElementById('prev-btn');
const player_next_btn = document.getElementById('next-btn');
const player_download_opt = document.getElementById("download-opt");
const player_switch_opt = document.getElementById("switch-opt");
const playlist = document.getElementById('playlist');
const playlist_search = document.getElementById('pl-search');

const title_txt = page_title.outerText;
let source_keys, ep_title, playlist_range;
let episode = new URLSearchParams(window.location.search).get("episode");

window.addEventListener('load', () => {
    user_options(anime_details.id);

    fetch(`/api/anime-info.php?id=${anime_details.id}`)
        .then(r => r.json())
        .then(d => {
            if (d?.status) {
                document.getElementById('rating-text').innerText = 'Rating ' + d.data.IMDB_Rating;
                const c = document.getElementById('info-c');
                c.style.display = 'flex';
                for (const key in d.data) {
                    if (!['IMDB_Rating', 'Runtime'].includes(key)) {
                        c.innerHTML += `<div><span>${key}: </span>${d.data[key]}</div>`;
                    }
                }
            }
        });

    if (!anime_details.status) { return }

    // reconstruct source object
    source_keys = [];
    let recon_src = {};
    anime_details.source.url.forEach(e => {
        recon_src[e[0]] = {};
        for (const [host, path] of Object.entries(e[1])) {
            const h = anime_details.source.host[host];
            recon_src[e[0]][h[0]] = `https://${h[1]}${path}`;
        }
        source_keys.push(String(e[0]));
    });
    anime_details.source = recon_src;

    // select recently watched episode if an episode not altready selected or selected episode not exist
    if (!source_keys.includes(episode)) {
        const d = JSON.parse(localStorage.getItem('watchHistory')) || [];
        const f = d.find(e => e.id === anime_details.id);
        episode = f && source_keys.includes(f.ep) ? f.ep : source_keys[0];
    }

    create_playlist(0, source_keys.length - 1);
    load_episode(episode);

    window.addEventListener('popstate', () => {
        const url = new URL(window.location);
        const ep = url.searchParams.get('episode') || source_keys[0];
        load_episode(ep);
    });

    // episode search
    playlist_search.addEventListener('input', () => {
        let s = playlist_search.value.trim();
        if (s) {
            let t = '';
            source_keys.filter(e => e.includes(s)).forEach(ep => {
                t += playlist_itm(ep);
            });
            playlist.innerHTML = t;
        } else {
            create_playlist(playlist_range.s, playlist_range.e);
        }
    });

    // get episode titles
    fetch('/api/ep-name.php?id=' + anime_details.id)
        .then(r => r.json())
        .then(d => {
            if (d?.status) {
                ep_title = d.data;
                create_playlist(playlist_range.s, playlist_range.e);
            }
        });
});

const ep_sector_c = document.getElementById("episode-sector");

function create_playlist(start, end) {
    if (playlist_range) {
        let t = '';
        for (let i = start; i <= end; i++) {
            t += playlist_itm(source_keys[i]);
        }
        playlist.innerHTML = t;
    } else {
        if (end <= 100) {
            playlist_range = { s: start, e: end };
            create_playlist(start, end);
        } else {
            for (let i = 0; i < Math.ceil(end / 100); i++) {
                const btn_start = i * 100;
                const btn_end = Math.min((i + 1) * 100 - 1, end);
                ep_sector_c.innerHTML += `<button data-id="${i}" onclick="select_pl_sect(${btn_start},${btn_end},${i})">${source_keys[btn_start]} - ${source_keys[btn_end]}</button>`;
                const idx = source_keys.findIndex(ep => ep === episode);
                if (idx >= btn_start && idx <= btn_end) {
                    select_pl_sect(btn_start, btn_end, i);
                }
            }
            ep_sector_c.style.display = 'grid';
        }
    }
}

function select_pl_sect(s, e, id) {
    playlist_range = { s: s, e: e };
    Array.from(ep_sector_c.children).forEach(i => {
        i.classList.toggle('select', i.dataset.id == id);
    });
    create_playlist(s, e);
}

function playlist_itm(ep) {
    let t = ep_title && ep_title[ep] ? ep_title[ep] : `Episode ${ep}`;
    return `<div data-playlist="${ep}" class="playlist-item${ep === episode ? ' select' : ''}" onclick="load_episode('${ep}')"><div><span>${ep}</span><p title="${t}">${t}</p></div><i class="fa-solid fa-circle-play"></i></div>`;
}

let ep_watched = [];
let rd_pup_id, rd_pup_timer;
const ep_view_out = document.getElementById('ep-view-count');

function load_episode(ep) {
    if (load_episode.recent === ep) { return }
    load_episode.recent = episode = ep;

    const url = new URL(window.location);
    if (episode === source_keys[0]) url.searchParams.delete('episode');
    else url.searchParams.set('episode', episode);
    history.pushState({}, '', url);

    page_title.innerText = `${`Ep ${episode}`} - ${title_txt}`;

    // select playlist episode
    document.querySelectorAll('.playlist-item').forEach(e => {
        e.classList.toggle('select', e.dataset.playlist === episode);
        if (e.dataset.playlist === episode) {
            e.scrollIntoView({
                block: 'nearest',
                behavior: 'smooth'
            });
        }
    });

    const ep_index = source_keys.findIndex(i => i === episode);
    if (ep_index > 0) {
        player_prev_btn.onclick = () => { load_episode(source_keys[ep_index - 1]); }
    }
    player_prev_btn.classList.toggle('show', ep_index > 0);
    if (ep_index < source_keys.length - 1) {
        player_next_btn.onclick = () => { load_episode(source_keys[ep_index + 1]); }
    }
    player_next_btn.classList.toggle('show', ep_index < source_keys.length - 1);

    let select;
    function select_player(h, l) {
        select = true;
        set_player(h, l);

        update_watch_history(anime_details.id);
        update_user_status(anime_details.id, anime_details.title, episode);

        const f = ep_watched.find(i => i.ep === episode);
        if (f) {
            ep_view_out.innerText = f.d;
        } else {
            fetch(`/api/view-count.php?id=${anime_details.id}&ep=${episode}`)
                .then(r => r.json())
                .then(d => {
                    if (d?.status) {
                        ep_watched.push({ 'ep': episode, 'd': d.data });
                        ep_view_out.innerText = d.data;
                    }
                })
        }

        // video ad script
        if (rd_pup_id) {
            document.getElementById(rd_pup_id).remove();
            rd_pup_id = null;
        }
        clearTimeout(rd_pup_timer);
        rd_pup_timer = setTimeout(() => {
            rd_pup_id = 'rp-' + Math.round(Math.random() * 10000);
            const re = Object.assign(document.createElement('div'), { id: rd_pup_id, className: rd_pup_id, style: 'position:absolute;z-index:1;top:0;bottom:0;left:0;right:0;' });
            re.onclick = () => {
                window.open(vid_ad_link, '_blank');
                rd_pup_id = null;
                re.remove();
            };
            document.querySelector('.player-preview').appendChild(re);
        }, 60000);
    }

    const entries = Object.entries(anime_details.source[episode]);
    const player = new URLSearchParams(window.location.search).get("player") || '';
    player_switch_opt.innerHTML = player_download_opt.innerHTML = '';

    for (const [host_name, link] of entries) {
        // player switch button
        player_switch_opt.innerHTML += `<button data-player="${host_name}" onclick="set_player('${host_name}', '${link}', true)">${host_name}</button>`;

        // download button
        const url = new URL(link);
        let l;
        switch (url.hostname) {
            case 'hgbazooka.com':
                l = link.replace("/e/", "/f/");
                break;
            case 'dood.wf':
                l = link.replace("/e/", "/d/");
                break;
            case 'alions.pro':
                l = link.replace("/v/", "/d/");
                break;
            case 'vdn.rpmvip.com':
            case 'vdn.rpmvid.com':
                l = `${link}&dl=1`;
                break;
        }
        if (l) {
            player_download_opt.innerHTML += `<a href="${l}" target="_blank">${host_name}</a>`;
        }

        if (player == host_name) {
            select_player(host_name, link);
        }
    }
    // set the player if not already set
    if (!select) { select_player(entries[0][0], entries[0][1]); }
}

function set_player(h, l, s = false) {
    if (set_player.recent !== l) {
        set_player.recent = l;

        document.querySelectorAll('[data-player]').forEach(e => {
            e.classList.toggle('select', e.dataset.player === h);
        });

        iframe.src = '';
        setTimeout(() => {
            if (set_player.recent === l) {
                iframe.src = l;
                if (s) {
                    const url = new URL(window.location);
                    url.searchParams.set('player', h);
                    history.pushState({}, '', url);
                }
            }
        }, 200);
    }
}

function update_user_status(id, title, ep) {
    let d = JSON.parse(localStorage.getItem('userStatus')) || false;
    if (d) {
        try {
            d.recent_activity = Date.now();
            const el = d.watched.find(e => e.i === id);
            setTimeout(() => {
                if (el) {
                    el.d = Date.now();
                    if (el.e !== ep) {
                        d.total_ep += 1;
                        if (el.e < ep) {
                            el.e = ep;
                        }
                    }
                } else {
                    d.total_ep += 1;
                    d.watched.push({ "i": id, "n": title, "e": ep, "t": 1, "d": Date.now() });
                    if (d.watched.length > 20) {
                        d.watched = d.watched.slice(1, 21);
                    }
                }
            }, 60000);
        } catch (err) {
            console.error('UserStatus error:', err);
            // reset the userStatus if it's not acording to the newest version structure
            localStorage.removeItem('userStatus');
            update_user_status(id, title, ep);
            return
        }
    } else {
        d = {
            "recent_activity": Date.now(), // recent activity
            "total_ep": 1, // total episodes watched
            "watch_time": 0, // total watch time
            "watched": [ // watched episodes
                {
                    "i": id, // id
                    "n": title, // video title
                    "e": ep, // recently watched episode
                    "t": 0, // total watch time
                    "d": Date.now() // recently watched date
                }
            ]
        }
    }
    localStorage.setItem('userStatus', JSON.stringify(d));

    const streaming_start = Date.now();
    window.addEventListener('beforeunload', () => {
        const watch_time = (Date.now() - streaming_start) / 1000;
        if (watch_time >= 60) {
            let d = JSON.parse(localStorage.getItem('userStatus'));
            d.watch_time += Math.floor(watch_time / 60);
            const el = d.watched.find(e => e.i === id);
            el.t += Math.floor(watch_time / 60);
            localStorage.setItem('userStatus', JSON.stringify(d));
        }
    })
}

function user_options(id) {
    // watch status script
    const ws_btn = document.querySelectorAll('.ws-btn');

    function select_ws_btn(i = null) {
        ws_btn.forEach(e => {
            e.classList.toggle('select', e.dataset.id == i);
        });
        document.getElementById('watch-status-btn').classList.toggle('select', i);
    }

    const ws_f = (JSON.parse(localStorage.getItem("watchStatus")) || []).find(e => e.id === id);
    if (ws_f) {
        select_ws_btn(Object.entries(watch_status_list).find(e => e[1] === ws_f.s)[0]);
    }

    ws_btn.forEach(e => {
        e.addEventListener('click', () => {
            if (e.classList.contains('select')) {
                update_watch_status(id, 'remove');
                select_ws_btn();
            } else {
                update_watch_status(id, 'add', e.dataset.id);
                select_ws_btn(e.dataset.id);
            }
        });
    });

    // rating script
    const rating_stars = document.getElementById("ratings-stars");
    function set_rating(r) {
        rating_stars.style.setProperty('--rating', `${r > 0 ? r / 2 : 0}`);
    }
    function get_rating() {
        const d = JSON.parse(localStorage.getItem("localScore")) || [];
        return d.find(e => e.id === id)?.s || 0;
    }
    set_rating(get_rating());

    rating_stars.addEventListener('mousemove', ({ offsetX }) => {
        const sw = rating_stars.offsetWidth / 10;
        const idx = Math.ceil(offsetX / sw);
        set_rating(idx);
        document.getElementById("rating-tooltip").innerText = `Rating: ${idx}`;
        document.getElementById("rating-tooltip").style.left = `${sw * idx - 52}px`;
    });

    rating_stars.addEventListener('mouseleave', () => set_rating(get_rating()));
    rating_stars.addEventListener('click', ({ offsetX }) => {
        const d = JSON.parse(localStorage.getItem("localScore")) || [];
        const s_e = d.find(e => e.id === id);
        let s = s_e ? s_e.s : 0;
        const sector_width = rating_stars.offsetWidth / 10;
        const clickedScore = Math.ceil(offsetX / sector_width);
        s = s === clickedScore ? 0 : clickedScore;
        let updatedScores = d.filter(e => e.id !== id);
        if (s > 0) {
            updatedScores.push({ "id": id, "s": s });
        }
        localStorage.setItem('localScore', JSON.stringify(updatedScores));
        set_rating(s);
    });

    // bookmark script
    const bookmark_btn = document.getElementById('bookmark-btn');

    function toggle_bookmark(c) {
        bookmark_btn.innerHTML = c ? `<i class="fa-solid fa-bookmark"></i>Bookmarked` : `<i class="fa-regular fa-bookmark"></i>Bookmark`;
    }
    toggle_bookmark((JSON.parse(localStorage.getItem('bookmarks')) || []).find(e => e.id === id));

    bookmark_btn.addEventListener('click', () => {
        let d = JSON.parse(localStorage.getItem('bookmarks')) || [];
        const f = d.find(e => e.id === id);
        if (f) {
            d = d.filter(e => e.id !== id);
        } else {
            d.push({ "id": id });
        }
        localStorage.setItem('bookmarks', JSON.stringify(d));
        toggle_bookmark(!f);
    });
}

function update_watch_history(id) {
    let d = JSON.parse(localStorage.getItem("watchHistory")) || [];
    const ed = { "id": id, "ep": episode };
    if (d) {
        d = d.filter(i => i.id !== id);
        d.push(ed);
        if (d.length > 30) {
            d = d.slice(1, 31);
        }
    } else {
        d = [ed];
    }
    localStorage.setItem('watchHistory', JSON.stringify(d));
}

const popup_window = document.getElementById('popup-window');
const popup_window_itm = document.querySelectorAll('.popup-window-itm');
function toggle_popup_window(popup = false) {
    if (!popup) { popup_window.style.display = 'none'; }
    else {
        popup_window_itm.forEach(e => {
            e.style.display = e.id === popup ? 'flex' : 'none';
        });
        popup_window.style.display = 'flex';
    }
}

// full screen
document.getElementById('player-expland-btn').onclick = () => {
    if (iframe.requestFullscreen) { // standard
        iframe.requestFullscreen();
    } else if (iframe.mozRequestFullScreen) { // firefox
        iframe.mozRequestFullScreen();
    } else if (iframe.webkitRequestFullscreen) { // chrome, safari, opera
        iframe.webkitRequestFullscreen();
    } else if (iframe.msRequestFullscreen) { // internet explorer, edge
        iframe.msRequestFullscreen();
    }
};

// toggle light
function toggle_light() {
    toggle_light.condition = !toggle_light.condition;
    document.getElementById('player-iframe-w').classList.toggle('light-off', toggle_light.condition);
}
document.getElementById('player-light-btn').addEventListener('click', toggle_light);
document.getElementById('player-back-drop').addEventListener('click', toggle_light);

// episode report script
const report_in = document.getElementById('report-opt');
const report_other_in = document.getElementById('report-other-opt');
const report_email_in = document.getElementById('report-email');

report_in.onchange = () => { document.getElementById('report-other-opt-c').classList.remove('error-msg'); };
report_other_in.onchange = () => { document.getElementById('report-other-opt-c').classList.remove('error-msg'); };
report_email_in.onchange = () => { document.getElementById('report-email-c').classList.remove('error-msg'); };

document.getElementById('player-report').addEventListener('click', () => { toggle_popup_window('report-popup'); });
document.getElementById("report-popup").addEventListener('submit', (e) => {
    e.preventDefault();
    const input = report_in.value.trim();
    const other_input = report_other_in.value.trim();
    const email = report_email_in.value.trim();
    let r = { Url: window.location.href };
    if (input) { r['Error'] = input; }
    else if (other_input) { r['Other error'] = other_input; }
    else { document.getElementById('report-other-opt-c').classList.add('error-msg'); return; }
    if (email.includes('@')) { r['Email'] = email; }
    else { document.getElementById('report-email-c').classList.add('error-msg'); return; }
    fetch(`/api/post/report.php?text=${encodeURIComponent(JSON.stringify(r))}`);
    toggle_popup_window();
});

// countdown to next episode
const next_ep_pg = document.getElementById('next-sch-p');
if (next_ep_pg) {
    const next_ep_t = document.getElementById('next-sch-s');
    const t = next_ep_t.dataset.time;

    (function run() {
        let diff = t - Math.floor(Date.now() / 1000);
        if (diff <= 0) {
            next_ep_pg.innerText = 'Episode Released';
            return;
        }

        const d = Math.floor(diff / (24 * 3600));
        diff %= 24 * 3600;
        const h = Math.floor(diff / 3600);
        diff %= 3600;
        const m = Math.floor(diff / 60);

        let r = [];
        if (d > 0) { r.push(`${d} Days`); }
        if (h > 0) { r.push(`${h} Hours`); }
        if (m > 0) { r.push(`${m} Min`); }
        next_ep_t.innerText = r.join(' ');

        setTimeout(run, 60000);
    })();
}

// comment section script
const cmt_section = document.getElementById('comments');
const cmt_post_btn = document.getElementById('cmt-post-btn');
const cmt_no_msg = document.getElementById('no-cmt-msg');
const cmt_error_msg = document.getElementById('cmt-error-msg');
let cmt_con = false;

const cs_children = cmt_section.children;
if (cs_children.length > 9) {
    for (let i = 9; i < cs_children.length; i++) {
        cs_children[i].style.display = 'none';
    }
    cmt_section.innerHTML += '<button id="vm-btn"><i class="fa-solid fa-caret-down"></i>View more</button>';
    document.getElementById('vm-btn').addEventListener('click', (e) => {
        for (let i = 9; i < cs_children.length; i++) {
            cs_children[i].style.display = 'flex';
        }
        e.target.remove();
    });
}

document.getElementById('comment-form').addEventListener('submit', (e) => {
    e.preventDefault();
    if (cmt_con) {
        return;
    }
    cmt_con = true;

    const form = new FormData(e.target);
    form.append('page_id', anime_details.id);

    cmt_error_msg.style.display = 'none';
    cmt_post_btn.classList.add('load');

    fetch('/api/comment.php', { method: 'POST', body: form })
        .then(r => r.json())
        .then(d => {
            if (d?.status) {
                e.target.reset();
                cmt_section.insertAdjacentHTML('afterbegin', `<div class="comment"><div class="comment-split"><img src="https://i.postimg.cc/6pQm2hNs/none-profile.jpg" alt="${d.data.username}" loading="lazy" class="comment-u-img"><div><div class="header"><h3>${d.data.username}</h3><i class="dot"></i><span class="time">Just Now</span></div><p>${d.data.comment}</p></div></div></div>`);
                cmt_no_msg ? cmt_no_msg.style.display = 'none' : '';
                cmt_con = false;
            } else {
                cmt_error_msg.innerText = d?.msg || 'Error posting the comment';
                cmt_error_msg.style.display = 'block';
            }
            cmt_post_btn.classList.remove('load');
        });
});