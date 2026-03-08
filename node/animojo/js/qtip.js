function isTouchDevice() { return ('ontouchstart' in window || navigator.maxTouchPoints > 0); }

const qtip = document.getElementById('qtip');
if (qtip && !isTouchDevice()) {
    let window_w, window_h;
    function init_window() {
        window_w = window.innerWidth;
        window_h = window.innerHeight;
    }
    init_window();
    window.addEventListener('resize', init_window);

    let qtip_cache = [];
    let qtip_select;
    function qtip_toggle(c) {
        qtip.classList.toggle('visible', c);
    }

    qtip.addEventListener('mouseenter', () => qtip_toggle(true));
    qtip.addEventListener('mouseleave', () => qtip_toggle(false));

    function setupQtip(c) {
        const id = c.dataset.id;
        let w, h, timer, scroll_listen;

        function run() {
            const f = qtip_cache.find(e => e.id === id);
            if (f) {
                if (f.status) {
                    qtip_toggle(true);
                    set_data(f);
                }
                return;
            }
            qtip_toggle(true);

            function init() {
                qtip.classList.add('init');
                w = qtip.offsetWidth;
                h = qtip.offsetHeight;
                qtip.classList.remove('init');
                // set qtip content
                const { left, top, width, height } = c.getBoundingClientRect();
                const scrollX = window.scrollX || window.pageXOffset;
                const scrollY = window.scrollY || window.pageYOffset;
                // calculate center
                let x = left + scrollX + width / 2;
                let y = top + scrollY + height / 2;
                // switch position
                if (x + w >= window_w) { x -= w; }
                if (y + h - scrollY > window_h) { y -= h; }
                // keep within viewport
                x = Math.max(scrollX + 10, Math.min(x, scrollX + window_w - w - 10));
                y = Math.max(scrollY + 10, Math.min(y, scrollY + window_h - h - 10));
                // set position
                qtip.style.left = `${x}px`;
                qtip.style.top = `${y}px`;
                if (!scroll_listen) {
                    window.addEventListener('scroll', init);
                    scroll_listen = true;
                }
            }
            init();

            function set_data(d) {
                if (qtip_select !== id) { return }

                document.getElementById('qtip-load').style.display = 'none';
                document.getElementById('qtip-con').innerHTML = d.data;

                const wl_btn = document.querySelectorAll('.qtip-wsl');
                wl_btn.forEach((btn, idx) => {
                    btn.dataset.id = idx + 1;
                    btn.addEventListener('click', () => {
                        let s;
                        if (btn.classList.contains('select')) {
                            update_watch_status(id, 'remove');
                        } else {
                            update_watch_status(id, 'add', btn.dataset.id);
                            s = btn.dataset.id;
                        }
                        wl_btn.forEach(e => {
                            e.classList.toggle('select', e.dataset.id == s);
                        });
                    });
                });

                const f = (JSON.parse(localStorage.getItem('watchStatus')) || []).find(e => e.id === id);
                if (f) {
                    const idx = Object.entries(watch_status_list).find(e => e[1] === f.s);
                    wl_btn.forEach(e => {
                        e.classList.toggle('select', e.dataset.id === idx[0]);
                    });
                }

                init();
            }

            fetch('/api/qtip.php?id=' + id)
                .then(r => r.json())
                .then(d => {
                    d.id = id;
                    qtip_cache.push(d);
                    if (d.status) {
                        set_data(d);
                    } else {
                        qtip_toggle(false);
                    }
                })
        }

        c.addEventListener('mouseenter', () => {
            if (qtip_select === id) {
                run();
            } else {
                qtip_select = id;
                document.getElementById('qtip-con').innerHTML = '';
                document.getElementById('qtip-load').style.display = 'flex';
                timer = setTimeout(() => { run(); }, 700);
            }
        });

        c.addEventListener('mouseleave', () => {
            clearTimeout(timer);
            qtip_toggle(false);
        });
    }

    const observer = new MutationObserver(() => {
        const qtips = document.querySelectorAll('.qtip-tag');
        qtips.forEach(e => {
            if (!e.dataset.qtipSetup) {
                e.dataset.qtipSetup = 'true';
                setupQtip(e);
            }
        });
    });

    observer.observe(document.body, { childList: true, subtree: true });
}