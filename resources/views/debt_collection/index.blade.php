@extends('layouts.modern-admin')

@section('title', 'Debt Collection')
@section('page_title', 'Debt Collection')

@section('header_actions')
    <button class="btn modern-btn modern-btn-success" id="open-call-modal">
        <i class="fas fa-phone"></i> Log Call
    </button>
@stop

@section('page_content')
    <div class="card modern-card mb-3">
        <div class="card-body">
            <div class="row align-items-end">
                <div class="col-md-5">
                    <label for="search-input" class="form-label">Search Customer</label>
                    <input type="text" id="search-input" class="form-control modern-input" placeholder="Name or phone">
                </div>
                <div class="col-md-3">
                    <label for="min-days" class="form-label">Min Days Since Activity</label>
                    <input type="number" id="min-days" class="form-control modern-input" min="0" placeholder="e.g. 30">
                </div>
                <div class="col-md-4 text-right">
                    <button class="btn modern-btn modern-btn-primary" id="apply-filters">
                        <i class="fas fa-filter"></i> Apply
                    </button>
                    <button class="btn modern-btn modern-btn-light" id="clear-filters">
                        <i class="fas fa-times"></i> Clear
                    </button>
                </div>
            </div>
            <p class="text-muted mt-2 mb-0">
                Sorted by longest time since last contact or transaction. Use “Log Call” to record promises.
            </p>
        </div>
    </div>

    <div class="card modern-card">
        <div class="card-header modern-header success-header">
            <h3 class="card-title">
                <i class="fas fa-users"></i> Inactive Customers
            </h3>
        </div>
        <div class="card-body table-responsive">
            <table id="debt-simple-table" class="table modern-table mb-0">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Outstanding</th>
                        <th>Last Transaction</th>
                        <th>Last Contact</th>
                        <th>Last Activity</th>
                        <th>Days Since</th>
                        <th>Last Promise</th>
                        <th>Promises</th>
                        <th>Promise Changes</th>
                        <th>Last Note</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="callModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Log Customer Call</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="callForm">
                    <div class="modal-body">
                        <input type="hidden" id="call-customer-id">

                        <div class="form-group">
                            <label>Customer</label>
                            <select class="form-control" id="call-customer-select">
                                <option value="">Select a customer...</option>
                            </select>
                            <div class="alert alert-info mt-2 mb-0" id="call-customer-info" style="display: none;">
                                <strong id="call-customer-name"></strong><br>
                                <span id="call-customer-phone"></span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Call Status <span class="text-danger">*</span></label>
                            <select class="form-control" id="call-status" required>
                                <option value="successful">Successful Contact</option>
                                <option value="missed">No Answer/Missed</option>
                                <option value="busy">Line Busy</option>
                                <option value="disconnected">Number Disconnected</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Payment Promise Date</label>
                            <input type="date" class="form-control" id="payment-promise-date">
                        </div>

                        <div class="form-group">
                            <label>Notes</label>
                            <textarea class="form-control" id="call-notes" rows="4" placeholder="Call details, promises, next steps..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Save Call Log
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('additional_css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
@stop

@section('additional_js')
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
    <script>
        $(document).ready(function() {
            const moneyFormatter = new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            const dateFormatter = new Intl.DateTimeFormat('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });

            function formatDate(value) {
                if (!value) {
                    return 'Never';
                }
                const date = new Date(value);
                if (Number.isNaN(date.getTime())) {
                    return value;
                }
                return dateFormatter.format(date);
            }

            function fetchFilters(d) {
                d.simple = 1;
                d.search = $('#search-input').val();
                d.min_days = $('#min-days').val();
            }

            const table = $('#debt-simple-table').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                ajax: {
                    url: '{{ route('debt-collection.get-customers') }}',
                    data: fetchFilters
                },
                order: [[5, 'desc']],
                columns: [
                    {
                        data: 'customer_name',
                        name: 'customer_name',
                        render: function(data, type, row) {
                            const phone = row.phone ? `<div class="text-muted small">${row.phone}</div>` : '';
                            return `<div>${data}${phone}</div>`;
                        }
                    },
                    {
                        data: 'outstanding_balance',
                        name: 'outstanding_balance',
                        render: function(data) { return `৳${moneyFormatter.format(data || 0)}`; }
                    },
                    {
                        data: 'last_transaction_at',
                        name: 'last_transaction_at',
                        render: function(data) { return formatDate(data); }
                    },
                    {
                        data: 'last_call_at',
                        name: 'last_call_at',
                        render: function(data) { return formatDate(data); }
                    },
                    {
                        data: 'last_activity_at',
                        name: 'last_activity_at',
                        render: function(data) { return formatDate(data); }
                    },
                    {
                        data: 'days_since_activity',
                        name: 'days_since_activity_sort',
                        render: function(data) { return data !== null ? `${data} days` : 'Never'; }
                    },
                    {
                        data: 'last_promise_date',
                        name: 'last_promise_date',
                        render: function(data) { return data ? formatDate(data) : '-'; }
                    },
                    {
                        data: 'promise_count',
                        name: 'promise_count'
                    },
                    {
                        data: 'promise_change_count',
                        name: 'promise_change_count'
                    },
                    {
                        data: 'last_note',
                        name: 'last_note',
                        render: function(data) {
                            if (!data) return '-';
                            const trimmed = data.length > 40 ? data.substring(0, 40) + '…' : data;
                            return `<span title="${data.replace(/"/g, '&quot;')}">${trimmed}</span>`;
                        }
                    },
                    {
                        data: 'customer_id',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return `
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-success btn-sm log-call-btn"
                                            data-customer-id="${data}"
                                            data-customer-name="${row.customer_name}"
                                            data-customer-phone="${row.phone || ''}">
                                        <i class="fas fa-phone"></i>
                                    </button>
                                    <a href="/debt-collection/customers/${data}/call-history" class="btn btn-info btn-sm">
                                        <i class="fas fa-history"></i>
                                    </a>
                                </div>
                            `;
                        }
                    }
                ]
            });

            $('#apply-filters').on('click', function() {
                table.ajax.reload();
            });

            $('#clear-filters').on('click', function() {
                $('#search-input').val('');
                $('#min-days').val('');
                table.ajax.reload();
            });

            $('#search-input').on('keyup', function(e) {
                if (e.key === 'Enter') {
                    table.ajax.reload();
                }
            });

            $('#open-call-modal').on('click', function() {
                $('#call-customer-id').val('');
                $('#call-customer-select').val('');
                $('#call-customer-name').text('');
                $('#call-customer-phone').text('');
                $('#call-customer-info').hide();
                $('#callForm')[0].reset();
                loadCustomersForDropdown();
                $('#callModal').modal('show');
            });

            $(document).on('click', '.log-call-btn', function() {
                const customerId = $(this).data('customer-id');
                $('#callForm')[0].reset();
                $('#call-customer-id').val(customerId);
                const selectedName = $(this).data('customer-name');
                const selectedPhone = $(this).data('customer-phone');
                const select = $('#call-customer-select');
                select.find(`option[value="${customerId}"]`).remove();
                select.append(new Option(`${selectedName}${selectedPhone ? ' - ' + selectedPhone : ''}`, customerId, true, true));
                $('#call-customer-select').val(customerId);
                $('#call-customer-name').text($(this).data('customer-name'));
                const phone = $(this).data('customer-phone');
                $('#call-customer-phone').text(phone ? `Phone: ${phone}` : '');
                $('#call-customer-info').show();
                loadCustomersForDropdown();
                $('#callModal').modal('show');
            });

            $('#call-customer-select').on('change', function() {
                const selectedId = $(this).val();
                if (!selectedId) {
                    $('#call-customer-id').val('');
                    $('#call-customer-info').hide();
                    return;
                }
                const selected = window.callCustomers?.find(c => String(c.id) === String(selectedId));
                if (selected) {
                    $('#call-customer-id').val(selected.id);
                    $('#call-customer-name').text(selected.name);
                    $('#call-customer-phone').text(selected.phone ? `Phone: ${selected.phone}` : '');
                    $('#call-customer-info').show();
                }
            });

            function loadCustomersForDropdown() {
                $.ajax({
                    url: '{{ route('debt-collection.get-customers') }}',
                    method: 'GET',
                    data: { for_dropdown: 1, length: 200 },
                    success: function(response) {
                        if (!response.success) {
                            return;
                        }
                        window.callCustomers = response.data || [];
                        const select = $('#call-customer-select');
                        const current = $('#call-customer-id').val();
                        select.empty().append('<option value="">Select a customer...</option>');
                        window.callCustomers.forEach(function(customer) {
                            const phone = customer.phone ? ` - ${customer.phone}` : '';
                            const label = `${customer.name}${phone}`;
                            const option = $('<option></option>').val(customer.id).text(label);
                            if (String(customer.id) === String(current)) {
                                option.prop('selected', true);
                            }
                            select.append(option);
                        });
                        if (current && !select.find(`option[value="${current}"]`).length) {
                            const fallbackName = $('#call-customer-name').text() || 'Selected Customer';
                            const fallbackPhone = $('#call-customer-phone').text().replace('Phone: ', '');
                            const fallbackLabel = `${fallbackName}${fallbackPhone ? ' - ' + fallbackPhone : ''}`;
                            select.append(new Option(fallbackLabel, current, true, true));
                        }
                        if (current) {
                            $('#call-customer-select').val(current).trigger('change');
                        }
                    }
                });
            }

            $('#callForm').on('submit', function(e) {
                e.preventDefault();

                const customerId = $('#call-customer-id').val() || $('#call-customer-select').val();
                if (!customerId) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Select a customer',
                        text: 'Please choose a customer first.'
                    });
                    return;
                }

                const formData = {
                    call_status: $('#call-status').val(),
                    notes: $('#call-notes').val() || '',
                    payment_promise_date: $('#payment-promise-date').val() || '',
                    _token: $('meta[name="csrf-token"]').attr('content')
                };

                const submitBtn = $(this).find('button[type="submit"]');
                const originalText = submitBtn.html();
                submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Saving...').prop('disabled', true);

                $.ajax({
                    url: `/debt-collection/customers/${customerId}/call`,
                    method: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            $('#callModal').modal('hide');
                            table.ajax.reload(null, false);
                            Swal.fire({
                                icon: 'success',
                                title: 'Saved',
                                text: 'Call logged successfully',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Failed to log call'
                            });
                        }
                    },
                    error: function(xhr) {
                        const message = xhr.responseJSON?.message || 'Failed to log call';
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: message
                        });
                    },
                    complete: function() {
                        submitBtn.html(originalText).prop('disabled', false);
                    }
                });
            });
        });
    </script>
@stop
