// ========================================
// INVOICE CREATE PAGE JAVASCRIPT
// ========================================

// Global functions for Invoice Calculation
window.formatRupiah = function (num) {
    return 'Rp ' + num.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
};

window.recalcTotals = function () {
    console.log('💰 recalcTotals triggered');
    const subtotalHidden = document.getElementById('invoice_subtotal_value');
    const taxInput = document.getElementById('tax_amount_input');
    const discountInput = document.getElementById('discount_amount_input');
    const pph23Input = document.getElementById('pph23_amount_input');

    const displaySubtotal = document.getElementById('display_subtotal');
    const displayTax = document.getElementById('display_tax');
    const displayDiscount = document.getElementById('display_discount');
    const displayTotal = document.getElementById('display_total');
    const displayPph23 = document.getElementById('display_pph23');
    const displayNetPayable = document.getElementById('display_net_payable');

    const subtotal = parseFloat(subtotalHidden?.value || '0') || 0;
    // Parse Indonesian format: remove dots (thousand sep), replace comma with dot (decimal)
    let tax = parseFloat((taxInput?.value || '0').replace(/\./g, '').replace(',', '.')) || 0;
    const discount = parseFloat((discountInput?.value || '0').replace(/\./g, '').replace(',', '.')) || 0;
    const pph23 = parseFloat((pph23Input?.value || '0').replace(/\./g, '').replace(',', '.')) || 0;

    console.log(`Values: Subtotal=${subtotal}, Tax=${tax}, Discount=${discount}, PPh23=${pph23}`);

    const total = subtotal + tax - discount;
    const netPayable = total - pph23;

    console.log(`Calculated: Total=${total}, NetPayable=${netPayable}`);

    if (displaySubtotal) displaySubtotal.textContent = window.formatRupiah(subtotal);
    if (displayTax) displayTax.textContent = window.formatRupiah(tax);
    if (displayDiscount) displayDiscount.textContent = '- ' + window.formatRupiah(discount);
    if (displayTotal) displayTotal.textContent = window.formatRupiah(total);
    if (displayPph23) displayPph23.textContent = '- ' + window.formatRupiah(pph23);
    if (displayNetPayable) displayNetPayable.textContent = window.formatRupiah(netPayable);
};

