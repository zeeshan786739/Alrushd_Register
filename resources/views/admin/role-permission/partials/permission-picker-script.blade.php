<script>
(function () {
    function initPermissionPicker(root) {
        var scope = root || document;
        scope.querySelectorAll('[data-perm-picker]').forEach(function (picker) {
            if (picker.dataset.bound === '1') return;
            picker.dataset.bound = '1';

            var searchInput = picker.querySelector('[data-perm-search]');
            var countEl = picker.querySelector('[data-perm-selected-count]');

            function updateCount() {
                if (!countEl) return;
                var checked = picker.querySelectorAll('[data-perm-checkbox]:checked').length;
                countEl.textContent = String(checked);
            }

            picker.querySelectorAll('[data-perm-checkbox]').forEach(function (box) {
                box.addEventListener('change', updateCount);
            });

            if (searchInput) {
                searchInput.addEventListener('input', function () {
                    var q = searchInput.value.toLowerCase().trim();
                    picker.querySelectorAll('[data-perm-group]').forEach(function (group) {
                        var visible = 0;
                        group.querySelectorAll('.um-perm-item').forEach(function (item) {
                            var label = item.getAttribute('data-perm-label') || '';
                            var show = !q || label.includes(q);
                            item.style.display = show ? '' : 'none';
                            if (show) visible++;
                        });
                        group.style.display = visible ? '' : 'none';
                        if (q && visible) group.open = true;
                    });
                });
            }

            picker.querySelectorAll('[data-perm-select-group]').forEach(function (btn) {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    var group = btn.closest('[data-perm-group]');
                    if (!group) return;
                    var boxes = Array.from(group.querySelectorAll('[data-perm-checkbox]'))
                        .filter(function (box) { return box.closest('.um-perm-item').style.display !== 'none'; });
                    var allChecked = boxes.length > 0 && boxes.every(function (box) { return box.checked; });
                    boxes.forEach(function (box) { box.checked = !allChecked; });
                    btn.textContent = allChecked ? 'Select all' : 'Clear group';
                    updateCount();
                });
            });

            updateCount();
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        initPermissionPicker(document);
    });

    document.addEventListener('admin:page-loaded', function (e) {
        initPermissionPicker(e.detail && e.detail.root ? e.detail.root : document);
    });
})();
</script>
