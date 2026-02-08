@push('js')
<script>
    (function() {
        const modalEl = document.getElementById('expenseCategoryModal');
        const form = document.getElementById('expense-category-form');
        const errors = document.getElementById('expense-category-errors');
        const nameInput = document.getElementById('category_name');
        const codeInput = document.getElementById('category_code');

        if (!modalEl || !form) {
            return;
        }

        const toCode = (value) => {
            const base = (value || '')
                .toString()
                .trim()
                .toUpperCase()
                .replace(/[^A-Z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '')
                .slice(0, 20);
            return base ? `EXP-${base}` : '';
        };

        let codeTouched = false;
        if (codeInput) {
            codeInput.addEventListener('input', () => {
                codeTouched = true;
            });
        }
        if (nameInput && codeInput) {
            nameInput.addEventListener('input', () => {
                if (codeTouched && codeInput.value.trim() !== '') {
                    return;
                }
                codeInput.value = toCode(nameInput.value);
            });
        }

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            errors.classList.add('d-none');
            errors.textContent = '';

            const formData = new FormData(form);
            const response = await fetch("{{ route('expenses.categories.store') }}", {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    'Accept': 'application/json'
                },
                body: formData
            });

            if (!response.ok) {
                const data = await response.json().catch(() => null);
                if (data && data.errors) {
                    const messages = Object.values(data.errors).flat().join('<br>');
                    errors.innerHTML = messages;
                } else {
                    errors.textContent = 'Failed to create category. Please try again.';
                }
                errors.classList.remove('d-none');
                return;
            }

            const data = await response.json();
            const selects = document.querySelectorAll('[data-expense-category-select]');
            selects.forEach((select) => {
                const option = new Option(data.name, data.id, true, true);
                select.add(option);
                if (window.jQuery && window.jQuery(select).hasClass('select2')) {
                    window.jQuery(select).trigger('change');
                } else {
                    select.dispatchEvent(new Event('change'));
                }
            });

            form.reset();
            codeTouched = false;
            const activeCheckbox = document.getElementById('category_active');
            if (activeCheckbox) activeCheckbox.checked = true;

            if (window.jQuery) {
                window.jQuery(modalEl).modal('hide');
            }
        });
    })();
</script>
@endpush
