@extends('layouts.modern-admin')

@section('title', 'Chart of Accounts')

@section('page_title', 'Chart of Accounts')

@section('header_actions')
    <button type="button" class="btn modern-btn modern-btn-outline" id="collapseAllBtn">
        <i class="fas fa-compress-alt"></i> Collapse All
    </button>
    <button type="button" class="btn modern-btn modern-btn-outline" id="expandAllBtn">
        <i class="fas fa-expand-alt"></i> Expand All
    </button>
    <a href="{{ route('accounting.account-groups.create') }}" class="btn modern-btn modern-btn-primary">
        <i class="fas fa-plus"></i> New Account Group
    </a>
@stop

@section('page_content')
    <!-- Search Box -->
    <div class="card modern-card mb-4">
        <div class="card-body py-3">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                        </div>
                        <input type="text" id="account-search" class="form-control" placeholder="Search accounts or groups by name or code...">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-secondary" id="clear-search" style="display: none;">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 text-right">
                    <span id="search-results-count" class="text-muted" style="display: none;"></span>
                </div>
            </div>
        </div>
    </div>

    <div class="card modern-card">
        <div class="card-header modern-header">
            <h3 class="card-title">
                <i class="fas fa-sitemap"></i> Account Groups Hierarchy
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($groups as $group)
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header bg-{{ $group->nature === 'assets' ? 'primary' : ($group->nature === 'liabilities' ? 'danger' : ($group->nature === 'income' ? 'success' : ($group->nature === 'expenses' ? 'warning' : 'info'))) }} text-white">
                                <h5 class="mb-0 d-flex justify-content-between align-items-center">
                                    <span>{{ $group->name }}</span>
                                    <span class="badge badge-light text-dark">
                                        {{ $groupTotals[$group->nature] ?? '৳0.00' }}
                                    </span>
                                </h5>
                            </div>
                            <div class="card-body">
                                @include('accounting.account-groups.partials.tree-item', ['items' => $group->allChildren, 'level' => 0])

                                @if($group->accounts->count() > 0)
                                    <div class="mt-3">
                                        <h6 class="text-muted">Direct Accounts:</h6>
                                        <ul class="list-unstyled mb-0">
                                            @foreach($group->accounts as $account)
                                                <li class="account-item py-1">
                                                    <i class="fas fa-file-alt text-muted mr-1"></i>
                                                    <a href="{{ route('accounting.accounts.show', $account) }}">
                                                        {{ $account->code }} - {{ $account->name }}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>
                            <div class="card-footer text-right">
                                <a href="{{ route('accounting.account-groups.show', $group) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                @if(!$group->is_system)
                                    <a href="{{ route('accounting.account-groups.edit', $group) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@stop