window.recalcPpn = function () {
    console.log('🔄 recalcPpn triggered');

    const taxInput = document.getElementById('tax_amount_input');
    const taxCodeSelect = document.getElementById('transaction_type_select') || document.querySelector('[name="transaction_type"]');
    const subtotalHidden = document.getElementById('invoice_subtotal_value');

    // Early return if essential elements don't exist
    if (!taxCodeSelect) {
        console.warn('Tax code select not found, skipping PPN calculation');
        return;
    }

    const code = taxCodeSelect.value;
    console.log('Tax Code Selected:', code);

    let rate = 0.11; // Default 11% (01, 02, 03, 04, 06, 09)

    if (code === '05') rate = 0.011;      // 1.1%
    else if (code === '07' || code === '08') rate = 0.0;   // 0%

    console.log('Tax Rate:', rate);

    if (rate !== null) {
        // Calculate taxable and non-taxable subtotals
        let taxableSubtotal = 0;
        let nonTaxableSubtotal = 0;
        const itemContainers = document.querySelectorAll('#invoice-items-container > div');

        console.log('Found item containers:', itemContainers.length);

        itemContainers.forEach(function (itemElement, index) {
            // Skip separator elements and empty state
            if (itemElement.querySelector('.border-dashed') || itemElement.querySelector('.text-center')) return;

            const qtyInput = itemElement.querySelector('input[name*="[quantity]"]');
            const priceInput = itemElement.querySelector('input[name*="[unit_price]"]');
            const excludeTaxCheckbox = itemElement.querySelector('input[name*="[exclude_tax]"]');

            if (qtyInput && priceInput) {
                const qty = parseFloat(qtyInput.value) || 0;
                const price = parseFloat(priceInput.value) || 0;
                const itemTotal = qty * price;
                const isExcluded = excludeTaxCheckbox && excludeTaxCheckbox.checked;

                if (isExcluded) {
                    nonTaxableSubtotal += itemTotal;
                } else {
                    taxableSubtotal += itemTotal;
                }
            }
        });

        console.log('Taxable Subtotal:', taxableSubtotal);
        console.log('Non-Taxable Subtotal:', nonTaxableSubtotal);

        // DPP Nilai Lain Logic
        const dppNilaiLainCheckbox = document.getElementById('use_dpp_nilai_lain');
        const dppNilaiLainRow = document.getElementById('dpp_nilai_lain_row');
        const displayDppNilaiLain = document.getElementById('display_dpp_nilai_lain');

        let taxBase = taxableSubtotal;
        if (dppNilaiLainCheckbox && dppNilaiLainCheckbox.checked) {
            // Formula: Total Main Item * 11/12
            const dppNilaiLain = taxableSubtotal * (11 / 12);
            taxBase = dppNilaiLain;

            // Override rate to 12% as per regulation
            rate = 0.12;

            if (dppNilaiLainRow) dppNilaiLainRow.classList.remove('hidden');
            if (displayDppNilaiLain) displayDppNilaiLain.textContent = window.formatRupiah(dppNilaiLain);
        } else {
            if (dppNilaiLainRow) dppNilaiLainRow.classList.add('hidden');
        }

        const tax = taxBase * rate;

        // Only update tax input if it exists
        if (taxInput) {
            taxInput.value = tax.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
        console.log('Calculated Tax:', tax);

        // Update all subtotal displays
        const allSubtotal = taxableSubtotal + nonTaxableSubtotal;
        if (subtotalHidden) {
            subtotalHidden.value = allSubtotal.toFixed(2);
        }

        // Update breakdown displays
        const displayTaxableSubtotal = document.getElementById('display_taxable_subtotal');
        const displayNonTaxableSubtotal = document.getElementById('display_nontaxable_subtotal');

        if (displayTaxableSubtotal) {
            displayTaxableSubtotal.textContent = window.formatRupiah(taxableSubtotal);
        }
        if (displayNonTaxableSubtotal) {
            displayNonTaxableSubtotal.textContent = window.formatRupiah(nonTaxableSubtotal);
        }
    }
    window.recalcTotals();
};

function addInvoiceItemRow() {
    const container = document.getElementById('invoice-items-container');
    if (!container) return;

    let nextIndex = parseInt(container.getAttribute('data-next-index') || '0', 10);
    if (isNaN(nextIndex) || nextIndex < 0) {
        nextIndex = 0;
    }

    const wrapper = document.createElement('div');
    wrapper.className = 'border border-slate-200 dark:border-slate-700 rounded-lg p-4 bg-slate-50 dark:bg-slate-800/30 relative group';

    wrapper.innerHTML =
        '<button type="button" onclick="removeInvoiceItemRow(this)" class="absolute top-2 right-2 p-1.5 text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 rounded opacity-0 group-hover:opacity-100 transition-opacity" title="Hapus item ini">' +
        '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>' +
        '</svg>' +
        '</button>' +
        '<input type="hidden" name="items[' + nextIndex + '][job_order_id]" value="">' +
        '<input type="hidden" name="items[' + nextIndex + '][shipment_leg_id]" value="">' +
        '<input type="hidden" name="items[' + nextIndex + '][item_type]" value="other">' +
        '<div class="grid grid-cols-1 md:grid-cols-5 gap-3">' +
        '<div class="md:col-span-2">' +
        '<label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Deskripsi</label>' +
        '<input type="text" name="items[' + nextIndex + '][description]" class="w-full rounded bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-2 py-2 text-sm" required>' +
        '</div>' +
        '<div>' +
        '<label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Qty</label>' +
        '<input type="number" step="0.01" min="0.01" name="items[' + nextIndex + '][quantity]" class="w-full rounded bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-2 py-2 text-sm" required>' +
        '</div>' +
        '<div>' +
        '<label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Unit Price</label>' +
        '<input type="number" step="0.01" min="0" name="items[' + nextIndex + '][unit_price]" class="w-full rounded bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-2 py-2 text-sm" required>' +
        '</div>' +
        '<div>' +
        '<label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Subtotal</label>' +
        '<div class="w-full rounded bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 px-2 py-2 text-sm font-semibold text-slate-900 dark:text-slate-100 item-subtotal">Rp 0</div>' +
        '</div>' +
        '</div>' +
        '<div class="mt-3 flex items-center gap-2">' +
        '<input type="checkbox" name="items[' + nextIndex + '][exclude_tax]" id="exclude_tax_' + nextIndex + '" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">' +
        '<label for="exclude_tax_' + nextIndex + '" class="text-xs text-slate-600 dark:text-slate-400 cursor-pointer">' +
        '<span class="font-medium">Exclude dari PPN</span>' +
        '<span class="text-slate-500 dark:text-slate-500"> (Item ini tidak dikenakan pajak)</span>' +
        '</label>' +
        '</div>';

    // Find the separator (billable items separator) to insert before it
    const separator = Array.from(container.children).find(child => {
        return child.textContent.includes('Biaya Tambahan') || child.textContent.includes('BILLABLE');
    });

    if (separator) {
        // Insert before the separator (before billable items)
        container.insertBefore(wrapper, separator);
    } else {
        // No separator found, append at the end
        container.appendChild(wrapper);
    }

    container.setAttribute('data-next-index', String(nextIndex + 1));

    // Hook subtotal auto-update for new row
    const qtyInput = wrapper.querySelector('input[name*="[quantity]"]');
    const priceInput = wrapper.querySelector('input[name*="[unit_price]"]');
    const subtotalDisplay = wrapper.querySelector('.item-subtotal');
    const excludeTaxCheckbox = wrapper.querySelector('input[name*="[exclude_tax]"]');

    if (qtyInput && priceInput && subtotalDisplay) {
        function updateSubtotal() {
            const qty = parseFloat(qtyInput.value) || 0;
            const price = parseFloat(priceInput.value) || 0;
            const subtotal = qty * price;
            subtotalDisplay.textContent = 'Rp ' + subtotal.toLocaleString('id-ID', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            });

            // Recalculate PPN when qty or price changes
            if (typeof recalcPpn === 'function') {
                recalcPpn();
            }
        }

        qtyInput.addEventListener('input', updateSubtotal);
        priceInput.addEventListener('input', updateSubtotal);
    }

    // Event listener untuk exclude_tax checkbox
    if (excludeTaxCheckbox) {
        excludeTaxCheckbox.addEventListener('change', function () {
            if (typeof recalcPpn === 'function') {
                recalcPpn();
            }
        });
    }
}

function removeInvoiceItemRow(button) {
    const row = button.closest('.border.border-slate-200');
    if (row) {
        // Fade out animation
        row.style.transition = 'opacity 0.3s ease';
        row.style.opacity = '0';

        setTimeout(() => {
            row.remove();
            // Recalculate PPN after removing item
            if (typeof recalcPpn === 'function') {
                recalcPpn();
            }
        }, 300);
    }
}

