const required = document.getElementsByClassName('required');
const inputs = document.getElementsByClassName('inputs');
const button = document.getElementById('button');
let btn_text = button.outerText;
let allow = true;
let max_req = 3;

document.addEventListener('DOMContentLoaded', function () {
    for (let i = 0; i < required.length; i++) {
        required[i].addEventListener('input', () => {
            if (required[i].value.length >= 3) {
                const e = document.getElementById(`${required[i].id}-alert`);
                if (e.style.display !== 'none') { e.style.display = 'none'; }
            }
        });
    }
}, { once: true });

function submit_request() {
    if (max_req < 1) {
        button.style.backgroundColor = "#323232";
        btn_text = 'Maximum request limit reached';
        button.innerText = btn_text;
        alert(btn_text);
        return;
    }

    let err = false;
    for (let i = 0; i < required.length; i++) {
        if (required[i].value.trim().length <= 3) {
            err = true;
            document.getElementById(`${required[i].id}-alert`).style.display = 'flex';
        }
    }
    if (err || !allow) { return }

    button.innerText = 'Sending...';
    allow = false;

    let msg = {};
    for (let i = 0; i < inputs.length; i++) {
        if (inputs[i].value.trim() !== '') { msg[inputs[i].id] = inputs[i].value.trim(); }
    }

    fetch(`/api/post/request.php?text=${encodeURIComponent(JSON.stringify(msg))}`).then(r => r.json()).then(d => {
        if (d.status) {
            max_req--;
            button.innerText = 'Request sent';
            for (let i = 0; i < inputs.length; i++) { inputs[i].value = ''; }
            setTimeout(() => { button.innerText = btn_text; allow = true; }, 3000);
        } else {
            button.style.backgroundColor = 'Tomato';
            button.innerText = 'Something went wrong';
            setTimeout(() => {
                button.innerText = btn_text;
                button.style.backgroundColor = 'var(--light-blue-color)';
                allow = true;
            }, 3000);
        }
    });
}