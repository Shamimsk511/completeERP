@php($searchUrl = route('global-search'))

<div class="navbar-global-search d-none d-md-flex" data-search-url="{{ $searchUrl }}">
    <button
        type="button"
        class="navbar-global-search-launch"
        data-toggle="modal"
        data-target="#globalSearchModal"
        data-global-search-trigger
    >
        <i class="fas fa-search" aria-hidden="true"></i>
        <span>Search</span>
        <span class="navbar-global-search-shortcut">Ctrl+K</span>
    </button>
</div>

@once
<div class="modal fade global-search-modal" id="globalSearchModal" tabindex="-1" role="dialog" aria-hidden="true" data-search-url="{{ $searchUrl }}">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content global-search-modal-content">
            <div class="global-search-head">
                <i class="fas fa-search global-search-head-icon" aria-hidden="true"></i>
                <input
                    type="text"
                    id="global-search-input"
                    class="global-search-input"
                    placeholder="Search invoices, customers, products, challans..."
                    autocomplete="off"
                    spellcheck="false"
                    aria-label="Global Search"
                >
                <button type="button" class="global-search-close-btn" data-dismiss="modal" aria-label="Close Search">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="global-search-results" id="global-search-results"></div>
        </div>
    </div>
</div>

<style>
    .main-header.navbar .navbar-global-search {
        margin: 0 10px;
    }

    .main-header.navbar .navbar-global-search-launch {
        height: 34px;
        min-width: 170px;
        display: inline-flex;
        align-items: center;
        justify-content: flex-start;
        gap: 9px;
        padding: 0 12px 0 10px;
        border-radius: 12px;
        border: 1px solid rgba(255, 255, 255, 0.34);
        background: linear-gradient(170deg, rgba(255, 255, 255, 0.24), rgba(255, 255, 255, 0.08));
        backdrop-filter: blur(10px) saturate(135%);
        -webkit-backdrop-filter: blur(10px) saturate(135%);
        box-shadow: inset 0 2px 7px rgba(15, 23, 42, 0.36), inset 0 -1px 0 rgba(255, 255, 255, 0.42), 0 1px 0 rgba(255, 255, 255, 0.18);
        color: #0f172a;
        font-size: 12px;
        font-weight: 700;
        line-height: 1;
        letter-spacing: 0.15px;
        transition: box-shadow 0.2s ease, background 0.2s ease, border-color 0.2s ease, color 0.2s ease;
    }

    .main-header.navbar.navbar-dark .navbar-global-search-launch {
        color: rgba(255, 255, 255, 0.96);
        border-color: rgba(255, 255, 255, 0.26);
        background: linear-gradient(170deg, rgba(2, 6, 23, 0.36), rgba(255, 255, 255, 0.1));
        box-shadow: inset 0 2px 7px rgba(2, 6, 23, 0.52), inset 0 -1px 0 rgba(255, 255, 255, 0.2), 0 1px 0 rgba(255, 255, 255, 0.08);
    }

    .main-header.navbar .navbar-global-search-launch:hover {
        background: linear-gradient(170deg, rgba(255, 255, 255, 0.3), rgba(255, 255, 255, 0.12));
        border-color: rgba(255, 255, 255, 0.48);
        box-shadow: inset 0 2px 8px rgba(15, 23, 42, 0.38), inset 0 -1px 0 rgba(255, 255, 255, 0.55), 0 6px 14px rgba(15, 23, 42, 0.18);
    }

    .main-header.navbar.navbar-dark .navbar-global-search-launch:hover {
        background: linear-gradient(170deg, rgba(2, 6, 23, 0.4), rgba(255, 255, 255, 0.14));
        border-color: rgba(255, 255, 255, 0.34);
    }

    .main-header.navbar .navbar-global-search-launch:active {
        box-shadow: inset 0 3px 10px rgba(15, 23, 42, 0.45), inset 0 -1px 0 rgba(255, 255, 255, 0.25);
    }

    .main-header.navbar .navbar-global-search-launch:focus {
        outline: none;
    }

    .main-header.navbar .navbar-global-search-launch:focus-visible {
        border-color: rgba(59, 130, 246, 0.9);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2), 0 14px 30px rgba(15, 23, 42, 0.22);
    }

    .main-header.navbar .navbar-global-search-launch i {
        width: 19px;
        height: 19px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        background: rgba(15, 23, 42, 0.14);
        border: 1px solid rgba(15, 23, 42, 0.2);
        box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.35);
        color: currentColor;
        flex: 0 0 auto;
    }

    .main-header.navbar.navbar-dark .navbar-global-search-launch i {
        background: rgba(15, 23, 42, 0.28);
        border-color: rgba(255, 255, 255, 0.2);
    }

    .main-header.navbar .navbar-global-search-launch > span:first-of-type {
        flex: 1 1 auto;
        text-align: left;
    }

    .main-header.navbar .navbar-global-search-shortcut {
        font-size: 10px;
        border: 1px solid rgba(15, 23, 42, 0.2);
        border-radius: 6px;
        padding: 2px 6px 1px;
        font-weight: 700;
        color: rgba(15, 23, 42, 0.75);
        background: rgba(255, 255, 255, 0.32);
        letter-spacing: 0.25px;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        flex: 0 0 auto;
    }

    .main-header.navbar.navbar-dark .navbar-global-search-shortcut {
        color: rgba(255, 255, 255, 0.95);
        border-color: rgba(255, 255, 255, 0.35);
        background: rgba(255, 255, 255, 0.12);
    }

    .global-search-modal-open .modal-backdrop.show {
        background: radial-gradient(circle at 15% 20%, rgba(59, 130, 246, 0.28), rgba(15, 23, 42, 0.82) 55%);
        opacity: 1;
        backdrop-filter: blur(4px);
    }

    .global-search-modal .modal-dialog {
        max-width: min(980px, calc(100vw - 34px));
        margin: clamp(34px, 8vh, 72px) auto;
    }

    .global-search-modal .global-search-modal-content {
        position: relative;
        border-radius: 18px;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.5);
        box-shadow: 0 36px 80px rgba(15, 23, 42, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.65);
        background: linear-gradient(152deg, rgba(255, 255, 255, 0.82), rgba(255, 255, 255, 0.68));
        backdrop-filter: blur(18px) saturate(140%);
        -webkit-backdrop-filter: blur(18px) saturate(140%);
    }

    .global-search-modal .global-search-modal-content::before {
        content: "";
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at 0% 0%, rgba(59, 130, 246, 0.13), transparent 42%),
            radial-gradient(circle at 96% 0%, rgba(16, 185, 129, 0.1), transparent 40%);
        pointer-events: none;
    }

    .global-search-head {
        position: relative;
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 13px 15px;
        border-bottom: 1px solid rgba(15, 23, 42, 0.12);
        background: linear-gradient(90deg, rgba(37, 99, 235, 0.11), rgba(37, 99, 235, 0.03));
        z-index: 1;
    }

    .global-search-head-icon {
        color: #1e293b;
        font-size: 15px;
    }

    .global-search-input {
        flex: 1 1 auto;
        border: 0;
        outline: none;
        font-size: 15px;
        font-weight: 700;
        color: #0f172a;
        background: transparent;
        min-width: 0;
    }

    .global-search-input::placeholder {
        color: #94a3b8;
        font-weight: 500;
    }

    .global-search-close-btn {
        width: 30px;
        height: 30px;
        border: 1px solid rgba(15, 23, 42, 0.14);
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.72);
        color: #334155;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
        backdrop-filter: blur(5px);
        -webkit-backdrop-filter: blur(5px);
    }

    .global-search-close-btn:hover {
        color: #0f172a;
        background: rgba(255, 255, 255, 0.94);
        border-color: rgba(15, 23, 42, 0.22);
    }

    .global-search-results {
        position: relative;
        max-height: min(68vh, 560px);
        overflow: auto;
        z-index: 1;
    }

    .global-search-state {
        padding: 15px;
        color: #475569;
        font-size: 13px;
        font-weight: 600;
    }

    .global-search-group {
        border-top: 1px solid rgba(15, 23, 42, 0.08);
    }

    .global-search-group:first-child {
        border-top: 0;
    }

    .global-search-group-label {
        padding: 10px 15px;
        background: linear-gradient(90deg, rgba(37, 99, 235, 0.12), rgba(148, 163, 184, 0.09));
        color: #1e293b;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }

    .global-search-item {
        display: flex;
        gap: 10px;
        padding: 11px 15px;
        text-decoration: none;
        color: #0f172a;
        border-top: 1px solid rgba(15, 23, 42, 0.06);
        transition: background 0.2s ease;
    }

    .global-search-item:first-child {
        border-top: 0;
    }

    .global-search-item:hover {
        background: linear-gradient(90deg, rgba(59, 130, 246, 0.16), rgba(59, 130, 246, 0.07));
        color: #0f172a;
    }

    .global-search-item-icon {
        width: 18px;
        flex: 0 0 18px;
        text-align: center;
        color: #2563eb;
        margin-top: 2px;
    }

    .global-search-item-body {
        min-width: 0;
        flex: 1 1 auto;
    }

    .global-search-item-title {
        font-size: 13px;
        font-weight: 800;
        line-height: 1.2;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .global-search-item-subtitle,
    .global-search-item-meta {
        margin-top: 2px;
        font-size: 11px;
        color: #64748b;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    @media (max-width: 1199.98px) {
        .main-header.navbar .navbar-global-search-shortcut {
            display: none;
        }

        .main-header.navbar .navbar-global-search-launch {
            min-width: 122px;
        }
    }
</style>

@push('js')
<script>
    (function () {
        var modalEl = document.getElementById('globalSearchModal');
        var input = document.getElementById('global-search-input');
        var results = document.getElementById('global-search-results');
        var searchUrl = modalEl ? (modalEl.getAttribute('data-search-url') || '') : '';
        var $modal = window.jQuery && modalEl ? window.jQuery(modalEl) : null;

        if (!modalEl || !input || !results || !searchUrl) {
            return;
        }

        var timer = null;
        var activeController = null;

        function esc(value) {
            return String(value == null ? '' : value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function setState(text) {
            results.innerHTML = '<div class="global-search-state">' + esc(text) + '</div>';
        }

        function renderResults(payload) {
            var groups = payload && Array.isArray(payload.groups) ? payload.groups : [];
            if (!groups.length) {
                setState('No matching results found.');
                return;
            }

            var html = groups.map(function (group) {
                var items = Array.isArray(group.items) ? group.items : [];
                if (!items.length) {
                    return '';
                }

                var itemsHtml = items.map(function (item) {
                    return ''
                        + '<a href="' + esc(item.url || '#') + '" class="global-search-item">'
                        + '  <span class="global-search-item-icon"><i class="' + esc(item.icon || 'fas fa-link') + '"></i></span>'
                        + '  <span class="global-search-item-body">'
                        + '    <span class="global-search-item-title">' + esc(item.title || '') + '</span>'
                        + '    <span class="global-search-item-subtitle">' + esc(item.subtitle || '') + '</span>'
                        + '    <span class="global-search-item-meta">' + esc(item.meta || '') + '</span>'
                        + '  </span>'
                        + '</a>';
                }).join('');

                return ''
                    + '<div class="global-search-group">'
                    + '  <div class="global-search-group-label">' + esc(group.label || 'Results') + '</div>'
                    + itemsHtml
                    + '</div>';
            }).join('');

            results.innerHTML = html;
        }

        function search(term) {
            if (activeController) {
                activeController.abort();
            }

            activeController = new AbortController();

            fetch(searchUrl + '?q=' + encodeURIComponent(term), {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                signal: activeController.signal
            })
                .then(function (response) {
                    return response.ok ? response.json() : Promise.reject(new Error('Search failed'));
                })
                .then(function (data) {
                    renderResults(data);
                })
                .catch(function (error) {
                    if (error && error.name === 'AbortError') {
                        return;
                    }
                    setState('Search is temporarily unavailable.');
                });
        }

        function openModal() {
            if ($modal) {
                $modal.modal('show');
            }
        }

        function handleInput() {
            var term = input.value.trim();
            window.clearTimeout(timer);

            if (term.length < 2) {
                setState('Type at least 2 characters to search.');
                return;
            }

            timer = window.setTimeout(function () {
                search(term);
            }, 250);
        }

        input.addEventListener('input', handleInput);

        if ($modal) {
            $modal.on('shown.bs.modal', function () {
                document.body.classList.add('global-search-modal-open');
                input.focus();
                if (input.value.trim().length < 2) {
                    setState('Type at least 2 characters to search.');
                } else {
                    handleInput();
                }
            });

            $modal.on('hidden.bs.modal', function () {
                document.body.classList.remove('global-search-modal-open');
                input.value = '';
                results.innerHTML = '';
            });
        }

        document.addEventListener('keydown', function (event) {
            if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
                event.preventDefault();
                openModal();
            }
        });
    })();
</script>
@endpush
@endonce
