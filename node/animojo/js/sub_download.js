const btn = document.getElementsByClassName('download-btn')[0];

function open(l, t = false) {
    const a = document.createElement('a');
    a.href = l;
    a.style.display = 'none';
    if (t) { a.target = '_blank'; }
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
}

let c = 0;
let f_data = false;
let click = false;

btn.addEventListener('click', () => {
    if (c < 1) {
        open(sub_download_al, true);
        c++;
        return
    }

    if (click) { return }
    click = true;
    btn.innerHTML += '<img src="/images/assets/buffer-c.gif" id="dl-btn-l">';

    function run(d) {
        document.getElementById('dl-btn-l').remove();
        if (d.status) {
            open(d.d);
            click = false;
        } else {
            btn.innerHTML = `<span class="bi i1">${d.msg}</span>`;
        }
    }

    if (f_data) {
        run(f_data);
    } else {
        const l = new URLSearchParams(window.location.search);
        fetch('download.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams(
                {
                    b: l.get('b'),
                    f: l.get('f')
                })
        }).then(r => r.json()).then(d => {
            run(d);
            f_data = d;
        });
    }
})