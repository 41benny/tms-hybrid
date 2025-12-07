@extends('layouts.app', ['title' => 'Notifications'])

@section('content')
<div class="max-w-4xl mx-auto">
    <x-card title="All Notifications">
        <div id="notifications-container" class="space-y-2">
            <!-- Loading state -->
            <div id="loading" class="text-center py-8">
                <svg class="animate-spin h-8 w-8 mx-auto text-indigo-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Loading notifications...</p>
            </div>

            <!-- Notifications will be loaded here -->
            <div id="notifications-list" class="hidden"></div>

            <!-- Empty state -->
            <div id="empty-state" class="hidden text-center py-8">
                <svg class="w-16 h-16 mx-auto text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
                <p class="mt-4 text-slate-600 dark:text-slate-400">No notifications yet</p>
            </div>
        </div>
    </x-card>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', async function() {
    const loading = document.getElementById('loading');
    const notificationsList = document.getElementById('notifications-list');
    const emptyState = document.getElementById('empty-state');

    try {
        const response = await fetch('{{ route('notifications.index') }}', {
            headers: {
                'Accept': 'application/json',
            }
        });
        const data = await response.json();

        loading.classList.add('hidden');

        if (data.notifications && data.notifications.length > 0) {
            notificationsList.classList.remove('hidden');
            
            data.notifications.forEach(notification => {
                const notifEl = createNotificationElement(notification);
                notificationsList.appendChild(notifEl);
            });
        } else {
            emptyState.classList.remove('hidden');
        }
    } catch (error) {
        console.error('Error loading notifications:', error);
        loading.innerHTML = '<p class="text-center text-red-600">Failed to load notifications</p>';
    }
});

function createNotificationElement(notification) {
    const div = document.createElement('div');
    div.className = `p-4 rounded-lg border transition-colors ${
        notification.read_at 
            ? 'bg-slate-50 dark:bg-slate-800/50 border-slate-200 dark:border-slate-700' 
            : 'bg-white dark:bg-slate-800 border-indigo-200 dark:border-indigo-800'
    }`;

    const url = getNotificationUrl(notification);
    const isClickable = url && url !== '#';

    div.innerHTML = `
        <div class="flex items-start gap-3 ${isClickable ? 'cursor-pointer' : ''}">
            <div class="flex-shrink-0 mt-1">
                <svg class="w-5 h-5 ${notification.read_at ? 'text-slate-400' : 'text-indigo-500'}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-slate-900 dark:text-white leading-relaxed">
                    ${notification.message}
                </p>
                <div class="flex items-center gap-2 mt-2">
                    <p class="text-xs text-slate-500 dark:text-slate-400">${notification.created_at}</p>
                    ${!notification.read_at ? '<span class="px-2 py-0.5 text-[10px] bg-indigo-500 text-white rounded-full font-medium">New</span>' : ''}
                </div>
            </div>
        </div>
    `;

    if (isClickable) {
        div.addEventListener('click', async function() {
            // Mark as read
            try {
                await fetch(`/notifications/${notification.id}/read`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                });
            } catch (error) {
                console.error('Error marking as read:', error);
            }

            // Navigate
            window.location.href = url;
        });
    }

    return div;
}

function getNotificationUrl(notification) {
    // If URL exists in data, use it
    if (notification.data?.url) {
        return notification.data.url;
    }
    
    // Fallback: generate URL based on notification type
    if (notification.type === 'App\\Notifications\\PaymentRequestCreated' && notification.data?.payment_request_id) {
        return `/payment-requests/${notification.data.payment_request_id}`;
    }
    if (notification.type === 'App\\Notifications\\InvoiceSubmittedForApproval' && notification.data?.invoice_id) {
        return `/invoices/${notification.data.invoice_id}`;
    }
    if (notification.type === 'App\\Notifications\\TaxInvoiceRequestedNotification') {
        return '/tax-invoices';
    }
    
    return '#'; // Default fallback
}
</script>
@endpush
@endsection
