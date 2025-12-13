// Number formatting helper for invoice tax amount
// This file patches the recalcPpn function to format tax display

(function () {
    // Wait for DOM to be ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    function init() {
        // Override the original recalcPpn to add formatting
        const originalRecalcPpn = window.recalcPpn;

        if (typeof originalRecalcPpn !== 'function') {
            console.warn('recalcPpn not found, formatting may not work');
            return;
        }

        window.recalcPpn = function () {
            // Call original function
            originalRecalcPpn();

            // Format the tax input with thousand separators
            const taxInput = document.getElementById('tax_amount_input');
            if (taxInput && taxInput.value) {
                const numValue = parseFloat(taxInput.value.replace(/\./g, '').replace(/,/g, '.')) || 0;
                taxInput.value = numValue.toLocaleString('id-ID', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }
        };

        // Trigger initial formatting if tax field has value
        const taxInput = document.getElementById('tax_amount_input');
        if (taxInput && taxInput.value && taxInput.value !== '0') {
            const numValue = parseFloat(taxInput.value) || 0;
            taxInput.value = numValue.toLocaleString('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }
    }
})();
