<style>
    .rolex-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .rolex-title {
        margin: 0;
        font-size: 1.35rem;
        font-weight: 700;
        color: var(--app-primary-dark, #0f172a);
    }

    .rolex-subtitle {
        margin: 0.25rem 0 0;
        color: var(--app-muted, #64748b);
        font-size: 0.85rem;
    }

    .rolex-card {
        background: #fff;
        border: 1px solid color-mix(in srgb, var(--app-primary, #4f46e5) 18%, #ffffff);
        border-radius: 0.75rem;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
        overflow: hidden;
    }

    .rolex-card-header {
        padding: 0.9rem 1rem;
        border-bottom: 1px solid color-mix(in srgb, var(--app-primary, #4f46e5) 14%, #ffffff);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        background: #fff;
    }

    .rolex-card-title {
        margin: 0;
        font-size: 0.98rem;
        font-weight: 700;
        color: var(--app-primary-dark, #0f172a);
    }

    .rolex-card-subtitle {
        margin: 0.2rem 0 0;
        font-size: 0.8rem;
        color: var(--app-muted, #64748b);
    }

    .rolex-card-body {
        padding: 0.95rem;
    }

    .rolex-input-label {
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--app-primary-dark, #1f2937);
        margin-bottom: 0.4rem;
        display: inline-block;
    }

    .rolex-help {
        margin-top: 0.35rem;
        font-size: 0.74rem;
        color: var(--app-muted, #64748b);
    }

    .rolex-metrics {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 0.75rem;
    }

    .rolex-metric {
        border: 1px solid color-mix(in srgb, var(--app-primary, #4f46e5) 16%, #ffffff);
        border-radius: 0.6rem;
        padding: 0.6rem 0.7rem;
        background: linear-gradient(160deg,
            color-mix(in srgb, var(--app-primary, #4f46e5) 8%, #ffffff),
            color-mix(in srgb, var(--app-accent, #9333ea) 5%, #ffffff));
    }

    .rolex-metric-label {
        display: block;
        font-size: 0.72rem;
        color: var(--app-muted, #64748b);
        margin-bottom: 0.2rem;
    }

    .rolex-metric-value {
        display: block;
        font-size: 1rem;
        font-weight: 700;
        color: var(--app-primary-dark, #0f172a);
    }

    .rolex-perm-builder {
        display: grid;
        gap: 0.9rem;
    }

    .rolex-perm-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 0.7rem;
        align-items: center;
        justify-content: space-between;
    }

    .rolex-search {
        position: relative;
        min-width: 240px;
        flex: 1;
        max-width: 360px;
    }

    .rolex-search i {
        position: absolute;
        left: 11px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
    }

    .rolex-search input {
        padding-left: 33px;
        height: 36px;
        border-radius: 8px;
    }

    .rolex-toolbar-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
    }

    .rolex-toolbar-summary {
        color: var(--app-muted, #64748b);
        font-size: 0.81rem;
    }

    .rolex-toolbar-summary strong {
        color: var(--app-primary-dark, #0f172a);
    }

    .rolex-group-list {
        display: grid;
        gap: 0.75rem;
    }

    .rolex-group {
        border: 1px solid color-mix(in srgb, var(--app-primary, #4f46e5) 15%, #ffffff);
        border-radius: 0.7rem;
        overflow: hidden;
        background: #fff;
    }

    .rolex-group-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.7rem 0.85rem;
        background: linear-gradient(135deg,
            color-mix(in srgb, var(--app-primary, #4f46e5) 8%, #ffffff),
            color-mix(in srgb, var(--app-accent, #9333ea) 6%, #ffffff));
        border-bottom: 1px solid color-mix(in srgb, var(--app-primary, #4f46e5) 14%, #ffffff);
    }

    .rolex-group-title-btn {
        border: 0;
        background: transparent;
        color: var(--app-primary-dark, #0f172a);
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        font-weight: 700;
        padding: 0;
    }

    .rolex-group-title-btn i {
        color: var(--app-primary, #4f46e5);
    }

    .rolex-group-meta {
        color: var(--app-muted, #64748b);
        font-size: 0.76rem;
    }

    .rolex-group-check {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        margin: 0;
        color: var(--app-muted, #64748b);
        font-size: 0.78rem;
        font-weight: 600;
    }

    .rolex-group-check input {
        width: 0.95rem;
        height: 0.95rem;
    }

    .rolex-group-body {
        padding: 0.75rem;
    }

    .rolex-chip-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 0.55rem;
    }

    .rolex-chip {
        display: block;
        margin: 0;
    }

    .rolex-chip input {
        display: none;
    }

    .rolex-chip span {
        border: 1px solid color-mix(in srgb, var(--app-primary, #4f46e5) 18%, #ffffff);
        border-radius: 0.6rem;
        padding: 0.5rem 0.6rem;
        font-size: 0.8rem;
        color: var(--app-primary-dark, #1f2937);
        background: #fff;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        width: 100%;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .rolex-chip span i {
        color: #b2bac5;
    }

    .rolex-chip span:hover {
        border-color: color-mix(in srgb, var(--app-primary, #4f46e5) 40%, #ffffff);
        background: color-mix(in srgb, var(--app-primary, #4f46e5) 6%, #ffffff);
    }

    .rolex-chip input:checked + span {
        border-color: color-mix(in srgb, var(--app-primary, #4f46e5) 45%, #ffffff);
        background: color-mix(in srgb, var(--app-primary, #4f46e5) 14%, #ffffff);
        color: var(--app-primary-dark, #0f172a);
        box-shadow: inset 0 0 0 1px color-mix(in srgb, var(--app-primary, #4f46e5) 30%, #ffffff);
    }

    .rolex-chip input:checked + span i {
        color: var(--app-primary, #4f46e5);
    }

    .rolex-chip.rolex-chip-readonly span {
        cursor: default;
        background: color-mix(in srgb, var(--app-primary, #4f46e5) 6%, #ffffff);
    }

    .rolex-chip-hidden {
        display: none !important;
    }

    .rolex-group.rolex-group-hidden {
        display: none;
    }

    .rolex-group.rolex-group-collapsed .rolex-group-body {
        display: none;
    }

    .rolex-group.rolex-group-collapsed .rolex-group-title-btn .rolex-chevron {
        transform: rotate(-90deg);
    }

    .rolex-chevron {
        transition: transform 0.12s ease;
    }

    .rolex-sticky-actions {
        position: sticky;
        bottom: 0;
        margin-top: 1rem;
        padding: 0.75rem 1rem;
        border: 1px solid color-mix(in srgb, var(--app-primary, #4f46e5) 18%, #ffffff);
        border-radius: 0.7rem;
        background: rgba(255, 255, 255, 0.95);
        box-shadow: 0 -4px 16px rgba(15, 23, 42, 0.08);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: wrap;
        z-index: 5;
    }

    .rolex-sticky-actions__hint {
        font-size: 0.8rem;
        color: var(--app-muted, #64748b);
    }

    .rolex-table {
        margin: 0;
    }

    .rolex-table thead th {
        background: #f8fafc;
        color: var(--app-muted, #64748b);
        border-top: 0;
        border-bottom: 1px solid color-mix(in srgb, var(--app-primary, #4f46e5) 14%, #ffffff);
        font-size: 0.72rem;
        letter-spacing: 0.05rem;
        text-transform: uppercase;
        padding: 0.7rem 0.85rem;
    }

    .rolex-table tbody td {
        padding: 0.75rem 0.85rem;
        border-top: 1px solid color-mix(in srgb, var(--app-primary, #4f46e5) 10%, #ffffff);
        vertical-align: middle;
    }

    .rolex-role-name {
        font-weight: 600;
        color: var(--app-primary-dark, #0f172a);
    }

    .rolex-role-meta {
        display: block;
        font-size: 0.75rem;
        color: var(--app-muted, #64748b);
    }

    .rolex-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        border-radius: 999px;
        padding: 0.24rem 0.6rem;
        background: color-mix(in srgb, var(--app-primary, #4f46e5) 14%, #ffffff);
        color: var(--app-primary-dark, #0f172a);
        font-size: 0.76rem;
        font-weight: 600;
        border: 1px solid color-mix(in srgb, var(--app-primary, #4f46e5) 28%, #ffffff);
    }

    .rolex-empty {
        text-align: center;
        color: var(--app-muted, #64748b);
        padding: 2rem 1rem;
    }

    .rolex-empty i {
        font-size: 1.8rem;
        color: color-mix(in srgb, var(--app-primary, #4f46e5) 25%, #ffffff);
        margin-bottom: 0.6rem;
    }

    .rolex-inline-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
        align-items: center;
    }

    @supports not (color: color-mix(in srgb, #000 50%, #fff)) {
        .rolex-card,
        .rolex-group,
        .rolex-sticky-actions,
        .rolex-chip span,
        .rolex-metric {
            border-color: rgba(79, 70, 229, 0.2);
        }

        .rolex-chip input:checked + span,
        .rolex-badge {
            background: rgba(79, 70, 229, 0.14);
        }
    }

    @media (max-width: 991.98px) {
        .rolex-search {
            max-width: none;
        }

        .rolex-chip-grid {
            grid-template-columns: repeat(auto-fill, minmax(155px, 1fr));
        }
    }
</style>
