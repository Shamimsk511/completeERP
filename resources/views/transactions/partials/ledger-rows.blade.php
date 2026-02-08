{{-- resources/views/transactions/partials/ledger-rows.blade.php --}}

@php
    $runningBalance = $customer->opening_balance;
@endphp

<!-- Opening Balance Row -->
<tr class="opening-row">
    <td class="date-cell">
        <div class="date-info">
            <span class="date">Opening</span>
            <small>Balance</small>
        </div>
    </td>
    <td><strong>Opening Balance</strong></td>
    <td>-</td>
    <td>-</td>
    <td>-</td>
    <td>-</td>
    <td>-</td>
    <td class="balance-cell">
        <span class="balance-amount">৳{{ number_format($runningBalance, 2) }}</span>
    </td>
    <td>-</td>
</tr>

<!-- Transaction Rows -->
@foreach($transactions as $transaction)
    @php
        if ($transaction->type == 'debit') {
            $effectiveAmount = $transaction->amount + ($transaction->discount_amount ?? 0);
            $runningBalance -= $effectiveAmount;
        } else {
            $runningBalance += $transaction->amount;
        }
    @endphp
    <tr class="transaction-row {{ $transaction->has_discount ? 'has-discount' : '' }}" 
        data-type="{{ $transaction->type }}" 
        data-method="{{ $transaction->method }}"
        data-date="{{ $transaction->created_at->format('Y-m-d') }}">
        <td class="date-cell">
            <div class="date-info">
                <span class="date">{{ $transaction->created_at->format('M d') }}</span>
                <small>{{ $transaction->created_at->format('h:i A') }}</small>
            </div>
        </td>
        <td class="purpose-cell">
            <div class="purpose-content">
                {{ $transaction->purpose }}
                @if($transaction->has_discount && $transaction->discount_reason)
                    <small class="discount-reason">
                        <i class="fas fa-tag"></i> {{ $transaction->discount_reason }}
                    </small>
                @endif
            </div>
        </td>
        <td>
            <span class="method-badge">{{ ucfirst(str_replace('_', ' ', $transaction->method)) }}</span>
        </td>
        
        @if($transaction->type == 'debit')
            <td class="amount payment">৳{{ number_format($transaction->amount, 2) }}</td>
            <td class="amount discount">
                @if($transaction->has_discount)
                    <i class="fas fa-tag"></i> ৳{{ number_format($transaction->discount_amount, 2) }}
                @else
                    -
                @endif
            </td>
            <td class="amount debit">৳{{ number_format($effectiveAmount, 2) }}</td>
            <td>-</td>
        @else
            <td>-</td>
            <td>-</td>
            <td>-</td>
            <td class="amount credit">৳{{ number_format($transaction->amount, 2) }}</td>
        @endif
        
        <td class="balance-cell">
            <div class="balance-info">
                <span class="balance-amount {{ $runningBalance > 0 ? 'due' : ($runningBalance < 0 ? 'advance' : 'clear') }}">
                    ৳{{ number_format($runningBalance, 2) }}
                </span>
                <span class="status-mini {{ $runningBalance > 0 ? 'due' : ($runningBalance < 0 ? 'advance' : 'clear') }}">
                    {{ $runningBalance > 0 ? 'Due' : ($runningBalance < 0 ? 'Adv' : 'Clear') }}
                </span>
            </div>
        </td>
        <td class="actions-cell">
            <div class="action-buttons">
                <a href="{{ route('transactions.show', $transaction) }}" 
                   class="action-btn view" title="View">
                    <i class="fas fa-eye"></i>
                </a>
                <a href="{{ route('transactions.edit', $transaction) }}" 
                   class="action-btn edit" title="Edit">
                    <i class="fas fa-edit"></i>
                </a>
                @if($transaction->has_discount)
                    <span class="discount-tag" title="Discount Applied">
                        <i class="fas fa-tag"></i>
                    </span>
                @endif
            </div>
        </td>
    </tr>
@endforeach