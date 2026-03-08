// create default card
function loadCard(e, c) {
    let l;
    switch (e.language.toLowerCase()) {
        case 'japanese':
            l = 'SUB';
            break;
        case 'english':
            l = 'DUB';
            break;
        case 'dual audio':
            l = 'Dual Audio';
            break;
        default:
            l = `${e.language} Dub`;
            break;
    }
    document.getElementById(c).innerHTML += `
    <div class="movie-card">
        <a href="/watch/${e.link}" class="thumbnail-container qtip-tag" data-id="${e.link.split('-').pop()}">
            <img src="${e.thumbnail_image}" alt="${e.name}" class="movie-card-img" loading="lazy" onerror="imgError(this)">
            <div class="movie-card-top-row">${e.tags.find(t => advance_settings.tags.adult_only.includes(t.toLowerCase())) ? '<span class="tl">18+</span>' : '<div></div>'}<span class="tr">${e.type}</span></div>
            <div class="movie-card-bottom-row"><div class="tick"><span class="ep">Ep ${e.episodes ?? '...'}</span>${e.subtitle == 'hard' ? '<span class="sub"><i class="fa-solid fa-closed-captioning"></i></span>' : ''}<span class="lan">${l}</span></div></div>
            <i class="fa-solid fa-play play-btn"></i>
        </a>
        <div class="movie-card-t"><a href="/anime/${e.link}" title="${e.name}">${e.name}</a></div>
    </div>`;
}

// search bar script
const main_search = document.getElementById('main-search');
const search_suggest = document.getElementById('search-suggest');

if (main_search) {
    let main_search_t;

    main_search.addEventListener('input', () => { if (main_search.value.trim().length < 2) { search_suggest.innerHTML = ''; } });

    setInterval(() => {
        const q = main_search.value.trim().toLowerCase();
        if (main_search_t === q) { return; }
        main_search_t = q;
        if (q.length < 2) { search_suggest.innerHTML = ''; return; }
        fetch(`/api/search.php?m=metaphone&text=${encodeURIComponent(q)}`).then(r => r.json()).then(d => {
            if (!d || d.status === false || d.data.length === 0) { search_suggest.innerHTML = '<span class="search-err-txt">no result found</span>'; return; }
            search_suggest.innerHTML = '';
            for (let i = 0; i < d.data.length && i < 5; i++) {
                const t = d.data[i].language.toLowerCase() === 'japanese' ? d.data[i].alt_name : d.data[i].name;
                search_suggest.innerHTML += `
                <a href="/watch/${d.data[i].link}" class="search-suggest-item ${i < 4 ? 'bl' : ''}">
                    <div class="image"><img onerror="imgError(this)" src="${d.data[i].thumbnail_image}"></div>
                    <div class="detail">
                        <h3 class="n1">${t}</h3>
                        ${d.data[i].alt_name !== d.data[i].name ? `<div class="n2">${d.data[i].alt_name}</div>` : ''}
                        <div class="info"><span>${d.data[i].release_year}</span><i class="dot"></i><span class="s">${d.data[i].type}</span><i class="dot"></i><span>${d.data[i].episodes} Ep</span></div>
                    </div>
                </a>`;
            }
            search_suggest.innerHTML += `<a href="/search?text=${main_search.value}" class="search-suggest-m-btn">View all resutls<i class="fa-solid fa-angle-right"></i></a>`;
        });
    }, 1000);
}

// header script
const header_w = document.getElementsByClassName('header-section-w')[0];

if (header_w) {
    let user_menu_con = false;
    function trigger_header_effect() { if (!user_menu_con) { header_w.classList.toggle('fixed', window.scrollY !== 0); } }
    trigger_header_effect();
    window.addEventListener('scroll', trigger_header_effect);

    function toggle_user_menu() {
        user_menu_con = !user_menu_con;
        header_w.classList.toggle('show', user_menu_con);
        if (user_menu_con) { header_w.classList.toggle('fixed', user_menu_con); } else { trigger_header_effect(); }
    }
    document.getElementById('user-menu-btn').addEventListener('click', toggle_user_menu);
}

// totop button script
const totop = document.getElementById('totop');
if (totop) {
    window.addEventListener('scroll', () => { totop.classList.toggle('show', (document.documentElement.scrollTop || document.body.scrollTop) > 300); });
    totop.addEventListener('click', () => { window.scrollTo({ top: 0, behavior: 'smooth' }); });
}

const watch_status_list = {
    1: 'Watching',
    2: 'On-Hold',
    3: 'Plan to watch',
    4: 'Dropped',
    5: 'Completed'
};

function update_watch_status(id, method, value = null) {
    let data = JSON.parse(localStorage.getItem('watchStatus')) || [];
    const e = data.find(i => i.id === id) || { id: id };
    data = data.filter(i => i.id !== id);
    switch (method) {
        case 'add':
            if (!watch_status_list[value]) {
                return;
            }
            e.s = watch_status_list[value];
            data.push(e);
            break;
        case 'remove':
            break;
        default:
            return;
    }
    localStorage.setItem('watchStatus', JSON.stringify(data));
}

// remove i=1 from url for sites hosted on infinityfree
if ((new URLSearchParams(window.location.search)).get('i')) {
    const l = new URL(window.location);
    l.searchParams.delete('i');
    window.history.replaceState({}, '', l);
}

// function act_on_geo(d) {
//     if (d.country_code === 'LK') {
//         document.querySelectorAll('.for-lk').forEach(e => {
//             e.classList.remove('for-lk')
//         })
//     }
// }

// const geo_data = JSON.parse(localStorage.getItem('geoData')) || null;
// if (geo_data) {
//     act_on_geo(geo_data);
// } else {
//     fetch('https://ipapi.co/json/')
//         .then(r => r.json())
//         .then(d => {
//             if (d) {
//                 act_on_geo(d);
//                 localStorage.setItem('geoData', JSON.stringify(d));
//             }
//         });
// }

// create section
let section_id = 0;
function create_section(t) {
    const id = `section-${section_id}`;
    section_id++;
    return {
        id: id,
        html: `<section><div class="section-title"><h2>${t}</h2></div><div class="movie-cards-container" id="${id}"></div></section>`
    };
}

// notice script
window.addEventListener('load', async () => {
    const c_time = 1000 * 60 * 60 * 12; // 12 hours
    let ld = JSON.parse(localStorage.getItem('notice')) || null;

    async function get() {
        const r = await fetch(`/api/notice.php`);
        const d = await r.json();
        return d?.status ? d.data : false;
    }

    function show(t) {
        document.body.innerHTML += `<div id="notice"><div><p>${t}</p><button id="notice-btn">Dismiss</button></div></div>`;
        document.getElementById('notice-btn').addEventListener('click', () => {
            localStorage.setItem('notice', JSON.stringify({ ...JSON.parse(localStorage.getItem('notice')), dismiss: true }));
            document.getElementById('notice').remove();
        });
    }

    if (ld) {
        if (ld.time > Date.now()) {
            if (!ld.dismiss) {
                show(ld.msg);
            }
        } else {
            const r = await get();
            if (r) {
                if (r.id === ld.id) {
                    ld.time = Date.now() + c_time;
                } else {
                    ld = { ...r, time: Date.now() + c_time };
                }
                localStorage.setItem('notice', JSON.stringify(ld));
                if (!ld.dismiss) {
                    show(ld.msg);
                }
            }
        }
    } else {
        const r = await get();
        if (r) {
            localStorage.setItem('notice', JSON.stringify({ ...r, time: Date.now() + c_time }));
            show(r.msg);
        }
    }
});