function openJobOrderModal() {
    const customerId = document.getElementById('customer_id_input')?.value;

    if (!customerId) {
        alert('Silakan pilih customer terlebih dahulu');
        return;
    }

    const modal = document.getElementById('jobOrderModal');

    // If modal doesn't exist, load it dynamically
    if (!modal) {
        console.log('📦 Loading job order modal dynamically...');
        const modalContainer = document.getElementById('jobOrderModalContainer');

        if (!modalContainer) {
            console.error('Modal container not found');
            return;
        }

        // Show loading state
        modalContainer.innerHTML = '<div class="text-center p-4">Memuat...</div>';

        // Fetch modal content
        fetch(`${window.INVOICE_CREATE_ROUTE}?customer_id=${customerId}&load_modal=1`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => response.text())
            .then(html => {
                modalContainer.innerHTML = html;
                // Try opening modal again after loading
                setTimeout(() => openJobOrderModal(), 100);
            })
            .catch(error => {
                console.error('Error loading modal:', error);
                alert('Gagal memuat modal. Silakan refresh halaman.');
                modalContainer.innerHTML = '';
            });

        return;
    }

    const invDate = document.querySelector('input[name="invoice_date"]');
    const dueDate = document.querySelector('input[name="due_date"]');
    const terms = document.querySelector('input[name="payment_terms"]');
    const notes = document.querySelector('textarea[name="notes"]');
    const taxCode = document.getElementById('transaction_type_select');
    const ref = document.getElementById('reference_header');

    const modalInv = document.getElementById('modal_invoice_date');
    const modalDue = document.getElementById('modal_due_date');
    const modalTerms = document.getElementById('modal_payment_terms');
    const modalNotes = document.getElementById('modal_notes');
    const modalTax = document.getElementById('modal_tax_code');
    const modalRef = document.getElementById('modal_reference');

    if (modalInv && invDate) modalInv.value = invDate.value;
    if (modalDue && dueDate) modalDue.value = dueDate.value;
    if (modalTerms && terms) modalTerms.value = terms.value;
    if (modalNotes && notes) modalNotes.value = notes.value;
    if (modalTax && taxCode) modalTax.value = taxCode.value;
    if (modalRef && ref) modalRef.value = ref.value;

    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}


function closeJobOrderModal() {
    const modal = document.getElementById('jobOrderModal');
    if (!modal) return;
    modal.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

// ========================================
// JOB ORDER MODAL FUNCTIONS
// ========================================

window.toggleDpInput = function () {
    const isDp = document.getElementById('is_dp')?.checked;
    const container = document.getElementById('dp_input_container');
    if (!container) return;

    if (isDp) {
        container.classList.remove('hidden');
    } else {
        container.classList.add('hidden');
        const dpAmountInput = document.getElementById('dp_amount');
        if (dpAmountInput) dpAmountInput.value = '';
    }
};

window.updateJobOrderList = function () {
    const statusFilter = document.getElementById('status_filter');
    const customerId = document.getElementById('modal_customer_id');
    const container = document.getElementById('job-order-list-container');

    if (!statusFilter || !customerId || !container) {
        console.error('Required elements not found for updateJobOrderList');
        return;
    }

    const status = statusFilter.value;
    const customerIdValue = customerId.value;

    // Show loading state
    container.innerHTML = '<div class="p-4 text-center text-sm text-slate-500">Memuat...</div>';

    // Fetch updated list via AJAX
    fetch(`${window.INVOICE_CREATE_ROUTE}?load_job_orders=1&customer_id=${customerIdValue}&status_filter=${status}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => response.text())
        .then(html => {
            container.innerHTML = html;
        })
        .catch(error => {
            console.error('Error:', error);
            container.innerHTML = '<div class="p-4 text-center text-sm text-red-500">Gagal memuat data.</div>';
        });
};

window.addSelectedJobOrders = function () {
    console.log('🎯 addSelectedJobOrders called');

    const checkboxes = document.querySelectorAll('#job-order-list-container input[type="checkbox"]:checked');
    const selectedIds = Array.from(checkboxes).map(cb => cb.value);

    console.log('Selected IDs:', selectedIds);

    if (selectedIds.length === 0) {
        alert('Pilih minimal satu Job Order');
        return;
    }

    // Skip job order yang sudah ada di invoice agar tidak double
    const existingJobOrderIds = new Set(
        Array.from(document.querySelectorAll('#invoice-items-container input[name*="[job_order_id]"]'))
            .map(input => input.value)
            .filter(Boolean)
    );
    const newJobOrderIds = selectedIds.filter(id => !existingJobOrderIds.has(id));

    if (newJobOrderIds.length === 0) {
        alert('Job Order yang dipilih sudah ada di invoice');
        return;
    }

    if (newJobOrderIds.length !== selectedIds.length) {
        alert('Sebagian Job Order sudah ada, hanya menambahkan yang belum ada.');
    }

    const customerId = document.getElementById('modal_customer_id')?.value;
    const isDp = document.getElementById('is_dp')?.checked || false;
    const dpAmount = document.getElementById('dp_amount')?.value || '';

    console.log('Customer ID:', customerId, 'Is DP:', isDp, 'DP Amount:', dpAmount);

    // Show loading state
    const container = document.getElementById('invoice-items-container');
    if (!container) {
        console.error('Invoice items container not found');
        alert('Error: Container tidak ditemukan');
        return;
    }

    // Fetch job order details and add to invoice
    const params = new URLSearchParams({
        customer_id: customerId,
        job_order_ids: newJobOrderIds.join(','),
        is_dp: isDp ? '1' : '0',
        dp_amount: dpAmount || '',
        fetch_items: '1'
    });

    console.log('Fetching items with params:', params.toString());

    fetch(`${window.INVOICE_CREATE_ROUTE}?${params}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);

            if (data.items && data.items.length > 0) {
                // Add items to the invoice
                data.items.forEach(item => {
                    addJobOrderItemToInvoice(item);
                });

                // Recalculate totals
                if (typeof window.recalcPpn === 'function') {
                    window.recalcPpn();
                }

                // Close modal
                closeJobOrderModal();

                // Show success message
                console.log(`✅ Added ${data.items.length} job order(s) to invoice`);
            } else {
                alert('Tidak ada item yang bisa ditambahkan');
            }
        })
        .catch(error => {
            console.error('Error adding job orders:', error);
            console.error('Error details:', error.message);
            alert(`Gagal menambahkan Job Order: ${error.message}`);
        });
};

