{{-- resources/views/components/referrer-modal.blade.php --}}
<div class="modal fade" id="newReferrerModal" tabindex="-1" role="dialog" aria-labelledby="newReferrerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content modern-modal">
            <div class="modal-header modern-modal-header">
                <h5 class="modal-title" id="newReferrerModalLabel">
                    <i class="fas fa-user-tag mr-2"></i>Add New Referrer
                </h5>
                <button type="button" class="close modern-close" data-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body modern-modal-body">
                <div id="referrer-modal-errors" class="alert modern-alert-danger" style="display: none;">
                    <div class="alert-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="alert-content">
                        <strong>Validation Error</strong>
                        <div class="alert-description">Please correct the following errors:</div>
                        <ul id="referrer-error-list" class="alert-list"></ul>
                    </div>
                </div>

                <form id="referrerForm" class="modern-form">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group modern-form-group">
                                <label for="referrer-name" class="modern-label">
                                    <i class="fas fa-user mr-2"></i>Referrer Name
                                </label>
                                <input type="text"
                                       name="name"
                                       id="referrer-name"
                                       class="form-control modern-input"
                                       placeholder="Enter referrer name"
                                       required>
                                <div class="modern-input-focus"></div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group modern-form-group">
                                <label for="referrer-phone" class="modern-label">
                                    <i class="fas fa-phone mr-2"></i>Phone Number
                                </label>
                                <input type="text"
                                       name="phone"
                                       id="referrer-phone"
                                       class="form-control modern-input"
                                       placeholder="Enter phone number">
                                <div class="modern-input-focus"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group modern-form-group">
                                <label for="referrer-profession" class="modern-label">
                                    <i class="fas fa-briefcase mr-2"></i>Profession
                                </label>
                                <input type="text"
                                       name="profession"
                                       id="referrer-profession"
                                       class="form-control modern-input"
                                       placeholder="Enter profession">
                                <div class="modern-input-focus"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group modern-form-group">
                                <label for="referrer-note" class="modern-label">
                                    <i class="fas fa-sticky-note mr-2"></i>Note
                                </label>
                                <input type="text"
                                       name="note"
                                       id="referrer-note"
                                       class="form-control modern-input"
                                       placeholder="Optional note">
                                <div class="modern-input-focus"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group modern-form-group">
                                <label class="modern-label">
                                    <i class="fas fa-hand-holding-usd mr-2"></i>Compensation
                                </label>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="referrer-compensation" name="compensation_enabled" value="1">
                                    <label class="custom-control-label" for="referrer-compensation">Enable Compensation</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group modern-form-group">
                                <label class="modern-label">
                                    <i class="fas fa-gift mr-2"></i>Gift
                                </label>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="referrer-gift" name="gift_enabled" value="1">
                                    <label class="custom-control-label" for="referrer-gift">Enable Gift</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer modern-modal-footer">
                <button type="button" class="btn modern-btn-outline-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-2"></i>Cancel
                </button>
                <button type="button" class="btn modern-btn modern-btn-primary" id="saveReferrerBtn">
                    <i class="fas fa-save mr-2"></i>Save Referrer
                </button>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
$(document).ready(function() {
    $('#saveReferrerBtn').click(function() {
        const $btn = $(this);
        const originalText = $btn.html();

        if ($btn.data('submitting')) {
            return;
        }

        $('#referrer-modal-errors').slideUp();
        $('#referrer-error-list').empty();

        $btn.data('submitting', true);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Creating...');

        const formData = {
            name: $('#referrer-name').val().trim(),
            phone: $('#referrer-phone').val().trim(),
            profession: $('#referrer-profession').val().trim(),
            note: $('#referrer-note').val().trim(),
            compensation_enabled: $('#referrer-compensation').is(':checked') ? 1 : 0,
            gift_enabled: $('#referrer-gift').is(':checked') ? 1 : 0,
            _token: $('meta[name="csrf-token"]').attr('content')
        };

        $.ajax({
            url: "{{ route('referrers.store') }}",
            type: "POST",
            data: formData,
            success: function(response) {
                $('#newReferrerModal').modal('hide');

                const label = response.referrer.phone
                    ? `${response.referrer.name} - ${response.referrer.phone}`
                    : response.referrer.name;

                const newOption = new Option(label, response.referrer.id, true, true);
                $('#referrer_id').append(newOption).trigger('change');

                Swal.fire({
                    icon: response.was_existing ? 'info' : 'success',
                    title: response.was_existing ? 'Already Exists' : 'Success!',
                    text: response.was_existing
                        ? 'Referrer "' + response.referrer.name + '" already exists and was selected.'
                        : 'Referrer "' + response.referrer.name + '" created successfully',
                    timer: 2500,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end',
                    background: '#f8f9fa',
                    color: '#333'
                });

                $('#referrerForm')[0].reset();
            },
            error: function(xhr) {
                if(xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    $('#referrer-modal-errors').slideDown();

                    $.each(errors, function(key, value) {
                        $('#referrer-error-list').append('<li>' + value + '</li>');
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Something went wrong. Please try again.',
                        background: '#f8f9fa',
                        color: '#333'
                    });
                }
            },
            complete: function() {
                $btn.data('submitting', false);
                $btn.prop('disabled', false).html(originalText);
            }
        });
    });

    $('#newReferrerModal').on('hidden.bs.modal', function () {
        $('#referrerForm')[0].reset();
        $('#referrer-modal-errors').hide();
        $('#referrer-error-list').empty();
        $('#saveReferrerBtn').prop('disabled', false).html('<i class="fas fa-save mr-2"></i>Save Referrer');
    });
});
</script>
@endpush
