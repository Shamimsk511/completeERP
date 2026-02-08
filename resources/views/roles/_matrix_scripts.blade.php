<script>
    $(document).ready(function() {
        var $permissionCheckboxes = $('.permission-checkbox');

        function updateSelectedCount() {
            $('#selected-count').text($permissionCheckboxes.filter(':checked').length);
        }

        function updateModuleState(moduleKey) {
            var $moduleBoxes = $permissionCheckboxes.filter('[data-module="' + moduleKey + '"]');
            var $selector = $('.module-selector[data-module="' + moduleKey + '"]');
            var total = $moduleBoxes.length;
            var checked = $moduleBoxes.filter(':checked').length;

            $selector.prop('disabled', total === 0);
            $selector.prop('checked', total > 0 && checked === total);
            $selector.prop('indeterminate', checked > 0 && checked < total);

            var $meta = $('.module-meta[data-module-meta="' + moduleKey + '"]');
            if ($meta.length) {
                var totalCount = parseInt($meta.data('total'), 10) || total;
                $meta.text(checked + ' / ' + totalCount + ' selected');
            }
        }

        function updateAllModuleStates() {
            $('.module-selector').each(function() {
                updateModuleState($(this).data('module'));
            });
        }

        function updateActionState(actionSlug) {
            var $actionBoxes = $permissionCheckboxes.filter('[data-action="' + actionSlug + '"]');
            var $selector = $('.action-selector[data-action="' + actionSlug + '"]');
            var total = $actionBoxes.length;
            var checked = $actionBoxes.filter(':checked').length;

            $selector.prop('disabled', total === 0);
            $selector.prop('checked', total > 0 && checked === total);
            $selector.prop('indeterminate', checked > 0 && checked < total);
        }

        function updateAllActionStates() {
            $('.action-selector').each(function() {
                updateActionState($(this).data('action'));
            });
        }

        function updateToolbarState() {
            var total = $permissionCheckboxes.length;
            var checked = $permissionCheckboxes.filter(':checked').length;
            var $selectAllBtn = $('#select-all-permissions');

            $selectAllBtn.prop('disabled', total === 0);
            if (checked === total && total > 0) {
                $selectAllBtn.html('<i class="fas fa-check-square"></i> All Selected');
            } else {
                $selectAllBtn.html('<i class="fas fa-check-square"></i> Select All');
            }
        }

        function refreshStates() {
            updateSelectedCount();
            updateAllModuleStates();
            updateAllActionStates();
            updateToolbarState();
        }

        $(document).on('change', '.permission-checkbox', function() {
            refreshStates();
        });

        $('.module-selector').on('change', function() {
            var moduleKey = $(this).data('module');
            var shouldCheck = $(this).is(':checked');
            $permissionCheckboxes.filter('[data-module="' + moduleKey + '"]').prop('checked', shouldCheck);
            refreshStates();
        });

        $('.action-selector').on('change', function() {
            var actionSlug = $(this).data('action');
            var shouldCheck = $(this).is(':checked');
            $permissionCheckboxes.filter('[data-action="' + actionSlug + '"]').prop('checked', shouldCheck);
            refreshStates();
        });

        $('#select-all-permissions').on('click', function() {
            $permissionCheckboxes.prop('checked', true);
            refreshStates();
        });

        $('#clear-all-permissions').on('click', function() {
            $permissionCheckboxes.prop('checked', false);
            refreshStates();
        });

        $('#permission-search').on('input', function() {
            var query = ($(this).val() || '').toLowerCase().trim();

            $('.permission-row').each(function() {
                var haystack = ($(this).data('search') || '').toString().toLowerCase();
                var visible = !query || haystack.indexOf(query) !== -1;
                $(this).toggle(visible);
            });
        });

        refreshStates();
    });
</script>
