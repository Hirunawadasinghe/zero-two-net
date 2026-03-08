document.addEventListener("DOMContentLoaded", () => {
    let data_list;
    const list_pre = document.getElementById('data-list');
    const search_bar = document.getElementById('az-search');

    function insert_data(d) {
        let t = '';
        for (let i = 0; i < d.length && i < 50; i++) {
            t += `<a href="/watch/${d[i].id}">${d[i].name + lang_str(d[i].lan)}</a>`;
        }
        list_pre.innerHTML = t;
    }

    fetch('/api/az-list.php').then(r => r.json()).then(d => {
        if (d.data) { data_list = d.data; insert_data(d.data); }
    });

    function lang_str(l) {
        let r = '';
        switch (l.toLowerCase()) {
            case 'japanese':
                r = '';
                break;
            case 'dual audio':
                r = ' (Dual Audio)';
                break;
            default:
                r = ` (${l} Dub)`;
                break;
        }
        return r;
    }

    search_bar.addEventListener('input', () => {
        list_pre.innerHTML = '';
        const q = search_bar.value.trim().toLowerCase();
        if (q === '') {
            insert_data(data_list);
            return;
        }
        let temp_list = [];
        [...data_list].forEach(e => {
            let start = null;
            let match = 0;
            for (let c = 0; c < e.name.length; c++) {
                if (e.name[c].toLowerCase() === q[match]) {
                    if (match === 0) { start = c; }
                    match++;
                    if (match === q.length) { break; }
                } else {
                    match = 0;
                    start = null;
                }
            }
            if (match === q.length && start !== null) {
                const end = start + match;
                temp_list.push({ ...e, name: `${e.name.slice(0, start)}<span class="highlight">${e.name.slice(start, end)}</span>${e.name.slice(end)}`, m: match });
            }
        });
        if (temp_list.length > 0) {
            temp_list = temp_list.sort((a, b) => b.m - a.m);
            insert_data(temp_list);
        }
    });
}, { once: true });