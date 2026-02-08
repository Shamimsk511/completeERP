<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Ledger</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #222; }
        h2 { margin-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background: #f5f5f5; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <h2>{{ $employee->name }} Ledger</h2>
    <p>Period: {{ $fromDate }} to {{ $toDate }}</p>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Reference</th>
                <th>Source</th>
                <th>Particulars</th>
                <th>Debit</th>
                <th>Credit</th>
                <th>Balance</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="6"><strong>Opening Balance</strong></td>
                <td>
                    {{ number_format($ledger['opening_balance']['balance'], 2) }}
                    {{ strtoupper(substr($ledger['opening_balance']['type'], 0, 2)) }}
                </td>
            </tr>
            @foreach($ledger['entries'] as $entry)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($entry['date'])->format('d M, Y') }}</td>
                    <td>{{ $entry['reference'] ?? '-' }}</td>
                    <td>{{ $entry['source_type'] ?? '-' }}</td>
                    <td>{{ $entry['particulars'] }}</td>
                    <td class="text-right">{{ $entry['debit'] > 0 ? number_format($entry['debit'], 2) : '-' }}</td>
                    <td class="text-right">{{ $entry['credit'] > 0 ? number_format($entry['credit'], 2) : '-' }}</td>
                    <td class="text-right">
                        {{ number_format($entry['running_balance'], 2) }}
                        {{ strtoupper(substr($entry['balance_type'], 0, 2)) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4">Totals</th>
                <th class="text-right">{{ number_format($ledger['totals']['debit'], 2) }}</th>
                <th class="text-right">{{ number_format($ledger['totals']['credit'], 2) }}</th>
                <th class="text-right">
                    {{ number_format($ledger['closing_balance']['balance'], 2) }}
                    {{ strtoupper(substr($ledger['closing_balance']['type'], 0, 2)) }}
                </th>
            </tr>
        </tfoot>
    </table>
</body>
</html>
