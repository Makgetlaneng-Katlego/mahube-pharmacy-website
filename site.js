/* Mahube Pharmacy - site behaviour that sits on top of app.js
   Demo-level sign in: details are kept in localStorage.
   To connect a real database later, replace the two "DEMO" blocks below with a fetch() to your PHP endpoint. */

function mhUser() {
    try { return JSON.parse(localStorage.getItem('mahubeUser') || 'null'); } catch (e) { return null; }
}
function mhLoggedIn() {
    return localStorage.getItem('mahubeLoggedIn') === 'true' || !!mhUser();
}
function mhFirstName(u, email) {
    if (u && u.name) return u.name.trim().split(/\s+/)[0];
    if (email) {
        var s = email.split('@')[0].replace(/[._-]+/g, ' ');
        return s.charAt(0).toUpperCase() + s.slice(1);
    }
    return 'there';
}

/* header: hide Log in / Create account once signed in */
function mhHeaderState() {
    var on = mhLoggedIn();
    document.querySelectorAll('[data-guest]').forEach(function (el) { el.style.display = on ? 'none' : ''; });
}

/* header search -> products page */
function siteSearch(form) {
    var q = form.q.value.trim();
    window.location.href = 'products.php' + (q ? '?q=' + encodeURIComponent(q) : '');
    return false;
}
function mhApplySearchParam() {
    var q = new URLSearchParams(window.location.search).get('q');
    if (!q || typeof filterProducts !== 'function') return;
    var box = document.querySelector('main input[onkeyup]');
    if (box) box.value = q;
    filterProducts(q);
}

/* the "signing you in" moment */
function mhOverlay(title, sub, goTo, delay) {
    var o = document.getElementById('signingOverlay');
    if (!o) return (window.location.href = goTo);
    o.querySelector('p').textContent = 'Signing you in...';
    o.querySelector('small').textContent = 'One moment';
    o.classList.remove('done');
    o.classList.add('show');
    setTimeout(function () {
        o.classList.add('done');
        o.querySelector('p').textContent = title;
        o.querySelector('small').textContent = sub;
        setTimeout(function () { window.location.href = goTo; }, 900);
    }, delay || 1100);
}

function mhSetInvalid(field, msg) {
    field.classList.add('invalid');
    var e = field.querySelector('.field-error');
    if (e && msg) e.textContent = msg;
}
function mhClearInvalid(form) {
    form.querySelectorAll('.field.invalid').forEach(function (f) { f.classList.remove('invalid'); });
}

function togglePw(btn) {
    var input = btn.parentElement.querySelector('input');
    var icon = btn.querySelector('i');
    var show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    icon.className = show ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye';
}

/* DEMO login */
function mhLogin(form) {
    mhClearInvalid(form);
    var email = form.email.value.trim();
    var pw = form.password.value;
    var ok = true;
    if (!/^\S+@\S+\.\S+$/.test(email)) { mhSetInvalid(form.querySelector('[data-f=email]'), 'Enter a valid email address.'); ok = false; }
    if (pw.length < 1) { mhSetInvalid(form.querySelector('[data-f=password]'), 'Enter your password.'); ok = false; }
    if (!ok) return false;

    var u = mhUser();
    localStorage.setItem('mahubeRole', 'customer');
    localStorage.setItem('mahubeLoggedIn', 'true');
    localStorage.setItem('mahubeLoginEmail', email);
    mhOverlay('Welcome back, ' + mhFirstName(u, email), 'Taking you to the homepage', 'index.php');
    return false;
}

/* DEMO register */
function mhRegister(form) {
    mhClearInvalid(form);
    var name = form.elements['name'].value.trim();
    var email = form.email.value.trim();
    var phone = form.phone.value.trim();
    var pw = form.password.value;
    var ok = true;
    if (name.length < 2) { mhSetInvalid(form.querySelector('[data-f=name]'), 'Please enter your full name.'); ok = false; }
    if (!/^\S+@\S+\.\S+$/.test(email)) { mhSetInvalid(form.querySelector('[data-f=email]'), 'Enter a valid email address.'); ok = false; }
    if (phone.replace(/\D/g, '').length < 10) { mhSetInvalid(form.querySelector('[data-f=phone]'), 'Enter a 10 digit phone number.'); ok = false; }
    if (pw.length < 8) { mhSetInvalid(form.querySelector('[data-f=password]'), 'Use at least 8 characters.'); ok = false; }
    if (form.confirm.value !== pw) { mhSetInvalid(form.querySelector('[data-f=confirm]'), 'The passwords do not match.'); ok = false; }
    if (!form.terms.checked) { var t = document.getElementById('termsError'); if (t) t.style.display = 'block'; ok = false; }
    if (!ok) return false;

    localStorage.setItem('mahubeUser', JSON.stringify({ name: name, email: email, phone: phone, dob: form.dob.value, since: new Date().toISOString() }));
    localStorage.setItem('mahubeRole', 'customer');
    localStorage.setItem('mahubeLoggedIn', 'true');
    mhOverlay('Welcome, ' + mhFirstName({ name: name }), 'Your account is ready', 'index.php', 1300);
    return false;
}

function logoutUser() {
    ['mahubeLoggedIn', 'mahubeLoginEmail', 'mahubeRole'].forEach(function (k) { localStorage.removeItem(k); });
    window.location.href = 'login.php';
    return false;
}

/* profile page: fill from the saved account */
function mhFillProfile() {
    var nameEl = document.getElementById('pfName');
    if (!nameEl) return;
    var u = mhUser() || {};
    var email = u.email || localStorage.getItem('mahubeLoginEmail') || '';
    var name = u.name || (email ? mhFirstName(null, email) : 'Guest');
    nameEl.textContent = name;
    var set = function (id, v, fallback) { var e = document.getElementById(id); if (e) e.textContent = v || fallback; };
    set('pfEmail', email, 'Not added yet');
    set('pfPhone', u.phone, 'Not added yet');
    var av = document.getElementById('pfAvatar');
    if (av) av.textContent = name.split(/\s+/).map(function (p) { return p.charAt(0); }).join('').slice(0, 2).toUpperCase();
    if (u.since) {
        var d = new Date(u.since);
        set('pfSince', d.toLocaleDateString('en-ZA', { month: 'long', year: 'numeric' }), '');
    }
}

document.addEventListener('DOMContentLoaded', function () {
    mhHeaderState();
    mhApplySearchParam();
    mhFillProfile();
});
