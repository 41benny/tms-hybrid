import "./bootstrap";

// Theme toggle handler
const themeKey = "tms-theme";

// Debounce function for performance optimization
function debounce(func, wait) {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout);
      func(...args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
}

// Wait for DOM to be ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    initThemeToggle();
    initNotifications();
  });
} else {
  // DOM already loaded
  initThemeToggle();
  initNotifications();
}

function initThemeToggle() {
  const btn = document.getElementById("theme-toggle");
  if (!btn) {
    console.warn('Theme toggle button not found');
    return;
  }

  console.log('Theme toggle initialized');
  
  btn.addEventListener("click", () => {
    const rootEl = document.documentElement;
    const isDark = rootEl.classList.toggle("dark");
    localStorage.setItem(themeKey, isDark ? "dark" : "light");
    console.log('Theme toggled to:', isDark ? 'dark' : 'light');
  });
}

// Notifications handler
function initNotifications() {
  const notificationButton = document.getElementById("notification-button");
  const notificationBadge = document.getElementById("notification-badge");
  const notificationList = document.getElementById("notification-list");
  const markAllReadBtn = document.getElementById("mark-all-read");
  
  if (!notificationButton || !notificationBadge || !notificationList) {
    return;
  }

  // Fetch notification count
  function updateNotificationCount() {
    fetch('/notifications/count', {
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
      },
      credentials: 'same-origin',
    })
      .then(response => response.json())
      .then(data => {
        const count = data.count || 0;
        if (count > 0) {
          notificationBadge.textContent = count > 99 ? '99+' : count;
          notificationBadge.classList.remove('hidden');
        } else {
          notificationBadge.classList.add('hidden');
        }
      })
      .catch(error => {
        console.error('Error fetching notification count:', error);
      });
  }

  // Fetch notifications
  function fetchNotifications() {
    fetch('/notifications', {
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
      },
      credentials: 'same-origin',
    })
      .then(response => response.json())
      .then(data => {
        const notifications = data.notifications || [];
        
        if (notifications.length === 0) {
          notificationList.innerHTML = '<div class="p-4 text-center text-sm text-slate-500 dark:text-slate-400">Tidak ada notifikasi</div>';
          return;
        }

        notificationList.innerHTML = notifications.map(notif => {
          const isRead = notif.read_at !== null;
          const data = notif.data || {};
          const paymentRequestId = data.payment_request_id;
          const requestNumber = data.request_number || 'N/A';
          const amount = data.amount ? new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(data.amount) : 'N/A';
          const requestedByName = data.requested_by_name || 'Unknown';
          
          return `
            <div class="p-3 border-b border-slate-200 dark:border-[#2d2d2d] hover:bg-slate-50 dark:hover:bg-[#2d2d2d] ${!isRead ? 'bg-indigo-50 dark:bg-indigo-900/20' : ''}">
              <a href="/payment-requests/${paymentRequestId}" class="block" onclick="markNotificationAsRead('${notif.id}')">
                <div class="flex items-start justify-between">
                  <div class="flex-1">
                    <p class="text-sm font-medium text-slate-900 dark:text-slate-100">
                      Pengajuan Pembayaran Baru
                    </p>
                    <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">
                      ${requestNumber} - ${amount}
                    </p>
                    <p class="text-xs text-slate-500 dark:text-slate-500 mt-1">
                      Oleh: ${requestedByName}
                    </p>
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">
                      ${notif.created_at}
                    </p>
                  </div>
                  ${!isRead ? '<div class="w-2 h-2 bg-indigo-600 rounded-full mt-1"></div>' : ''}
                </div>
              </a>
            </div>
          `;
        }).join('');
      })
      .catch(error => {
        console.error('Error fetching notifications:', error);
        notificationList.innerHTML = '<div class="p-4 text-center text-sm text-red-500">Error memuat notifikasi</div>';
      });
  }

  // Mark notification as read
  window.markNotificationAsRead = function(notificationId) {
    fetch(`/notifications/${notificationId}/read`, {
      method: 'POST',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      },
      credentials: 'same-origin',
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          updateNotificationCount();
          fetchNotifications();
        }
      })
      .catch(error => {
        console.error('Error marking notification as read:', error);
      });
  };

  // Mark all as read
  if (markAllReadBtn) {
    markAllReadBtn.addEventListener('click', function(e) {
      e.preventDefault();
      fetch('/notifications/read-all', {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
        credentials: 'same-origin',
      })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            updateNotificationCount();
            fetchNotifications();
          }
        })
        .catch(error => {
          console.error('Error marking all notifications as read:', error);
        });
    });
  }

  // Load notifications when dropdown opens
  notificationButton.addEventListener('click', function() {
    fetchNotifications();
  });

  // Initial load
  updateNotificationCount();

  // Auto-refresh count every 60 seconds (optimized from 30s)
  setInterval(updateNotificationCount, 60000);
  
  // Use Page Visibility API to pause when tab is hidden
  document.addEventListener('visibilitychange', () => {
    if (!document.hidden) {
      updateNotificationCount();
    }
  });
}
