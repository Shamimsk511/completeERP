<div class="input-group">
    <input type="date" 
           class="form-control due-date-picker" 
           value="{{ $customer->due_date }}"
           data-customer-id="{{ $customer->id }}">
    <div class="input-group-append">
        <span class="input-group-text">
            <i class="far fa-calendar-alt"></i>
        </span>
    </div>
</div>
