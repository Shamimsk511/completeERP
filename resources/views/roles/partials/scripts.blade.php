<script>
    (function () {
        function initBuilder(root) {
            var searchInput = root.querySelector('[data-role-search]');
            var selectAllBtn = root.querySelector('[data-role-select-all]');
            var clearAllBtn = root.querySelector('[data-role-clear]');
            var expandAllBtn = root.querySelector('[data-role-expand]');
            var collapseAllBtn = root.querySelector('[data-role-collapse]');
            var selectedCountNode = root.querySelector('[data-role-selected-count]');
            var totalCountNode = root.querySelector('[data-role-total-count]');
            var groups = Array.prototype.slice.call(root.querySelectorAll('[data-role-group]'));

            function allPermissionChecks() {
                return Array.prototype.slice.call(root.querySelectorAll('.rolex-perm-check'));
            }

            function updateToolbarCounts() {
                var checks = allPermissionChecks();
                var checked = checks.filter(function (item) { return item.checked; }).length;
                if (selectedCountNode) selectedCountNode.textContent = checked.toString();
                if (totalCountNode) totalCountNode.textContent = checks.length.toString();
            }

            function updateGroupState(groupEl) {
                var groupChecks = Array.prototype.slice.call(groupEl.querySelectorAll('.rolex-perm-check'));
                var groupMaster = groupEl.querySelector('.rolex-group-check-input');
                var checkedCount = groupChecks.filter(function (item) { return item.checked; }).length;
                var groupCountNode = groupEl.querySelector('[data-role-group-selected-count]');

                if (groupCountNode) {
                    groupCountNode.textContent = checkedCount.toString();
                }

                if (groupMaster) {
                    if (checkedCount === 0) {
                        groupMaster.checked = false;
                        groupMaster.indeterminate = false;
                    } else if (checkedCount === groupChecks.length) {
                        groupMaster.checked = true;
                        groupMaster.indeterminate = false;
                    } else {
                        groupMaster.checked = false;
                        groupMaster.indeterminate = true;
                    }
                }
            }

            function updateAllGroupStates() {
                groups.forEach(updateGroupState);
                updateToolbarCounts();
            }

            function setAllChecks(state) {
                allPermissionChecks().forEach(function (check) {
                    check.checked = state;
                });
                updateAllGroupStates();
            }

            function toggleSelectAll() {
                var checks = allPermissionChecks();
                var isAllChecked = checks.length > 0 && checks.every(function (item) { return item.checked; });
                setAllChecks(!isAllChecked);
            }

            function setGroupCollapsed(groupEl, collapsed) {
                if (collapsed) {
                    groupEl.classList.add('rolex-group-collapsed');
                } else {
                    groupEl.classList.remove('rolex-group-collapsed');
                }
            }

            function applySearchFilter() {
                var query = (searchInput ? searchInput.value : '').toLowerCase().trim();

                groups.forEach(function (groupEl) {
                    var chips = Array.prototype.slice.call(groupEl.querySelectorAll('.rolex-chip'));
                    var visibleCount = 0;

                    chips.forEach(function (chip) {
                        var label = (chip.getAttribute('data-label') || '').toLowerCase();
                        var show = !query || label.indexOf(query) !== -1;
                        chip.classList.toggle('rolex-chip-hidden', !show);
                        if (show) visibleCount++;
                    });

                    groupEl.classList.toggle('rolex-group-hidden', visibleCount === 0);

                    if (query && visibleCount > 0) {
                        setGroupCollapsed(groupEl, false);
                    }
                });
            }

            groups.forEach(function (groupEl) {
                var titleBtn = groupEl.querySelector('.rolex-group-title-btn');
                var groupMaster = groupEl.querySelector('.rolex-group-check-input');
                var groupChecks = Array.prototype.slice.call(groupEl.querySelectorAll('.rolex-perm-check'));

                if (titleBtn) {
                    titleBtn.addEventListener('click', function () {
                        setGroupCollapsed(groupEl, !groupEl.classList.contains('rolex-group-collapsed'));
                    });
                }

                if (groupMaster) {
                    groupMaster.addEventListener('change', function () {
                        groupChecks.forEach(function (check) {
                            check.checked = groupMaster.checked;
                        });
                        updateGroupState(groupEl);
                        updateToolbarCounts();
                    });
                }

                groupChecks.forEach(function (check) {
                    check.addEventListener('change', function () {
                        updateGroupState(groupEl);
                        updateToolbarCounts();
                    });
                });
            });

            if (searchInput) {
                searchInput.addEventListener('input', applySearchFilter);
            }

            if (selectAllBtn) {
                selectAllBtn.addEventListener('click', toggleSelectAll);
            }

            if (clearAllBtn) {
                clearAllBtn.addEventListener('click', function () {
                    setAllChecks(false);
                });
            }

            if (expandAllBtn) {
                expandAllBtn.addEventListener('click', function () {
                    groups.forEach(function (groupEl) {
                        setGroupCollapsed(groupEl, false);
                    });
                });
            }

            if (collapseAllBtn) {
                collapseAllBtn.addEventListener('click', function () {
                    groups.forEach(function (groupEl) {
                        setGroupCollapsed(groupEl, true);
                    });
                });
            }

            updateAllGroupStates();
        }

        document.addEventListener('DOMContentLoaded', function () {
            var builders = document.querySelectorAll('[data-role-permission-builder]');
            builders.forEach(function (root) {
                initBuilder(root);
            });

            var searchInput = document.getElementById('roles-search-input');
            if (searchInput) {
                var rows = Array.prototype.slice.call(document.querySelectorAll('#roles-table tbody tr'));
                searchInput.addEventListener('input', function () {
                    var query = searchInput.value.toLowerCase().trim();
                    rows.forEach(function (row) {
                        if (row.querySelector('.rolex-empty')) {
                            return;
                        }
                        row.style.display = row.textContent.toLowerCase().indexOf(query) !== -1 ? '' : 'none';
                    });
                });
            }
        });
    })();
</script>
