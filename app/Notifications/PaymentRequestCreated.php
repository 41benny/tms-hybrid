<?php

namespace App\Notifications;

use App\Models\Operations\PaymentRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PaymentRequestCreated extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public PaymentRequest $paymentRequest
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
        // Ensure requestedBy relationship is loaded
        if (! $this->paymentRequest->relationLoaded('requestedBy')) {
            $this->paymentRequest->load('requestedBy');
        }

        return [
            'payment_request_id' => $this->paymentRequest->id,
            'request_number' => $this->paymentRequest->request_number,
            'amount' => (float) $this->paymentRequest->amount,
            'requested_by_name' => $this->paymentRequest->requestedBy?->name ?? 'Unknown',
            'request_date' => $this->paymentRequest->request_date->format('Y-m-d'),
            'status' => $this->paymentRequest->status,
            'url' => route('payment-requests.show', $this->paymentRequest->id),
        ];
    }
}
