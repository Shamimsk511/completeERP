<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll Slip</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #222; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 6px; }
        th { background: #f5f5f5; }
    </style>
</head>
<body>
    <h2>Payroll Slip</h2>
    <p><strong>Employee:</strong> {{ $payroll->employee->name }}</p>
    <p><strong>Period:</strong> {{ $payroll->period_start->format('d M, Y') }} - {{ $payroll->period_end->format('d M, Y') }}</p>

    <table>
        <tr><th>Basic Salary</th><td>৳{{ number_format($payroll->basic_salary, 2) }}</td></tr>
        <tr><th>Present Days</th><td>{{ $payroll->present_days }}</td></tr>
        <tr><th>Absent Days</th><td>{{ $payroll->absent_days }}</td></tr>
        <tr><th>Paid Absent Days</th><td>{{ $payroll->paid_absent_days }}</td></tr>
        <tr><th>Bonus</th><td>৳{{ number_format($payroll->bonus_amount, 2) }}</td></tr>
        <tr><th>Other Bonus</th><td>৳{{ number_format($payroll->other_bonus_amount, 2) }}</td></tr>
        <tr><th>Increment</th><td>৳{{ number_format($payroll->increment_amount, 2) }}</td></tr>
        <tr><th>Deduction</th><td>৳{{ number_format($payroll->deduction_amount, 2) }}</td></tr>
        <tr><th>Advance Deduction</th><td>৳{{ number_format($payroll->advance_deduction, 2) }}</td></tr>
        <tr><th>Gross Salary</th><td>৳{{ number_format($payroll->gross_salary, 2) }}</td></tr>
        <tr><th>Net Pay</th><td>৳{{ number_format($payroll->net_pay, 2) }}</td></tr>
        <tr><th>Status</th><td>{{ ucfirst($payroll->status) }}</td></tr>
    </table>
</body>
</html>
