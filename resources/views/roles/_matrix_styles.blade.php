<style>
    .permission-counter-box {
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.85);
        padding: 0.7rem 0.9rem;
        text-align: center;
    }

    .permission-counter-label {
        color: var(--app-muted, #64748b);
        font-size: 0.76rem;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }

    .permission-counter-value {
        font-size: 1.35rem;
        line-height: 1.1;
        font-weight: 700;
        color: var(--app-primary-dark, #0f172a);
        margin: 0.2rem 0;
    }

    .permission-search-wrap {
        position: relative;
        min-width: 250px;
        max-width: 380px;
        width: 100%;
    }

    .permission-search-wrap i {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
    }

    .permission-search-wrap input {
        height: 36px;
        padding-left: 34px;
    }

    .permission-matrix-wrap {
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 10px;
        overflow: auto;
        max-height: 65vh;
    }

    .permission-matrix thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        background: #f8fafc;
        border-top: none;
        border-bottom: 1px solid rgba(15, 23, 42, 0.08);
        vertical-align: middle;
    }

    .permission-matrix td,
    .permission-matrix th {
        padding: 0.48rem 0.5rem;
        font-size: 0.82rem;
    }

    .permission-matrix td {
        border-color: rgba(15, 23, 42, 0.07);
    }

    .permission-row:hover {
        background: rgba(15, 23, 42, 0.02);
    }

    .module-label {
        font-weight: 600;
        color: var(--app-primary-dark, #0f172a);
        line-height: 1.2;
    }

    .module-meta {
        font-size: 0.72rem;
        color: var(--app-muted, #64748b);
        margin-top: 0.2rem;
    }

    .action-col-label {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 0.28rem;
    }

    .action-selector,
    .module-selector {
        width: 15px;
        height: 15px;
    }

    .permission-matrix .custom-control {
        min-height: 1rem;
    }

    .permission-matrix .custom-control-label::before,
    .permission-matrix .custom-control-label::after {
        top: 0.02rem;
    }
</style>
