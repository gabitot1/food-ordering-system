<?php

namespace App\Mail;

use App\Models\Orders;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminNewOrderMail extends Mailable
{
    use Queueable, SerializesModels;

    public Orders $order;

    public function __construct(Orders $order)
    {
        $this->order = $order;
    }

    public function build()
    {
        return $this->subject('New Order Received: ' . $this->order->order_number)
            ->view('emails.admin-new-order')
            ->with([
                'order' => $this->order,
            ]);
    }
}

