document.addEventListener('DOMContentLoaded', function() {
    initializeCalculator();
    
    // Add click handler to menu item
    document.querySelectorAll('.decor-calculator-trigger, #decorCalculatorMenuItem').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            $('#decorCalculatorModal').modal('show');
        });
    });
});

function initializeCalculator() {
    // Load tiles categories with error handling
    fetch('/admin/decor-calculator/categories')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Categories loaded:', data);
            if (!data || data.length === 0) {
                console.warn('No categories found!');
                return;
            }
            
            let options = '<option value="">Select category</option>';
            data.forEach(category => {
                options += `<option value="${category.id}" data-height="${category.height}" data-width="${category.width}">${category.name}</option>`;
            });
            
            const categorySelect = document.getElementById('tilesCategory');
            if (categorySelect) {
                categorySelect.innerHTML = options;
            } else {
                console.error('Category select element not found!');
            }
        })
        .catch(error => {
            console.error('Error loading categories:', error);
        });
    
    // Handle category change
    document.getElementById('tilesCategory').addEventListener('change', function() {
        const categoryId = this.value;
        if (categoryId) {
            fetch(`/admin/decor-calculator/settings/${categoryId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data) {
                        document.getElementById('lightTimes').value = data.light_times || 4;
                        document.getElementById('decoTimes').value = data.deco_times || 1;
                        document.getElementById('deepTimes').value = data.deep_times || 1;
                    }
                })
                .catch(error => {
                    console.error('Error loading settings:', error);
                });
        }
    });
    
    // Handle exclude deep checkbox
    document.getElementById('excludeDeep').addEventListener('change', function() {
        if (this.checked) {
            document.getElementById('deepSection').style.display = 'none';
            document.getElementById('deepResultContainer').style.display = 'none';
        } else {
            document.getElementById('deepSection').style.display = 'table-row';
            document.getElementById('deepResultContainer').style.display = 'block';
        }
    });
    
    // Calculate button click
    document.getElementById('calculateBtn').addEventListener('click', function() {
        const categoryId = document.getElementById('tilesCategory').value;
        if (!categoryId) {
            alert('Please select a tiles category');
            return;
        }
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        const data = {
            category_id: categoryId,
            quantity: document.getElementById('quantity').value,
            height: document.getElementById('height').value,
            light_times: document.getElementById('lightTimes').value,
            light_qty: document.getElementById('lightQty').value,
            deco_times: document.getElementById('decoTimes').value,
            deco_qty: document.getElementById('decoQty').value,
            deep_times: document.getElementById('deepTimes').value,
            deep_qty: document.getElementById('deepQty').value,
            exclude_deep: document.getElementById('excludeDeep').checked
        };
        
        fetch('/admin/decor-calculator/calculate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify(data)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(response => {
            document.getElementById('lightResult').textContent = response.light_quantity.toFixed(2);
            document.getElementById('decoResult').textContent = response.deco_quantity.toFixed(2);
            document.getElementById('deepResult').textContent = response.deep_quantity.toFixed(2);
            document.getElementById('resultBox').style.display = 'block';
        })
        .catch(error => {
            alert('Error in calculation. Please check your inputs.');
            console.error('Calculation error:', error);
        });
    });
    
    // Apply to invoice button
    document.getElementById('applyToInvoiceBtn').addEventListener('click', function() {
        // Implement your apply to invoice functionality here
        alert('Apply to invoice functionality will be implemented here.');
    });
}