@section('additional_css')
<style>
    .tree-item {
        padding-left: 20px;
        border-left: 1px dashed #ddd;
        margin-left: 10px;
    }

    .tree-item-header {
        padding: 5px 0;
    }

    .tree-item-header:hover {
        background-color: #f8f9fa;
        border-radius: 4px;
    }

    .account-item {
        padding: 3px 0;
        padding-left: 30px;
    }

    .account-item i {
        width: 20px;
    }

    .branch-toggle {
        transition: transform 0.2s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .branch-toggle .toggle-icon {
        transition: transform 0.2s ease;
        font-size: 12px;
        color: #6c757d;
    }

    .branch-toggle.collapsed .toggle-icon {
        transform: rotate(-90deg);
    }

    .branch-content {
        transition: all 0.2s ease;
    }

    .branch-content.collapsed {
        display: none;
    }

    /* Search highlighting */
    .search-highlight {
        background-color: #fff3cd;
        padding: 2px 4px;
        border-radius: 3px;
    }

    .search-hidden {
        display: none !important;
    }
</style>
@stop

@section('additional_js')
<script>
$(document).ready(function() {
    // Load saved collapse states from localStorage
    const savedStates = JSON.parse(localStorage.getItem('accountGroupCollapseStates') || '{}');

    // Apply saved states
    Object.keys(savedStates).forEach(function(branchId) {
        if (savedStates[branchId]) {
            const $toggle = $(`.branch-toggle[data-target="${branchId}"]`);
            const $content = $(`#${branchId}`);
            $toggle.addClass('collapsed');
            $content.addClass('collapsed');
        }
    });

    // Handle branch toggle click
    $(document).on('click', '.branch-toggle', function() {
        const targetId = $(this).data('target');
        const $content = $(`#${targetId}`);

        $(this).toggleClass('collapsed');
        $content.toggleClass('collapsed');

        // Save state to localStorage
        const states = JSON.parse(localStorage.getItem('accountGroupCollapseStates') || '{}');
        states[targetId] = $(this).hasClass('collapsed');
        localStorage.setItem('accountGroupCollapseStates', JSON.stringify(states));
    });

    // Collapse All button
    $('#collapseAllBtn').on('click', function() {
        $('.branch-toggle').addClass('collapsed');
        $('.branch-content').addClass('collapsed');

        // Save all states as collapsed
        const states = {};
        $('.branch-toggle').each(function() {
            states[$(this).data('target')] = true;
        });
        localStorage.setItem('accountGroupCollapseStates', JSON.stringify(states));
    });

    // Expand All button
    $('#expandAllBtn').on('click', function() {
        $('.branch-toggle').removeClass('collapsed');
        $('.branch-content').removeClass('collapsed');

        // Clear all saved states
        localStorage.removeItem('accountGroupCollapseStates');
    });

    // Search functionality
    let searchTimeout;
    $('#account-search').on('input', function() {
        clearTimeout(searchTimeout);
        const searchTerm = $(this).val().toLowerCase().trim();

        searchTimeout = setTimeout(function() {
            performSearch(searchTerm);
        }, 200);
    });

    // Clear search button
    $('#clear-search').on('click', function() {
        $('#account-search').val('').trigger('input');
        $(this).hide();
    });

    function performSearch(searchTerm) {
        const $clearBtn = $('#clear-search');
        const $resultsCount = $('#search-results-count');

        if (searchTerm.length === 0) {
            // Reset view - show all, restore collapse states
            $('.tree-item, .account-item, .card').removeClass('search-hidden');
            $('.search-highlight').contents().unwrap();
            $clearBtn.hide();
            $resultsCount.hide();

            // Restore saved collapse states
            const savedStates = JSON.parse(localStorage.getItem('accountGroupCollapseStates') || '{}');
            Object.keys(savedStates).forEach(function(branchId) {
                if (savedStates[branchId]) {
                    $(`.branch-toggle[data-target="${branchId}"]`).addClass('collapsed');
                    $(`#${branchId}`).addClass('collapsed');
                }
            });
            return;
        }

        $clearBtn.show();

        // Expand all branches first
        $('.branch-toggle').removeClass('collapsed');
        $('.branch-content').removeClass('collapsed');

        // Remove previous highlights
        $('.search-highlight').contents().unwrap();

        let matchCount = 0;

        // Search in accounts
        $('.account-item').each(function() {
            const $item = $(this);
            const $link = $item.find('a');
            const text = $link.text().toLowerCase();

            if (text.includes(searchTerm)) {
                $item.removeClass('search-hidden');
                matchCount++;

                // Highlight matching text
                const originalText = $link.text();
                const regex = new RegExp(`(${escapeRegex(searchTerm)})`, 'gi');
                $link.html(originalText.replace(regex, '<span class="search-highlight">$1</span>'));

                // Make sure parent groups are visible
                $item.parents('.branch-content').removeClass('collapsed');
                $item.parents('.tree-item').removeClass('search-hidden');
            } else {
                $item.addClass('search-hidden');
            }
        });

        // Search in group names
        $('.tree-item-header').each(function() {
            const $header = $(this);
            const $item = $header.closest('.tree-item');
            const $strong = $header.find('strong');
            const text = $strong.text().toLowerCase();
            const code = $header.find('small.text-muted').first().text().toLowerCase();

            if (text.includes(searchTerm) || code.includes(searchTerm)) {
                $item.removeClass('search-hidden');
                matchCount++;

                // Highlight matching text
                const originalText = $strong.text();
                const regex = new RegExp(`(${escapeRegex(searchTerm)})`, 'gi');
                $strong.html(originalText.replace(regex, '<span class="search-highlight">$1</span>'));

                // Make sure parent groups are visible
                $item.parents('.branch-content').removeClass('collapsed');
                $item.parents('.tree-item').removeClass('search-hidden');
            }
        });

        // Hide groups with no visible children
        $('.tree-item').each(function() {
            const $item = $(this);
            const hasVisibleContent = $item.find('.account-item:not(.search-hidden)').length > 0 ||
                                       $item.find('.tree-item:not(.search-hidden)').length > 0 ||
                                       $item.find('.search-highlight').length > 0;
            if (!hasVisibleContent) {
                $item.addClass('search-hidden');
            }
        });

        // Update results count
        $resultsCount.text(matchCount + ' result' + (matchCount !== 1 ? 's' : '') + ' found').show();
    }

    function escapeRegex(string) {
        return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }
});
</script>
@stop
