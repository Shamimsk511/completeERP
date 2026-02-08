<p>
    <strong>Last Transaction:</strong><br>
    {{ $customer->last_transaction_date }}<br>
    <small>{{ ucfirst($customer->last_transaction_type) }}</small>
</p>
<p>
    <strong>Last Invoice:</strong><br>
    {{ $customer->last_invoice_date }}
</p>
<p>
    <strong>Last Payment:</strong><br>
    {{ $customer->last_payment_date }}
</p>
<p>
    <small>{{ $customer->days_since_last_transaction }} days since last transaction</small>
</p>