function addJobOrderItemToInvoice(item) {
    console.log('Adding item to invoice:', item);

    const container = document.getElementById('invoice-items-container');
    if (!container) {
        console.error('Container not found');
        return;
    }

    // Remove empty state if present
    const emptyState = container.querySelector('.text-center.py-12');
    if (emptyState) {
        emptyState.remove();
    }

    // Show summary section
    const summarySection = document.getElementById('invoice-summary-section');
    if (summarySection) {
        summarySection.classList.remove('hidden');
    }

    let nextIndex = parseInt(container.getAttribute('data-next-index') || '0', 10);
    if (isNaN(nextIndex) || nextIndex < 0) {
        nextIndex = 0;
    }

    const wrapper = document.createElement('div');
    wrapper.className = 'border border-slate-200 dark:border-slate-700 rounded-lg p-4 bg-slate-50 dark:bg-slate-800/30 relative group';

    const amount = (parseFloat(item.quantity) || 0) * (parseFloat(item.unit_price) || 0);
    const itemType = item.item_type || 'job_order';

    wrapper.innerHTML =
        '<button type="button" onclick="removeInvoiceItemRow(this)" class="absolute top-2 right-2 p-1.5 text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 rounded opacity-0 group-hover:opacity-100 transition-opacity" title="Hapus item ini">' +
        '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>' +
        '</svg>' +
        '</button>' +
        '<input type="hidden" name="items[' + nextIndex + '][job_order_id]" value="' + (item.job_order_id || '') + '">' +
        '<input type="hidden" name="items[' + nextIndex + '][shipment_leg_id]" value="' + (item.shipment_leg_id || '') + '">' +
        '<input type="hidden" name="items[' + nextIndex + '][item_type]" value="' + itemType + '">' +
        '<div class="grid grid-cols-1 md:grid-cols-5 gap-3">' +
        '<div class="md:col-span-2">' +
        '<label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Deskripsi</label>' +
        '<input type="text" name="items[' + nextIndex + '][description]" value="' + (item.description || '').replace(/"/g, '&quot;') + '" class="w-full rounded bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-2 py-2 text-sm" required>' +
        '</div>' +
        '<div>' +
        '<label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Qty</label>' +
        '<input type="number" step="0.01" min="0.01" name="items[' + nextIndex + '][quantity]" value="' + (item.quantity || 1) + '" class="w-full rounded bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-2 py-2 text-sm" required>' +
        '</div>' +
        '<div>' +
        '<label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Unit Price</label>' +
        '<input type="number" step="0.01" min="0" name="items[' + nextIndex + '][unit_price]" value="' + (item.unit_price || 0) + '" class="w-full rounded bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-2 py-2 text-sm" required>' +
        '</div>' +
        '<div>' +
        '<label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Subtotal</label>' +
        '<div class="w-full rounded bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 px-2 py-2 text-sm font-semibold text-slate-900 dark:text-slate-100 item-subtotal">Rp ' + amount.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 }) + '</div>' +
        '</div>' +
        '</div>' +
        '<div class="mt-3 flex items-center gap-2">' +
        '<input type="checkbox" name="items[' + nextIndex + '][exclude_tax]" id="exclude_tax_' + nextIndex + '" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">' +
        '<label for="exclude_tax_' + nextIndex + '" class="text-xs text-slate-600 dark:text-slate-400 cursor-pointer">' +
        '<span class="font-medium">Exclude dari PPN</span>' +
        '<span class="text-slate-500 dark:text-slate-500"> (Item ini tidak dikenakan pajak)</span>' +
        '</label>' +
        '</div>';

    const isBillableType = ['insurance_billable', 'additional_cost_billable'].includes(itemType);
    let billableSeparator = Array.from(container.children).find(child =>
        child.dataset?.billableSeparator === '1' ||
        child.textContent.includes('Biaya Tambahan') ||
        child.textContent.includes('BILLABLE')
    );

    if (isBillableType) {
        if (!billableSeparator) {
            billableSeparator = document.createElement('div');
            billableSeparator.dataset.billableSeparator = '1';
            billableSeparator.className = 'flex items-center gap-3 py-3';
            billableSeparator.innerHTML =
                '<div class="flex-1 border-t-2 border-dashed border-amber-300 dark:border-amber-700"></div>' +
                '<span class="text-xs font-semibold text-amber-600 dark:text-amber-400 uppercase tracking-wider px-3 py-1 bg-amber-50 dark:bg-amber-900/20 rounded-full">Biaya Tambahan (Billable)</span>' +
                '<div class="flex-1 border-t-2 border-dashed border-amber-300 dark:border-amber-700"></div>';
            container.appendChild(billableSeparator);
        }

        if (billableSeparator.nextSibling) {
            container.insertBefore(wrapper, billableSeparator.nextSibling);
        } else {
            container.appendChild(wrapper);
        }
    } else {
        if (billableSeparator) {
            container.insertBefore(wrapper, billableSeparator);
        } else {
            container.appendChild(wrapper);
        }
    }

    container.setAttribute('data-next-index', String(nextIndex + 1));

    // Hook subtotal auto-update for new row
    const qtyInput = wrapper.querySelector('input[name*="[quantity]"]');
    const priceInput = wrapper.querySelector('input[name*="[unit_price]"]');
    const subtotalDisplay = wrapper.querySelector('.item-subtotal');
    const excludeTaxCheckbox = wrapper.querySelector('input[name*="[exclude_tax]"]');

    if (excludeTaxCheckbox && item.exclude_tax) {
        excludeTaxCheckbox.checked = true;
    }

    if (qtyInput && priceInput && subtotalDisplay) {
        function updateSubtotal() {
            const qty = parseFloat(qtyInput.value) || 0;
            const price = parseFloat(priceInput.value) || 0;
            const subtotal = qty * price;
            subtotalDisplay.textContent = 'Rp ' + subtotal.toLocaleString('id-ID', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            });

            if (typeof window.recalcPpn === 'function') {
                window.recalcPpn();
            }
        }

        qtyInput.addEventListener('input', updateSubtotal);
        priceInput.addEventListener('input', updateSubtotal);
    }

    if (excludeTaxCheckbox) {
        excludeTaxCheckbox.addEventListener('change', function () {
            if (typeof window.recalcPpn === 'function') {
                window.recalcPpn();
            }
        });
    }
}

