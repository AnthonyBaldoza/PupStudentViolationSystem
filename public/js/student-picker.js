/*
 * Student picker for the violation report form.
 * Searches /students/search as you type and stores the chosen student's ID
 * in the hidden #student_id field. Results are built with textContent, so
 * student names can never inject HTML.
 */
(function () {
    'use strict';

    var root = document.getElementById('student-picker');
    if (!root) return;

    var searchUrl = root.dataset.searchUrl;
    var hidden = document.getElementById('student_id');
    var input = document.getElementById('student-search');
    var results = document.getElementById('student-results');
    var statusEl = document.getElementById('student-status');
    var searchWrap = document.getElementById('student-search-wrap');
    var picked = document.getElementById('student-picked');
    var pickedName = document.getElementById('student-picked-name');
    var pickedMeta = document.getElementById('student-picked-meta');
    var clearBtn = document.getElementById('student-clear');

    var timer = null;
    var controller = null;

    function meta(s) {
        var parts = [s.student_number, s.course, s.year_level];
        if (s.section) parts.push(s.section);
        return parts.join(' · ');
    }

    function pick(s) {
        hidden.value = s.id;
        pickedName.textContent = s.full_name;
        pickedMeta.textContent = meta(s);
        picked.classList.remove('d-none');
        searchWrap.classList.add('d-none');
        results.replaceChildren();
        statusEl.textContent = '';
        input.value = '';
    }

    function clearPick() {
        hidden.value = '';
        picked.classList.add('d-none');
        searchWrap.classList.remove('d-none');
        input.focus();
    }

    function render(list) {
        results.replaceChildren();

        if (!list.length) {
            statusEl.textContent = 'No matching students.';
            return;
        }

        statusEl.textContent = '';
        list.forEach(function (s) {
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'list-group-item list-group-item-action';

            var name = document.createElement('div');
            name.className = 'fw-semibold';
            name.textContent = s.full_name;

            var info = document.createElement('div');
            info.className = 'small text-muted';
            info.textContent = meta(s);

            btn.appendChild(name);
            btn.appendChild(info);
            btn.addEventListener('click', function () { pick(s); });
            results.appendChild(btn);
        });
    }

    function search() {
        var q = input.value.trim();

        if (q.length < 2) {
            results.replaceChildren();
            statusEl.textContent = '';
            return;
        }

        if (controller) controller.abort();
        controller = new AbortController();
        statusEl.textContent = 'Searching…';

        fetch(searchUrl + '?q=' + encodeURIComponent(q), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
            signal: controller.signal
        })
            .then(function (res) {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.json();
            })
            .then(render)
            .catch(function (err) {
                if (err.name === 'AbortError') return;
                results.replaceChildren();
                statusEl.textContent = 'Search failed. Reload the page and try again.';
            });
    }

    input.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(search, 250);
    });

    // Pressing Enter in the search box must not submit the whole report form.
    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') e.preventDefault();
    });

    clearBtn.addEventListener('click', clearPick);

    // Close the result list when clicking elsewhere.
    document.addEventListener('click', function (e) {
        if (!root.contains(e.target)) results.replaceChildren();
    });
})();