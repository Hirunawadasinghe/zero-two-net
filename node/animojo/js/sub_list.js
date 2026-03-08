document.getElementById('sub-list').querySelectorAll('a').forEach(e => {
    e.href = `${subdl_base_url[e.dataset.i]}&f=${e.dataset.e}`;
});