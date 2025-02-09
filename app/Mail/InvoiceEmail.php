<?php

namespace App\Mail;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvoiceEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $order; // Pass your order details

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($order)
    {
        $this->order = $order;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // Generate PDF
        $pdf = Pdf::loadView('pdf.invoice', ['order' => $this->order]);

        return $this->subject('Your Order Invoice')
            ->markdown('emails.invoice')
            ->with(['order' => $this->order])
            ->attachData($pdf->output(), 'invoice.pdf', [
                'mime' => 'application/pdf',
            ])
            ->to('info@northland-co.com');
    }
}
