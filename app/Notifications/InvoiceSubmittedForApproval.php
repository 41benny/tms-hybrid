<?php

namespace App\Notifications;

use App\Models\Finance\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class InvoiceSubmittedForApproval extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Invoice $invoice
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        // Ensure relationships are loaded
        if (! $this->invoice->relationLoaded('customer')) {
            $this->invoice->load('customer');
        }
        if (! $this->invoice->relationLoaded('createdBy')) {
            $this->invoice->load('createdBy');
        }

        return [
            'invoice_id' => $this->invoice->id,
            'invoice_number' => $this->invoice->invoice_number,
            'customer_name' => $this->invoice->customer?->name ?? 'Unknown',
            'total_amount' => (float) $this->invoice->total_amount,
            'submitted_by_name' => $this->invoice->createdBy?->name ?? 'Unknown',
            'invoice_date' => $this->invoice->invoice_date->format('Y-m-d'),
            'approval_status' => $this->invoice->approval_status,
            'url' => route('invoices.show', $this->invoice->id),
        ];
    }
}
