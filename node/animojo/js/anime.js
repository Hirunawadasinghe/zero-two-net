let ed_con = false;
const ed_btn = document.getElementById('entry-des-btn');
ed_btn.addEventListener('click', () => {
    ed_con = !ed_con;
    document.getElementById('entry-des').classList.toggle('expand', ed_con);
    ed_btn.innerText = ed_con ? 'Fewer Details' : 'More Details';
});

const ws_btns = document.querySelectorAll('.ws-btn');

function select_ws_btn(i = null) {
    ws_btns.forEach(e => {
        e.classList.toggle('select', e.dataset.id == i);
    });
}

window.addEventListener('load', () => {
    const ws_f = (JSON.parse(localStorage.getItem("watchStatus")) || []).find(e => e.id === anime_details.id);
    if (ws_f) {
        select_ws_btn(Object.entries(watch_status_list).find(e => e[1] === ws_f.s)[0]);
    }
});

ws_btns.forEach(e => {
    e.addEventListener('click', () => {
        if (e.classList.contains('select')) {
            update_watch_status(anime_details.id, 'remove');
            select_ws_btn();
        } else {
            update_watch_status(anime_details.id, 'add', e.dataset.id);
            select_ws_btn(e.dataset.id);
        }
    });
});

fetch(`/api/characters.php?id=${anime_details.id}`)
    .then(r => r.json())
    .then(d => {
        if (d?.status) {
            const sect_w = document.getElementById('character-sect-w');
            const sect = document.getElementById('character-sect');

            d.data.forEach(e => {
                if (e.voice_actors.length > 0) {
                    const f = e.voice_actors.find(i => i.language == 'japanese') || e.voice_actors[0];
                    sect.innerHTML += `<div class="character-card"><div class="back" style="background-image: url('${e.image === 'https://s4.anilist.co/file/anilistcdn/character/medium/default.jpg' ? '' : e.image}')"></div><div class="con"><div class="img-c"><img src="${f.image}" alt="${f.name}" loading="lazy"></div><div><p>${f.name}</p><span>${e.name} (${e.role})</span></div></div></div>`;
                }
            });

            if (sect.children.length) {
                sect_w.style.display = 'block';

                const cs = getComputedStyle(sect);
                const row = cs.gridTemplateRows.split(' ');

                if (row.length > 3) {
                    sect.style.maxHeight = `${parseFloat(row[0]) + parseFloat(row[1]) + parseFloat(row[2]) + parseFloat(cs.gap) * 2}px`;
                    sect.innerHTML += '<div class="vm-w"><p id="vm-btn">View More</p></div>';
                    let con = false;
                    document.getElementById('vm-btn').addEventListener('click', () => {
                        con = !con;
                        sect.classList.toggle('expand', con);
                    });
                }
            }
        }
    });

fetch(`/api/relations.php?id=${anime_details.id}`)
    .then(r => r.json())
    .then(d => {
        if (d?.status) {
            d.data.forEach(e => {
                loadCard(e, 'related-sect');
            });
            document.getElementById('related-sect-w').style.display = 'block';
        }
    });