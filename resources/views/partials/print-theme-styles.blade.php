<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Montserrat:wght@500;700;800&family=Playfair+Display:wght@500;700&family=Space+Grotesk:wght@500;600;700&family=Nunito:wght@500;700;800&display=swap" rel="stylesheet">
<style>
    @page {
        size: A4 portrait;
    }

    :root {
        --print-sheet-width: 184mm;
        --print-sheet-padding: 1.2mm;
    }

    body.print-theme {
        margin: 0;
        padding: 0;
        color: #1f2937;
        background: #ffffff;
    }

    body.print-theme :is(.invoice-container, .challan-container, .ledger-container, .receipt-container, .return-container, .print-container) {
        width: 100%;
        max-width: var(--print-sheet-width);
        margin: 0 auto;
        box-sizing: border-box;
        padding: var(--print-sheet-padding);
        position: relative;
        overflow: hidden;
        min-height: 262mm;
        border: none !important;
        outline: none !important;
        box-shadow: none !important;
    }

    body.print-theme .invoice-header,
    body.print-theme .challan-header,
    body.print-theme .ledger-header {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        flex-wrap: nowrap;
        page-break-inside: avoid;
        break-inside: avoid;
    }

    body.print-theme .company-info {
        min-width: 0;
        flex: 1 1 auto;
    }

    body.print-theme .company-details {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-size: 10px;
        line-height: 1.2;
    }

    body.print-theme .bill-section {
        display: grid;
        grid-template-columns: minmax(0, 1.15fr) minmax(0, 0.85fr);
        gap: 8px;
        page-break-inside: avoid;
        break-inside: avoid;
    }

    body.print-theme table {
        width: 100%;
        border-collapse: collapse;
    }

    body.print-theme table thead th {
        background: #f3f4f6;
    }

    body.print-theme .document-title {
        letter-spacing: 0.6px;
    }

    body.print-theme.template-standard {
        font-family: 'Inter', Arial, sans-serif;
    }

    body.print-theme.template-standard :is(.invoice-container, .challan-container, .ledger-container, .receipt-container, .return-container, .print-container) {
        border-radius: 6px;
    }

    body.print-theme.template-modern {
        font-family: 'Space Grotesk', 'Inter', Arial, sans-serif;
        background: linear-gradient(180deg, #f8fbff 0%, #ecf4ff 100%);
    }

    body.print-theme.template-modern :is(.invoice-container, .challan-container, .ledger-container, .receipt-container, .return-container, .print-container) {
        border-radius: 12px;
    }

    body.print-theme.template-modern :is(.invoice-container, .challan-container, .ledger-container, .receipt-container, .return-container, .print-container)::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 14mm;
        background: linear-gradient(90deg, #0ea5e9 0%, #2563eb 55%, #22c55e 100%);
        z-index: 0;
        opacity: 0.16;
        pointer-events: none;
    }

    body.print-theme.template-modern .document-title,
    body.print-theme.template-modern .table thead th,
    body.print-theme.template-modern table thead th {
        background: #1d4ed8 !important;
        color: #ffffff !important;
    }

    body.print-theme.template-simple {
        font-family: 'Inter', Arial, sans-serif;
        color: #111827;
    }

    body.print-theme.template-simple :is(.invoice-container, .challan-container, .ledger-container, .receipt-container, .return-container, .print-container) {
        border-radius: 0;
        box-shadow: none;
        background: #ffffff;
    }

    body.print-theme.template-simple .document-title,
    body.print-theme.template-simple .table thead th,
    body.print-theme.template-simple table thead th {
        background: #111827 !important;
        color: #ffffff !important;
        border-radius: 0 !important;
    }

    body.print-theme.template-simple .invoice-header,
    body.print-theme.template-simple .challan-header,
    body.print-theme.template-simple .ledger-header {
        border-bottom: 1px solid #111827;
        padding-bottom: 3mm;
    }

    body.print-theme.template-bold {
        font-family: 'Montserrat', 'Inter', Arial, sans-serif;
        color: #0b1324;
    }

    body.print-theme.template-bold :is(.invoice-container, .challan-container, .ledger-container, .receipt-container, .return-container, .print-container) {
        border-radius: 0;
        box-shadow: none;
        background: #ffffff;
        padding-left: 10mm !important;
    }

    body.print-theme.template-bold :is(.invoice-container, .challan-container, .ledger-container, .receipt-container, .return-container, .print-container)::before {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        left: 0;
        width: 8mm;
        background: #0b1324;
        z-index: 0;
        pointer-events: none;
    }

    body.print-theme.template-bold .document-title,
    body.print-theme.template-bold .table thead th,
    body.print-theme.template-bold table thead th {
        background: #0b1324 !important;
        color: #ffffff !important;
        text-transform: uppercase;
        letter-spacing: 1.1px;
        font-weight: 700;
    }

    body.print-theme.template-elegant {
        font-family: 'Playfair Display', Georgia, serif;
        color: #3b2f2f;
        background: #fffcf5;
    }

    body.print-theme.template-elegant :is(.invoice-container, .challan-container, .ledger-container, .receipt-container, .return-container, .print-container) {
        border-radius: 10px;
        background: #fffaf0;
    }

    body.print-theme.template-elegant .document-title,
    body.print-theme.template-elegant .table thead th,
    body.print-theme.template-elegant table thead th {
        background: #7a5c2e !important;
        color: #ffffff !important;
        letter-spacing: 0.9px;
    }

    body.print-theme.template-imaginative {
        font-family: 'Nunito', 'Inter', Arial, sans-serif;
        background: linear-gradient(135deg, #fff1f2 0%, #eff6ff 38%, #ecfeff 100%);
    }

    body.print-theme.template-imaginative :is(.invoice-container, .challan-container, .ledger-container, .receipt-container, .return-container, .print-container) {
        border-radius: 18px;
        background: #ffffff;
        background-image:
            linear-gradient(145deg, rgba(99, 102, 241, 0.12) 0 16mm, transparent 16mm),
            linear-gradient(-35deg, rgba(16, 185, 129, 0.12) 0 14mm, transparent 14mm);
        background-repeat: no-repeat;
        background-position: left top, right bottom;
    }

    body.print-theme.template-imaginative .document-title,
    body.print-theme.template-imaginative .table thead th,
    body.print-theme.template-imaginative table thead th {
        background: linear-gradient(135deg, #ec4899 0%, #6366f1 58%, #06b6d4 100%) !important;
        color: #ffffff !important;
    }

    @media print {
        html,
        body {
            width: auto !important;
            margin: 0 !important;
            padding: 0 !important;
            background: #ffffff !important;
            print-color-adjust: exact;
            -webkit-print-color-adjust: exact;
        }

        .no-print,
        .print-actions,
        .d-print-none {
            display: none !important;
        }

        body.print-theme :is(.invoice-container, .challan-container, .ledger-container, .receipt-container, .return-container, .print-container) {
            width: 100% !important;
            max-width: var(--print-sheet-width) !important;
            margin: 0 auto !important;
            padding: 0.8mm !important;
            box-sizing: border-box !important;
            min-height: 268mm !important;
            transform: none !important;
        }

        body.print-theme .invoice-header,
        body.print-theme .challan-header,
        body.print-theme .ledger-header {
            flex-direction: row !important;
            align-items: flex-start !important;
            gap: 6px !important;
        }

        body.print-theme .company-details {
            font-size: 9.2px !important;
            line-height: 1.2 !important;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        body.print-theme table th,
        body.print-theme table td {
            font-size: 10px !important;
            padding: 3.5px 4.5px !important;
        }

        body.print-theme .bill-section {
            grid-template-columns: minmax(0, 1.08fr) minmax(0, 0.92fr) !important;
            gap: 5px !important;
        }
    }
</style>
