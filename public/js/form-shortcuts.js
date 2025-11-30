/**
 * Global Form Keyboard Shortcuts
 * Alt+S hanya untuk tombol Save/Simpan
 * Rekomendasi: tandai tombol dengan data-shortcut="save"
 */

document.addEventListener('DOMContentLoaded', function () {
    // Detect if form exists on page
    const forms = document.querySelectorAll('form');

    if (forms.length === 0) return;

    // Helper: cek apakah tombol adalah tombol "Save"
    function isSaveButton(button) {
        if (!button) return false;

        // Prioritas: atribut eksplisit
        if (button.dataset && button.dataset.shortcut === 'save') {
            return true;
        }

        // Fallback: berdasarkan teks tombol
        const text = (button.textContent || '').toLowerCase();
        return text.includes('simpan') || text.includes('save');
    }

    // Add keyboard shortcut listener (Alt+S)
    document.addEventListener('keydown', function (e) {
        // Check for Alt+S
        if (e.altKey && e.key.toLowerCase() === 's') {
            e.preventDefault(); // Prevent any default action

            // Find the active form (the one user is currently interacting with)
            let targetForm = null;

            // Check if focus is inside a form
            const activeElement = document.activeElement;
            if (activeElement && activeElement.form) {
                targetForm = activeElement.form;
            } else {
                // If no focus in form, use the first form on page
                targetForm = forms[0];
            }

            if (!targetForm) return;

            // Cari tombol submit yang benar‑benar "Save"
            const submitButtons = targetForm.querySelectorAll('button[type="submit"]');
            const submitButton = Array.from(submitButtons).find(isSaveButton);

            if (submitButton) {
                // Check if button is disabled
                if (submitButton.disabled) {
                    console.log('Form submit prevented: button is disabled');
                    return;
                }

                // Trigger click on submit button (this will trigger validation)
                submitButton.click();

                // Visual feedback
                showSaveNotification();
            } else {
                // If no submit button, try to submit form directly
                if (targetForm.checkValidity()) {
                    targetForm.submit();
                    showSaveNotification();
                } else {
                    // Trigger HTML5 validation
                    targetForm.reportValidity();
                }
            }
        }
    });

    // Show visual feedback when Alt+S is pressed
    function showSaveNotification() {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = 'fixed bottom-4 right-4 bg-indigo-600 text-white px-4 py-2 rounded-lg shadow-lg flex items-center gap-2 z-50 animate-fade-in';
        notification.innerHTML = `
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <span class="text-sm font-medium">Saving...</span>
        `;

        document.body.appendChild(notification);

        // Remove after 2 seconds
        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transform = 'translateY(10px)';
            notification.style.transition = 'all 0.3s ease';
            setTimeout(() => notification.remove(), 300);
        }, 2000);
    }

});

// Add CSS for animation
const style = document.createElement('style');
style.textContent = `
    @keyframes fade-in {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    .animate-fade-in {
        animation: fade-in 0.3s ease;
    }
`;
document.head.appendChild(style);