// Print preview function (global scope for onclick access)
window.printPreview = function () {
    const printContent = document.getElementById('preview_content').innerHTML;
    const printWindow = window.open('', '', 'height=600,width=800');
    printWindow.document.write('<html><head><title>Print Invoice</title>');
    printWindow.document.write('<style>body{font-family:system-ui,-apple-system,sans-serif;padding:20px;} @media print{body{padding:0;}}</style>');
    printWindow.document.write('</head><body>');
    printWindow.document.write(printContent);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.print();
};

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function () {
    // ========================================
    // AUTO-SAVE FORM DATA TO LOCALSTORAGE
    // ========================================
    const STORAGE_KEY = 'invoice_create_form_data';
    const invoiceForm = document.getElementById('invoiceForm');

    // Fields to auto-save
    const fieldsToSave = [
        'invoice_date',
        'due_date',
        'payment_terms',
        'notes',
        'tax_amount',
        'discount_amount',
        'pph23_amount'
    ];

    // Restore saved data on page load
    function restoreFormData() {
        try {
            const savedData = localStorage.getItem(STORAGE_KEY);
            if (!savedData) return;

            const data = JSON.parse(savedData);
            let taxCodeRestored = false;

            // Restore each field
            fieldsToSave.forEach(fieldName => {
                if (data[fieldName] !== undefined) {
                    const field = document.querySelector(`[name="${fieldName}"]`);
                    if (field && !field.value) { // Only restore if field is empty
                        field.value = data[fieldName];
                    }
                }
            });

            // Restore tax code select
            if (data.tax_code_select) {
                const taxCodeSelect = document.getElementById('transaction_type_select');
                if (taxCodeSelect && !taxCodeSelect.value) {
                    taxCodeSelect.value = data.tax_code_select;
                    taxCodeRestored = true;
                }
            }

            console.log('✅ Form data restored from localStorage');

            // Trigger PPN recalculation after restore if tax code was restored
            if (taxCodeRestored) {
                setTimeout(function () {
                    if (typeof window.recalcPpn === 'function') {
                        window.recalcPpn();
                        console.log('🔄 PPN recalculated after restore');
                    }
                }, 100);
            }
        } catch (e) {
            console.error('Error restoring form data:', e);
        }
    }

    // Save form data to localStorage
    function saveFormData() {
        try {
            const data = {};

            // Save each field
            fieldsToSave.forEach(fieldName => {
                const field = document.querySelector(`[name="${fieldName}"]`);
                if (field) {
                    data[fieldName] = field.value;
                }
            });

            // Save tax code select
            const taxCodeSelect = document.getElementById('transaction_type_select');
            if (taxCodeSelect) {
                data.tax_code_select = taxCodeSelect.value;
            }

            localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
        } catch (e) {
            console.error('Error saving form data:', e);
        }
    }

    // Clear saved data (called on successful submit)
    function clearSavedFormData() {
        localStorage.removeItem(STORAGE_KEY);
        console.log('🗑️ Saved form data cleared');
    }

    // Restore data on page load
    restoreFormData();

    // Auto-save on input change (debounced)
    let saveTimeout;
    function debouncedSave() {
        clearTimeout(saveTimeout);
        saveTimeout = setTimeout(saveFormData, 500); // Save after 500ms of inactivity
    }

    // Attach listeners to all fields
    fieldsToSave.forEach(fieldName => {
        const field = document.querySelector(`[name="${fieldName}"]`);
        if (field) {
            field.addEventListener('input', debouncedSave);
            field.addEventListener('change', debouncedSave);
        }
    });

    // Tax code select
    const taxCodeSelect = document.getElementById('transaction_type_select');
    if (taxCodeSelect) {
        taxCodeSelect.addEventListener('change', debouncedSave);
    }

    // Clear saved data on successful form submission
    if (invoiceForm) {
        invoiceForm.addEventListener('submit', function () {
            // Convert formatted values back to raw numbers for backend
            const taxInput = document.getElementById('tax_amount_input');
            const discountInput = document.getElementById('discount_amount_input');
            const pph23Input = document.getElementById('pph23_amount_input');

            // Helper function to convert Indonesian format to raw number
            const toRawNumber = (value) => {
                if (!value) return '0';
                return value.replace(/\./g, '').replace(',', '.');
            };

            if (taxInput) taxInput.value = toRawNumber(taxInput.value);
            if (discountInput) discountInput.value = toRawNumber(discountInput.value);
            if (pph23Input) pph23Input.value = toRawNumber(pph23Input.value);

            // Clear after a short delay to ensure form is submitted
            setTimeout(clearSavedFormData, 100);
        });
    }

    // ========================================
    // SCROLL POSITION PERSISTENCE
    // ========================================
    const scrollKey = 'invoice_create_scroll';
    const savedScroll = sessionStorage.getItem(scrollKey);
    if (savedScroll !== null) {
        window.scrollTo(0, parseInt(savedScroll, 10));
        sessionStorage.removeItem(scrollKey);
    }

    function persistScrollPosition() {
        sessionStorage.setItem(scrollKey, String(window.scrollY || window.pageYOffset || 0));
    }

    document.querySelectorAll('form').forEach(function (formEl) {
        formEl.addEventListener('submit', persistScrollPosition);
    });

    window.submitInvoiceFormWithScroll = function (formEl) {
        if (!formEl) return;
        persistScrollPosition();
        formEl.submit();
    };

    // Customer typeahead di header
    const customerList = window.CUSTOMER_LOOKUP || [];
    const searchInput = document.getElementById('customer_search');
    const hiddenId = document.getElementById('customer_id_input');
    const suggestionsBox = document.getElementById('customer_suggestions');

    function clearCustomerSuggestions() {
        if (!suggestionsBox) return;
        suggestionsBox.innerHTML = '';
        suggestionsBox.classList.add('hidden');
    }

    function renderCustomerSuggestions(items) {
        if (!suggestionsBox) return;
        if (!items.length) {
            clearCustomerSuggestions();
            return;
        }
        suggestionsBox.innerHTML = items.map(function (c) {
            return '<button type="button" data-id=\"' + c.id + '\" class=\"w-full text-left px-3 py-2 text-sm hover:bg-slate-100 dark:hover:bg-slate-800\">' +
                c.name.replace(/</g, '&lt;') +
                '</button>';
        }).join('');
        suggestionsBox.classList.remove('hidden');


        Array.prototype.forEach.call(suggestionsBox.querySelectorAll('button[data-id]'), function (btn) {
            btn.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const found = customerList.find(function (c) { return String(c.id) === String(id); });
                if (!found) return;

                if (hiddenId) hiddenId.value = found.id;
                if (searchInput) searchInput.value = found.name;

                const addrField = document.querySelector('textarea[name="customer_address"]');
                const phoneField = document.querySelector('input[name="customer_phone"]');
                const npwpField = document.querySelector('input[name="customer_npwp"]');
                if (addrField) addrField.value = found.address || '';
                if (phoneField) phoneField.value = found.phone || '';
                if (npwpField) npwpField.value = found.npwp || '';

                clearCustomerSuggestions();

                // Enable the "Pilih Job Order" button
                const btnPilihJobOrder = document.getElementById('btn_pilih_job_order');
                if (btnPilihJobOrder) {
                    btnPilihJobOrder.disabled = false;
                    btnPilihJobOrder.classList.remove('bg-slate-200', 'text-slate-500', 'cursor-not-allowed');
                    btnPilihJobOrder.classList.add('text-white', 'bg-indigo-600', 'hover:bg-indigo-700', 'focus:outline-none', 'focus:ring-2', 'focus:ring-offset-2', 'focus:ring-indigo-500');
                }

                // Don't auto-submit form - let user add items first
                console.log('✅ Customer selected:', found.name);
            });
        });

    }

    if (searchInput && suggestionsBox) {
        console.log('✅ Customer search initialized');

        // Update search input handling
        searchInput.addEventListener('input', function () {
            const q = (this.value || '').trim().toLowerCase();
            console.log('🔍 Search query:', q);

            // If input is empty, hide suggestions
            if (q.length === 0) {
                clearCustomerSuggestions();
                return;
            }
            // If less than 2 characters, also hide suggestions
            if (q.length < 2) {
                clearCustomerSuggestions();
                return;
            }
            const results = customerList.filter(function (c) {
                return (c.name || '').toLowerCase().includes(q);
            }).slice(0, 10);

            console.log('📋 Results found:', results.length);

            if (results.length === 0) {
                // Show no results message
                suggestionsBox.innerHTML = '<div class="p-2 text-sm text-slate-500 dark:text-slate-400">Tidak ada hasil</div>';
                suggestionsBox.classList.remove('hidden');
            } else {
                renderCustomerSuggestions(results);
            }
        });


        document.addEventListener('click', function (e) {
            if (!suggestionsBox.contains(e.target) && e.target !== searchInput) {
                clearCustomerSuggestions();
            }
        });
    }

    // Get all item containers
    const itemContainers = document.querySelectorAll('.border.border-slate-200.rounded-lg.p-4.bg-slate-50');

    itemContainers.forEach(function (itemElement) {
        const qtyInput = itemElement.querySelector('input[name*="[quantity]"]');
        const priceInput = itemElement.querySelector('input[name*="[unit_price]"]');
        const subtotalDisplay = itemElement.querySelector('.item-subtotal');
        const excludeTaxCheckbox = itemElement.querySelector('input[name*="[exclude_tax]"]');

        if (qtyInput && priceInput && subtotalDisplay) {
            // Function untuk update subtotal
            function updateSubtotal() {
                const qty = parseFloat(qtyInput.value) || 0;
                const price = parseFloat(priceInput.value) || 0;
                const subtotal = qty * price;

                // Format dengan pemisah ribuan
                subtotalDisplay.textContent = 'Rp ' + subtotal.toLocaleString('id-ID', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0
                });

                // Recalculate PPN when qty or price changes
                if (typeof recalcPpn === 'function') {
                    recalcPpn();
                }
            }

            // Event listener untuk quantity
            qtyInput.addEventListener('input', updateSubtotal);

            // Event listener untuk unit price
            priceInput.addEventListener('input', updateSubtotal);
        }

        // Event listener untuk exclude_tax checkbox
        if (excludeTaxCheckbox) {
            excludeTaxCheckbox.addEventListener('change', function () {
                if (typeof recalcPpn === 'function') {
                    recalcPpn();
                }
            });
        }
    });

    // Auto-update subtotal saat qty atau unit price berubah

    const elTaxCodeSelect = document.getElementById('transaction_type_select');
    const elTaxInput = document.getElementById('tax_amount_input');
    const elPph23Input = document.getElementById('pph23_amount_input');
    const elDiscountInput = document.getElementById('discount_amount_input');
    const elBtnCalcPph23 = document.getElementById('btn_calc_pph23');
    const elSubtotalHidden = document.getElementById('invoice_subtotal_value');
    const elDppNilaiLainCheckbox = document.getElementById('use_dpp_nilai_lain');

    if (elTaxCodeSelect) {
        elTaxCodeSelect.addEventListener('change', function () {
            window.recalcPpn();
        });
    }

    if (elDppNilaiLainCheckbox) {
        elDppNilaiLainCheckbox.addEventListener('change', function () {
            window.recalcPpn();
        });
    }

    if (elTaxInput) elTaxInput.addEventListener('input', window.recalcTotals);
    if (elPph23Input) elPph23Input.addEventListener('input', window.recalcTotals);
    if (elDiscountInput) elDiscountInput.addEventListener('input', window.recalcTotals);

    if (elBtnCalcPph23) {
        elBtnCalcPph23.addEventListener('click', function () {
            // Ensure PPN is up to date
            window.recalcPpn();

            // Calculate subtotal of main items only (item_type === 'job_order')
            let mainSubtotal = 0;
            const itemContainers = document.querySelectorAll('#invoice-items-container > div');
            itemContainers.forEach(function (container) {
                const typeInput = container.querySelector('input[name*="[item_type]"]');
                const qtyInput = container.querySelector('input[name*="[quantity]"]');
                const priceInput = container.querySelector('input[name*="[unit_price]"]');
                if (typeInput && typeInput.value === 'job_order' && qtyInput && priceInput) {
                    const qty = parseFloat(qtyInput.value) || 0;
                    const price = parseFloat(priceInput.value) || 0;
                    mainSubtotal += qty * price;
                }
            });

            const pph = mainSubtotal * 0.02;

            if (elPph23Input) {
                elPph23Input.value = pph.toFixed(2);
                // Trigger input event to save to localStorage
                elPph23Input.dispatchEvent(new Event('input'));
                window.recalcTotals();
            }
        });
    }


    // Initial calc
    window.recalcPpn();
    window.recalcTotals();

    // Sinkronkan Reff header -> form utama
    const headerRef = document.getElementById('reference_header');
    const hiddenRef = document.getElementById('reference_hidden');

    function syncHeaderToHidden() {
        if (headerRef && hiddenRef) hiddenRef.value = headerRef.value || '';
    }

    if (headerRef) {
        headerRef.addEventListener('input', syncHeaderToHidden);
    }

    // Initial sync
    syncHeaderToHidden();

    // Helper: update due date otomatis dari term (hari)
    const termInput = document.getElementById('payment_terms');
    const invoiceDateInput = document.querySelector('input[name="invoice_date"]');
    const dueDateInput = document.querySelector('input[name="due_date"]');

    function recalcDueDateFromTerm() {
        if (!termInput || !invoiceDateInput || !dueDateInput) return;
        const termDays = parseInt(termInput.value, 10);
        const invDateStr = invoiceDateInput.value;
        if (!termDays || !invDateStr) return;

        const base = new Date(invDateStr);
        if (isNaN(base.getTime())) return;

        base.setDate(base.getDate() + termDays);
        const yyyy = base.getFullYear();
        const mm = String(base.getMonth() + 1).padStart(2, '0');
        const dd = String(base.getDate()).padStart(2, '0');
        dueDateInput.value = `${yyyy}-${mm}-${dd}`;

        console.log('📅 Due date updated:', dueDateInput.value);
    }

    if (termInput) {
        // Listen to both input and change events for real-time updates
        termInput.addEventListener('input', recalcDueDateFromTerm);
        termInput.addEventListener('change', recalcDueDateFromTerm);
    }
    if (invoiceDateInput) {
        invoiceDateInput.addEventListener('change', recalcDueDateFromTerm);
    }

    // Initial calculation on page load
    setTimeout(recalcDueDateFromTerm, 100);
    // AJAX Job Order Filter
    const statusFilterSelect = document.querySelector('select[name="status_filter"]');
    const joContainer = document.getElementById('job-order-list-container');

    if (statusFilterSelect && joContainer) {
        statusFilterSelect.addEventListener('change', function () {
            const status = this.value;
            const urlParams = new URLSearchParams(window.location.search);
            const customerId = urlParams.get('customer_id');

            // Keep other params if needed, but mainly we need customer_id and status_filter
            if (!customerId) return;

            // Show loading state
            joContainer.style.opacity = '0.5';

            // Fetch updated list
            fetch(window.INVOICE_CREATE_ROUTE + `?customer_id=${customerId}&status_filter=${status}&load_job_orders=1`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => response.text())
                .then(html => {
                    joContainer.innerHTML = html;
                    joContainer.style.opacity = '1';
                })
                .catch(error => {
                    console.error('Error fetching job orders:', error);
                    joContainer.style.opacity = '1';
                });
        });
    }

    // ========== INVOICE PREVIEW FUNCTIONALITY ==========
    const btnPreview = document.getElementById('btn_preview_invoice');
    const previewModal = document.getElementById('preview_modal');
    const closePreviewBtn = document.getElementById('close_preview_modal');
    const previewContent = document.getElementById('preview_content');

    if (btnPreview) {
        btnPreview.addEventListener('click', function () {
            generatePreview();
            previewModal.classList.remove('hidden');
        });
    }

    if (closePreviewBtn) {
        closePreviewBtn.addEventListener('click', function () {
            previewModal.classList.add('hidden');
        });
    }

    // Close modal when clicking outside
    if (previewModal) {
        previewModal.addEventListener('click', function (e) {
            if (e.target === previewModal) {
                previewModal.classList.add('hidden');
            }
        });
    }

    function generatePreview() {
        // Get form data
        const customerName = document.getElementById('customer_search')?.value || '-';
        const customerAddress = document.querySelector('[name="customer_address"]')?.value || '';
        const invoiceNumber = document.querySelector('[name="invoice_number"]')?.value || 'DRAFT';
        const invoiceDate = document.querySelector('[name="invoice_date"]')?.value || '';
        const dueDate = document.querySelector('[name="due_date"]')?.value || '';
        const notes = document.querySelector('[name="notes"]')?.value || '';
        const reference = document.querySelector('[name="reference"]')?.value || '';

        // Get totals
        const subtotal = parseFloat(document.getElementById('invoice_subtotal_value')?.value || 0);
        const tax = parseFloat(document.querySelector('[name="tax_amount"]')?.value || 0);
        const discount = parseFloat(document.querySelector('[name="discount_amount"]')?.value || 0);
        const pph23 = parseFloat(document.querySelector('[name="pph23_amount"]')?.value || 0);
        const showPph23 = document.querySelector('[name="show_pph23"]')?.checked || false;
        const total = subtotal + tax - discount;
        const netPayable = total - pph23;

        // Get items
        const itemContainers = document.querySelectorAll('#invoice-items-container > div');
        let itemsHtml = '';
        let rowNum = 1;

        itemContainers.forEach(function (container) {
            const description = container.querySelector('[name*="[description]"]')?.value || '';
            const qty = parseFloat(container.querySelector('[name*="[quantity]"]')?.value || 0);
            const price = parseFloat(container.querySelector('[name*="[unit_price]"]')?.value || 0);
            const amount = qty * price;

            if (description) {
                itemsHtml += `
                    <tr>
                        <td style="padding: 8px; border-bottom: 1px solid #e2e8f0;">${rowNum++}</td>
                        <td style="padding: 8px; border-bottom: 1px solid #e2e8f0;">${description}</td>
                        <td style="padding: 8px; text-align: right; border-bottom: 1px solid #e2e8f0;">${qty.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                        <td style="padding: 8px; text-align: right; border-bottom: 1px solid #e2e8f0;">${price.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                        <td style="padding: 8px; text-align: right; border-bottom: 1px solid #e2e8f0;">${amount.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                    </tr>
                `;
            }
        });

        if (!itemsHtml) {
            itemsHtml = '<tr><td colspan="5" style="padding: 16px; text-align: center; color: #94a3b8;">Tidak ada item</td></tr>';
        }

        // Format dates
        const formatDate = (dateStr) => {
            if (!dateStr) return '-';
            const date = new Date(dateStr);
            return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
        };

        // Generate preview HTML
        const previewHtml = `
            <div style="font-size: 12px; color: #1f2937; position: relative; overflow: hidden;">
                <!-- Watermark -->
                <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-45deg); font-size: 80px; font-weight: 800; color: rgba(203, 213, 225, 0.3); pointer-events: none; white-space: nowrap; z-index: 0;">
                    DRAFT PREVIEW
                </div>
                <div style="position: relative; z-index: 1;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 24px;">
                    <div>
                        <h1 style="font-size: 24px; font-weight: 700; margin: 0 0 12px 0;">INVOICE</h1>
                        <div style="font-size: 11px; line-height: 1.6;">
                            <div><span style="font-weight: 600;">Nomor:</span> ${invoiceNumber}</div>
                            <div><span style="font-weight: 600;">Tanggal:</span> ${formatDate(invoiceDate)}</div>
                            <div><span style="font-weight: 600;">Jatuh Tempo:</span> ${formatDate(dueDate)}</div>
                            ${reference ? `<div><span style="font-weight: 600;">Referensi:</span> ${reference}</div>` : ''}
                        </div>
                    </div>
                    <div style="text-align: right; width: 40%;">
                        <div style="font-weight: 600; margin-bottom: 4px;">Kepada Yth:</div>
                        <div style="font-size: 14px; font-weight: 600;">${customerName}</div>
                        ${customerAddress ? `<div style="font-size: 11px; white-space: pre-line; margin-top: 4px; word-wrap: break-word;">${customerAddress}</div>` : ''}
                    </div>
                </div>

                <table style="width: 100%; border-collapse: collapse; margin: 24px 0; border: 1px solid #e2e8f0; border-radius: 8px;">
                    <thead>
                        <tr style="background: #f3f4f6;">
                            <th style="padding: 8px; text-align: left; font-weight: 600; width: 5%;">No</th>
                            <th style="padding: 8px; text-align: left; font-weight: 600; width: 45%;">Deskripsi</th>
                            <th style="padding: 8px; text-align: right; font-weight: 600; width: 15%;">Qty</th>
                            <th style="padding: 8px; text-align: right; font-weight: 600; width: 15%;">Harga</th>
                            <th style="padding: 8px; text-align: right; font-weight: 600; width: 20%;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${itemsHtml}
                    </tbody>
                </table>

                <div style="display: flex; justify-content: space-between; margin-top: 24px;">
                    <div style="width: 55%;">
                        ${notes ? `
                            <div style="font-weight: 600; margin-bottom: 4px;">Catatan:</div>
                            <div style="font-size: 11px; white-space: pre-line;">${notes}</div>
                        ` : ''}
                    </div>
                    <div style="width: 40%;">
                        <table style="width: 100%;">
                            <tr>
                                <td style="padding: 4px; font-size: 11px;">Subtotal</td>
                                <td style="padding: 4px; text-align: right; font-size: 11px;">${subtotal.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                            </tr>
                            ${document.getElementById('use_dpp_nilai_lain')?.checked ? `
                            <tr>
                                <td style="padding: 4px; font-size: 11px;">DPP Nilai Lain</td>
                                <td style="padding: 4px; text-align: right; font-size: 11px;">${(subtotal * 11 / 12).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                            </tr>
                            ` : ''}
                            <tr>
                                <td style="padding: 4px; font-size: 11px;">PPN</td>
                                <td style="padding: 4px; text-align: right; font-size: 11px;">${tax.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                            </tr>
                            ${discount > 0 ? `
                            <tr>
                                <td style="padding: 4px; font-size: 11px;">Diskon</td>
                                <td style="padding: 4px; text-align: right; font-size: 11px;">-${discount.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                            </tr>
                            ` : ''}
                            ${showPph23 ? `
                                <tr>
                                    <td style="padding: 4px; font-size: 11px; color: #d97706;">PPh 23 (Estimasi)</td>
                                    <td style="padding: 4px; text-align: right; font-size: 11px; color: #d97706;">-${pph23.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                                </tr>
                            ` : ''}
                            <tr style="border-top: 1px solid #e2e8f0;">
                                <td style="padding: 8px 4px 4px 4px; font-weight: 700;">Total Tagihan</td>
                                <td style="padding: 8px 4px 4px 4px; text-align: right; font-weight: 700;">${(showPph23 ? netPayable : total).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div style="margin-top: 48px; display: flex; justify-content: space-between;">
                    <div style="font-size: 10px; color: #64748b;">
                        Preview dibuat: ${new Date().toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })}
                    </div>
                    <div style="text-align: right; width: 40%;">
                        <div style="margin-bottom: 48px; font-size: 11px;">Hormat kami,</div>
                        <div style="margin-top: 48px; border-top: 1px solid #d0d7de; padding-top: 4px; font-size: 11px;">(________________________)</div>
                    </div>
                </div>
                </div>
            </div>
        `;

        previewContent.innerHTML = previewHtml;
    }
});
