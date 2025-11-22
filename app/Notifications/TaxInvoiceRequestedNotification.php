<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaxInvoiceRequestedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $requests;

    /**
     * Create a new notification instance.
     */
    public function __construct(array $requests)
    {
        $this->requests = $requests;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $count = count($this->requests);
        $firstRequest = $this->requests[0];
        
        $totalDpp = collect($this->requests)->sum('dpp');
        $totalPpn = collect($this->requests)->sum('ppn');
        $totalAmount = collect($this->requests)->sum('total_amount');

        return (new MailMessage)
            ->subject('[TMS] New Tax Invoice Request - ' . $firstRequest->request_number)
            ->greeting('Dear Tax Department,')
            ->line('A new tax invoice request has been submitted.')
            ->line('')
            ->line('**Request Details:**')
            ->line('Request Number: ' . $firstRequest->request_number)
            ->line('Submitted by: ' . $firstRequest->requester->name)
            ->line('Date: ' . $firstRequest->requested_at->format('d M Y H:i'))
            ->line('Number of Invoices: ' . $count)
            ->line('')
            ->line('**Summary:**')
            ->line('Total DPP: Rp ' . number_format($totalDpp, 0, ',', '.'))
            ->line('Total PPN: Rp ' . number_format($totalPpn, 0, ',', '.'))
            ->line('Total Amount: Rp ' . number_format($totalAmount, 0, ',', '.'))
            ->line('')
            ->action('View Tax Invoice Requests', route('tax-invoices.index'))
            ->line('Please review and process the request as soon as possible.')
            ->line('')
            ->line('Thank you.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        $count = count($this->requests);
        $firstRequest = $this->requests[0];
        
        return [
            'type' => 'tax_invoice_requested',
            'request_number' => $firstRequest->request_number,
            'count' => $count,
            'requester' => $firstRequest->requester->name,
            'requested_at' => $firstRequest->requested_at,
            'url' => route('tax-invoices.index'),
        ];
    }
}
