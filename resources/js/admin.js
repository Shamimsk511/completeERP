// Load jQuery and Bootstrap first
import $ from 'jquery';
window.$ = window.jQuery = $;

import 'bootstrap';

// Lazy load heavy components
const loadDataTables = () => import('datatables.net-bs4');
const loadSelect2 = () => import('select2');
const loadSweetAlert = () => import('sweetalert2');

// Initialize core functionality immediately
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Initialize alerts with auto-hide
    setTimeout(() => $('.alert').fadeOut(500), 5000);
    
    // Initialize basic form interactions
    initBasicFormFeatures();
});

// Lazy load components when needed
function initLazyComponents() {
    // Load DataTables only if table exists
    if (document.querySelector('#invoices-table')) {
        loadDataTables().then(module => {
            initDataTable();
        });
    }
    
    // Load Select2 only if select2 elements exist
    if (document.querySelector('.select2')) {
        loadSelect2().then(module => {
            $('.select2').select2({ width: '100%' });
        });
    }
}

// Call lazy loading after page is interactive
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initLazyComponents);
} else {
    initLazyComponents();
}

function initBasicFormFeatures() {
    // Lightweight form validation
    $('form').on('submit', function(e) {
        const requiredFields = $(this).find('[required]');
        let isValid = true;
        
        requiredFields.each(function() {
            if (!$(this).val()) {
                $(this).addClass('is-invalid');
                isValid = false;
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            $('.is-invalid').first().focus();
        }
    });
}

function initDataTable() {
    $('#invoices-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '/invoices/data',
            data: function(d) {
                // Add filter parameters
                d.search = $('#search-input').val();
                d.customer_id = $('#customer-filter').val();
                d.payment_status = $('#payment-status-filter').val();
                d.delivery_status = $('#delivery-status-filter').val();
                d.from_date = $('#from-date').val();
                d.to_date = $('#to-date').val();
            }
        },
        pageLength: 25,
        responsive: true,
        columns: [
            // Define columns
        ],
        drawCallback: function() {
            // Reinitialize tooltips for new content
            $('[data-toggle="tooltip"]').tooltip();
        }
    });
}

// Export functions that might be needed globally
window.AdminApp = {
    initDataTable,
    loadSweetAlert
};