function back_prop_redir() {
    document.write(" Please close the Developer Tools. ");
    window.location = '/';
}
function check_dvt() {
    console.clear();
    before = new Date().getTime();
    debugger;
    after = new Date().getTime();
    if (after - before > 200) {
        back_prop_redir();
    } else {
        before = null;
        after = null;
        delete before;
        delete after;
    }
    setTimeout(check_dvt, 200);
}
check_dvt();
window.onload = function () {
    document.addEventListener("keydown", function (e) {
        if (event.keyCode == 123) {
            back_prop_redir();
        }
        if (e.ctrlKey && e.shiftKey && e.keyCode == 73) {
            back_prop_redir();
        }
        if (e.ctrlKey && e.shiftKey && e.keyCode == 74) {
            back_prop_redir();
        }
        if (e.keyCode == 83 && (navigator.platform.match("Mac") ? e.metaKey : e.ctrlKey)) {
            back_prop_redir();
        }
        if (e.ctrlKey && e.keyCode == 85) {
            back_prop_redir();
        }
    }, false);
};