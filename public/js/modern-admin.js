/**
 * MODERN ADMIN SCRIPTS
 * Reusable JavaScript functions for all admin views
 */

// Global Admin Object
window.ModernAdmin = {
    // Configuration
    config: {
        select2: {
            width: '100%',
            dropdownAutoWidth: false,
            closeOnSelect: true
        },
        performance: {
            enableValidation: false,
            enableTooltips: false,
            enableKeyboardShortcuts: false,
            enableAlerts: true,
            enableModals: true
        },
        validation: {
            realTime: true,
            showIcons: true
        },
        animations: {
            duration: 0,
            easing: 'linear'
        }
    },

    // Initialize all modern admin features
    init: function() {
        if ($('.select2').length) {
            this.initSelect2();
        }
        this.initDataTables();
        if (this.config.performance.enableValidation && this.config.validation.realTime) {
            this.initValidation();
        }
        if (this.config.performance.enableKeyboardShortcuts) {
            this.initKeyboardNavigation();
        }
        if (this.config.performance.enableTooltips && ($('[data-toggle="tooltip"]').length || $('[title]').length)) {
            this.initTooltips();
        }
        if (this.config.performance.enableAlerts && ($('.modern-alert, .alert').length)) {
            this.initAlerts();
        }
        if (this.config.performance.enableModals && $('.modal').length) {
            this.initModals();
        }
        this.bindGlobalEvents();
    },

    // DataTables Defaults for faster rendering/search
    initDataTables: function() {
        if (!$.fn || !$.fn.dataTable) {
            return;
        }

        $.extend(true, $.fn.dataTable.defaults, {
            deferRender: true,
            searchDelay: 300
        });
    },

    // Select2 Initialization
    initSelect2: function() {
        $('.select2').each(function() {
            let options = Object.assign({}, ModernAdmin.config.select2, {
                placeholder: $(this).find('option[value=""]').text() || 'Select an option'
            });
            
            // If the select is inside a modal, set the dropdown parent
            const modalParent = $(this).closest('.modal');
            if (modalParent.length) {
                options.dropdownParent = modalParent;
                options.dropdownAutoWidth = false;
            }
            
            $(this).select2(options);
        });

        $(document).on('select2:open', function() {
            const searchField = document.querySelector('.select2-search__field');
            if (searchField) {
                searchField.focus();
            }
        });
    },

    // Real-time Form Validation
    initValidation: function() {
        if (!this.config.validation.realTime) return;

        // Real-time validation on input
        $(document).on('input change', 'input[required], select[required], textarea[required]', function() {
            ModernAdmin.validateField($(this));
        });

        // Validation on blur for better UX
        $(document).on('blur', 'input[required], select[required], textarea[required]', function() {
            ModernAdmin.validateField($(this));
        });

        // Select2 validation
        $(document).on('select2:select select2:unselect', '.select2', function() {
            ModernAdmin.validateField($(this));
        });
    },

    // Field Validation Function
    validateField: function(field) {
        const value = field.val();
        const fieldName = field.attr('name') || field.attr('id');
        const validationElement = $(`#${fieldName.replace('_', '-')}-validation`);
        
        if (field.prop('required') && (!value || value.trim() === '')) {
            field.addClass('is-invalid').removeClass('is-valid');
            if (validationElement.length) {
                validationElement.text('This field is required').addClass('invalid').removeClass('valid');
            }
            return false;
        } else {
            field.removeClass('is-invalid').addClass('is-valid');
            if (validationElement.length) {
                validationElement.text('Valid').addClass('valid').removeClass('invalid');
            }
            return true;
        }
    },

    // Validate entire form
    validateForm: function(formSelector) {
        let isValid = true;
        $(formSelector).find('input[required], select[required], textarea[required]').each(function() {
            if (!ModernAdmin.validateField($(this))) {
                isValid = false;
            }
        });
        return isValid;
    },

    // Enhanced Keyboard Navigation
    initKeyboardNavigation: function() {
        // Enhanced keyboard navigation for Select2
        $(document).on('keydown', '.select2', function(e) {
            if (e.keyCode === 13) { // Enter key
                e.preventDefault();
                $(this).select2('open');
            }
        });

        // Tab navigation enhancement
        $(document).on('keydown', 'input, select, textarea', function(e) {
            if (e.keyCode === 13 && !$(this).is('textarea')) { // Enter key (not in textarea)
                e.preventDefault();
                const tabindex = parseInt($(this).attr('tabindex'), 10);
                
                if (!isNaN(tabindex)) {
                    const nextElement = $(`[tabindex="${tabindex + 1}"]`);
                    
                    if (nextElement.length) {
                        if (nextElement.hasClass('select2-hidden-accessible')) {
                            nextElement.select2('open');
                        } else {
                            nextElement.focus();
                        }
                    }
                } else {
                    // Find next focusable element
                    const focusableElements = $('input, select, textarea, button').not(':disabled');
                    const currentIndex = focusableElements.index(this);
                    const nextElement = focusableElements.eq(currentIndex + 1);
                    
                    if (nextElement.length) {
                        nextElement.focus();
                    }
                }
            }
        });

        // Global keyboard shortcuts
        $(document).on('keydown', function(e) {
            // Ctrl + S to save (if save button exists)
            if (e.ctrlKey && e.keyCode === 83) {
                e.preventDefault();
                const saveBtn = $('#save-btn, #quick-save-btn, .save-btn').first();
                if (saveBtn.length && !saveBtn.prop('disabled')) {
                    saveBtn.click();
                }
            }
            
            // Ctrl + Enter to preview (if preview button exists)
            if (e.ctrlKey && e.keyCode === 13) {
                e.preventDefault();
                const previewBtn = $('#preview-btn, .preview-btn').first();
                if (previewBtn.length && !previewBtn.prop('disabled')) {
                    previewBtn.click();
                }
            }
            
            // Escape to close modals
            if (e.keyCode === 27) {
                $('.modal.show').modal('hide');
            }
        });
    },

    // Initialize Tooltips
    initTooltips: function() {
        // Initialize Bootstrap tooltips
        $('[data-toggle="tooltip"]').tooltip();
        
        // Initialize custom tooltips
        $('[title]').tooltip({
            placement: 'top',
            trigger: 'hover',
            delay: { show: 500, hide: 100 }
        });
    },

    // Alert Management
    initAlerts: function() {
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            $('.modern-alert, .alert').fadeOut(500);
        }, 5000);

        // Close button functionality
        $(document).on('click', '.alert-close, .close[data-dismiss="alert"]', function() {
            $(this).closest('.alert, .modern-alert').fadeOut(300);
        });
    },

    // Show custom alerts (SweetAlert preferred)
    showAlert: function(message, type = 'info', duration = 5000) {
        const normalizedType = type === 'danger' ? 'error' : type;
        const title = normalizedType.charAt(0).toUpperCase() + normalizedType.slice(1);

        if (window.Swal && typeof Swal.fire === 'function') {
            const options = {
                icon: normalizedType,
                title: title,
                text: message
            };
            if (duration > 0) {
                options.timer = duration;
                options.timerProgressBar = true;
                options.showConfirmButton = false;
            }
            Swal.fire(options);
            return;
        }

        const alertClass = normalizedType === 'warning' ? 'modern-alert-warning' :
                         normalizedType === 'error' ? 'modern-alert-error' :
                         normalizedType === 'success' ? 'modern-alert-success' : 'modern-alert-info';

        const iconClass = normalizedType === 'warning' ? 'fa-exclamation-triangle' :
                         normalizedType === 'error' ? 'fa-exclamation-circle' :
                         normalizedType === 'success' ? 'fa-check-circle' : 'fa-info-circle';

        const alertHtml = `
            <div class="alert modern-alert ${alertClass} alert-dismissible fade show" role="alert">
                <div class="alert-content">
                    <i class="fas ${iconClass} alert-icon"></i>
                    <div class="alert-message">
                        <strong>${title}!</strong> ${message}
                    </div>
                    <button type="button" class="alert-close" onclick="this.parentElement.parentElement.style.display='none'">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `;

        const targetContainer = $('.modern-card, .card, .container-fluid, body').first();
        targetContainer.prepend(alertHtml);

        if (duration > 0) {
            setTimeout(() => {
                $(`.modern-alert-${normalizedType}`).fadeOut(300);
            }, duration);
        }
    },

    // Modal Enhancements
    initModals: function() {
        // Fix for Bootstrap modals and Select2
        $.fn.modal.Constructor.prototype.enforceFocus = function() {
            var modal = this;
            $(document).on('focusin.modal', function(e) {
                if (modal.$element[0] !== e.target && 
                    !modal.$element.has(e.target).length && 
                    !$(e.target).closest('.select2-container').length) {
                    modal.$element.focus();
                }
            });
        };

        // Reinitialize Select2 when modals are shown
        $('.modal').on('shown.bs.modal', function() {
            $(this).find('.select2').each(function() {
                $(this).select2({
                    dropdownParent: $(this).closest('.modal'),
                    width: '100%'
                });
            });
        });

        // Clear loading states when modals are hidden
        $('.modal').on('hidden.bs.modal', function() {
            $('.btn.loading').removeClass('loading').prop('disabled', false);
        });
    },

    // Bind Global Events
    bindGlobalEvents: function() {
        // Loading state management for buttons
        $(document).on('click', '.btn[data-loading]', function() {
            ModernAdmin.setButtonLoading($(this), true);
        });

        // Character counter for textareas
        $(document).on('input', 'textarea[maxlength]', function() {
            ModernAdmin.updateCharacterCounter($(this));
        });

        // Auto-format number inputs on blur
        $(document).on('blur', 'input[type="number"].currency', function() {
            const value = parseFloat($(this).val()) || 0;
            $(this).val(value.toFixed(2));
        });

        // Pulse effect for successful actions
        $(document).on('click', '[data-pulse-on-click]', function() {
            $(this).addClass('pulse');
            setTimeout(() => $(this).removeClass('pulse'), 500);
        });
    },

    // Button Loading State
    setButtonLoading: function(button, isLoading) {
        if (isLoading) {
            button.addClass('loading').prop('disabled', true);
            if (button.data('loading-text')) {
                button.data('original-text', button.html());
                button.html(button.data('loading-text'));
            }
        } else {
            button.removeClass('loading').prop('disabled', false);
            if (button.data('original-text')) {
                button.html(button.data('original-text'));
            }
        }
    },

    // Character Counter
    updateCharacterCounter: function(textarea) {
        const maxLength = parseInt(textarea.attr('maxlength'));
        const currentLength = textarea.val().length;
        const counter = textarea.siblings('.char-counter').find('.char-count');
        
        if (counter.length) {
            counter.text(currentLength);
            
            if (currentLength > maxLength * 0.9) {
                counter.css('color', '#dc2626');
            } else if (currentLength > maxLength * 0.8) {
                counter.css('color', '#f59e0b');
            } else {
                counter.css('color', '#6b7280');
            }
        }
    },

    // Utility Functions
    utils: {
        // Format currency
        formatCurrency: function(amount, symbol = '৳') {
            return symbol + parseFloat(amount || 0).toFixed(2);
        },

        // Debounce function
        debounce: function(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        },

        // Smooth scroll to element
        scrollTo: function(element, offset = 100) {
            $('html, body').animate({
                scrollTop: $(element).offset().top - offset
            }, ModernAdmin.config.animations.duration);
        },

        // Generate unique ID
        generateId: function(prefix = 'id') {
            return prefix + '_' + Math.random().toString(36).substr(2, 9);
        },

        // Local storage with error handling
        storage: {
            set: function(key, value) {
                try {
                    localStorage.setItem(key, JSON.stringify(value));
                    return true;
                } catch (e) {
                    console.warn('Failed to save to localStorage:', e);
                    return false;
                }
            },

            get: function(key, defaultValue = null) {
                try {
                    const item = localStorage.getItem(key);
                    return item ? JSON.parse(item) : defaultValue;
                } catch (e) {
                    console.warn('Failed to read from localStorage:', e);
                    return defaultValue;
                }
            },

            remove: function(key) {
                try {
                    localStorage.removeItem(key);
                    return true;
                } catch (e) {
                    console.warn('Failed to remove from localStorage:', e);
                    return false;
                }
            }
        }
    },

    // Form Utilities
    form: {
        // Serialize form data to object
        toObject: function(formSelector) {
            const formArray = $(formSelector).serializeArray();
            const formObject = {};
            
            $.each(formArray, function(i, field) {
                if (formObject[field.name]) {
                    if (!Array.isArray(formObject[field.name])) {
                        formObject[field.name] = [formObject[field.name]];
                    }
                    formObject[field.name].push(field.value);
                } else {
                    formObject[field.name] = field.value;
                }
            });
            
            return formObject;
        },

        // Reset form with animation
        reset: function(formSelector) {
            const form = $(formSelector);
            form.addClass('pulse');
            form[0].reset();
            form.find('.select2').val(null).trigger('change');
            form.find('.is-valid, .is-invalid').removeClass('is-valid is-invalid');
            form.find('.field-validation').empty();
            
            setTimeout(() => form.removeClass('pulse'), 500);
        },

        // Auto-save functionality
        enableAutoSave: function(formSelector, key, interval = 30000) {
            const form = $(formSelector);
            let autoSaveTimer;

            function scheduleAutoSave() {
                clearTimeout(autoSaveTimer);
                autoSaveTimer = setTimeout(() => {
                    const formData = ModernAdmin.form.toObject(formSelector);
                    ModernAdmin.utils.storage.set(key, formData);
                }, interval);
            }

            form.on('input change', 'input, select, textarea', scheduleAutoSave);
            
            // Clear auto-save on form submission
            form.on('submit', () => {
                ModernAdmin.utils.storage.remove(key);
            });
        },

        // Load saved form data
        loadSavedData: function(formSelector, key) {
            const savedData = ModernAdmin.utils.storage.get(key);
            
            if (savedData && Object.keys(savedData).length > 0) {
                const form = $(formSelector);
                
                // Check if form is empty
                const hasData = form.find('input, select, textarea').filter(function() {
                    return $(this).val() !== '';
                }).length > 0;
                
                if (!hasData) {
                    if (confirm('Would you like to restore your previous draft?')) {
                        Object.keys(savedData).forEach(key => {
                            const element = form.find(`[name="${key}"]`);
                            if (element.length) {
                                if (element.is(':checkbox, :radio')) {
                                    element.filter(`[value="${savedData[key]}"]`).prop('checked', true);
                                } else {
                                    element.val(savedData[key]).trigger('change');
                                }
                            }
                        });
                        
                        ModernAdmin.showAlert('Draft restored successfully!', 'success', 3000);
                    }
                }
            }
        }
    }
};

// Auto-initialize when DOM is ready
$(document).ready(function() {
    ModernAdmin.init();
});

// Expose to global scope for manual initialization if needed
window.MA = ModernAdmin;
