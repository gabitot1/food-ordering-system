<?php

namespace App\Mail;

use App\Models\Orders;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Orders $order;

    public function __construct(Orders $order)
    {
        $this->order = $order;
    }

    public function build()
    {
        $statusLabel = ucfirst(str_replace('_', ' ', $this->order->status ?? 'pending'));

        return $this->subject('Order Update: ' . $this->order->order_number)
            ->view('emails.order-status-updated')
            ->with([
                'order' => $this->order,
                'statusLabel' => $statusLabel,
            ]);
    }
}
