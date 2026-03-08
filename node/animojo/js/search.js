const filter_inputs = document.querySelectorAll('.filter-inputs');

document.addEventListener("DOMContentLoaded", () => {
    const p = new URLSearchParams(window.location.search);
    p.forEach((v, k) => {
        v = v.trim();
        if (v) {
            try {
                const e = document.getElementById(`search-in-${k}`);
                e.value = v.toLowerCase();
                e.classList.add('active');
            } catch { }
        }
    });

    filter_inputs.forEach(e => {
        e.addEventListener('input', () => {
            e.classList.toggle('active', e.value);
        });
    });
}, { once: true });

document.getElementById('search-form').addEventListener('submit', () => {
    filter_inputs.forEach(i => {
        if (!i.value.trim()) { i.name = ''; }
    });
});