<strong>{{ $customer->name }}</strong>
<br>
<small>Phone: {{ $customer->phone }}</small>
<br>
<small>Balance: {{ number_format($customer->outstanding_balance, 2) }}</small>
