<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Get all notifications for the authenticated user.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['notifications' => [], 'unread_count' => 0]);
        }

        $notifications = $user->notifications()
            ->latest()
            ->limit(20)
            ->get()
            ->filter(function ($notification) {
                // Validate that the referenced data still exists
                return $this->isNotificationValid($notification);
            });

        return response()->json([
            'notifications' => $notifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'data' => $notification->data,
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at->diffForHumans(),
                    'message' => $this->getNotificationMessage($notification),
                ];
            })->values(),
            'unread_count' => $user->unreadNotifications()
                ->get()
                ->filter(fn($n) => $this->isNotificationValid($n))
                ->count(),
        ]);
    }
    
    /**
     * Check if the notification's referenced data still exists.
     */
    private function isNotificationValid($notification): bool
    {
        $data = $notification->data;
        
        // Check based on notification type
        return match ($notification->type) {
            'App\\Notifications\\InvoiceSubmittedForApproval' => 
                \App\Models\Finance\Invoice::where('invoice_number', $data['invoice_number'] ?? '')->exists(),
            
            'App\\Notifications\\TaxInvoiceRequestedNotification' => 
                \App\Models\Accounting\TaxInvoiceRequest::where('request_number', $data['request_number'] ?? '')->exists(),
            
            'App\\Notifications\\PaymentRequestCreated' => 
                \App\Models\Operations\PaymentRequest::where('request_number', $data['request_number'] ?? '')->exists(),
            
            default => true, // Keep unknown notification types
        };
    }

    /**
     * Get a human-readable message for the notification.
     */
    private function getNotificationMessage($notification): string
    {
        $data = $notification->data;
        
        return match ($notification->type) {
            'App\Notifications\InvoiceSubmittedForApproval' => "Invoice #{$data['invoice_number']} for {$data['customer_name']} submitted for approval by {$data['submitted_by_name']}.",
            'App\Notifications\TaxInvoiceRequestedNotification' => "Tax Invoice Request #{$data['request_number']} from {$data['requester']} ({$data['count']} invoices).",
            'App\Notifications\PaymentRequestCreated' => "Payment Request #{$data['request_number']} for Rp " . number_format($data['amount'], 0, ',', '.') . " requested by {$data['requested_by_name']}.",
            default => $data['message'] ?? 'New Notification',
        };
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(string $id): JsonResponse
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['success' => false], 401);
        }

        $notification = $user->notifications()->find($id);

        if ($notification) {
            $notification->markAsRead();
        }

        return response()->json([
            'success' => true,
            'unread_count' => $user->unreadNotifications()->count(),
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(): JsonResponse
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['success' => false], 401);
        }

        $user->unreadNotifications()->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'unread_count' => 0,
        ]);
    }

    /**
     * Get unread notification count.
     */
    public function count(): JsonResponse
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['count' => 0]);
        }

        return response()->json([
            'count' => $user->unreadNotifications()->count(),
        ]);
    }
}
