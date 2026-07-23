/* Canciones en masa — tabs, dropzone y filtro client-side. */
(function () {
	/* ── Tabs ── */
	var tabs = document.querySelectorAll('.gl-tabs .gl-tab');
	var panels = document.querySelectorAll('.gl-bulk-panel');

	function showTab(name) {
		tabs.forEach(function (t) {
			t.classList.toggle('is-active', t.getAttribute('data-tab') === name);
		});
		panels.forEach(function (p) {
			p.classList.toggle('is-active', p.getAttribute('data-panel') === name);
		});
	}

	tabs.forEach(function (tab) {
		tab.addEventListener('click', function (e) {
			e.preventDefault();
			var name = tab.getAttribute('data-tab');
			showTab(name);
			if (history.replaceState) {
				var url = new URL(window.location.href);
				url.searchParams.set('tab', name);
				history.replaceState(null, '', url.toString());
			}
		});
	});

	/* ── Dropzone ── */
	var dropzone = document.getElementById('gl-dropzone');
	if (dropzone) {
		var fileInput = dropzone.querySelector('input[type="file"]');
		var fileName = document.getElementById('gl-file-name');

		fileInput.addEventListener('change', function () {
			fileName.textContent = fileInput.files.length ? '✓ ' + fileInput.files[0].name : '';
		});

		['dragenter', 'dragover'].forEach(function (ev) {
			dropzone.addEventListener(ev, function (e) {
				e.preventDefault();
				dropzone.classList.add('is-drag');
			});
		});
		['dragleave', 'drop'].forEach(function (ev) {
			dropzone.addEventListener(ev, function (e) {
				e.preventDefault();
				dropzone.classList.remove('is-drag');
			});
		});
		dropzone.addEventListener('drop', function (e) {
			if (e.dataTransfer && e.dataTransfer.files.length) {
				fileInput.files = e.dataTransfer.files;
				fileInput.dispatchEvent(new Event('change'));
			}
		});
	}

	/* ── Filtro de la sección de asignación ── */
	var list = document.getElementById('gl-song-list');
	if (!list) return;

	var rows = Array.prototype.slice.call(list.querySelectorAll('.gl-song-row'));
	var input = document.getElementById('gl-song-filter');
	var nocat = document.getElementById('gl-song-nocat');
	var count = document.getElementById('gl-song-count');

	function norm(s) {
		s = (s || '').toLowerCase().trim();
		return s.normalize ? s.normalize('NFD').replace(/[̀-ͯ]/g, '') : s;
	}

	function apply() {
		var q = norm(input.value);
		rows.forEach(function (row) {
			var okText = !q || row.getAttribute('data-title').indexOf(q) !== -1;
			var okCat = !nocat.checked || row.getAttribute('data-nocat') === '1';
			row.style.display = okText && okCat ? '' : 'none';
		});
	}

	function updateCount() {
		count.textContent = list.querySelectorAll('input:checked').length;
	}

	input.addEventListener('input', apply);
	nocat.addEventListener('change', apply);
	list.addEventListener('change', updateCount);

	document.getElementById('gl-song-select-visible').addEventListener('click', function () {
		rows.forEach(function (row) {
			if (row.style.display !== 'none') row.querySelector('input').checked = true;
		});
		updateCount();
	});

	document.getElementById('gl-song-deselect').addEventListener('click', function () {
		rows.forEach(function (row) {
			row.querySelector('input').checked = false;
		});
		updateCount();
	});
})();
