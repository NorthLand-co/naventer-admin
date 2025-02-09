@component('mail::message')
    # Thank You for Your Purchase!

    Your order has been processed successfully. Attached is the invoice for your order.

    @component('mail::table')
        | Item | Quantity | Price |
        | ------------- |:------------:| -------:|
        @foreach ($order->items as $item)
            | {{ $item->name }} | {{ $item->quantity }} | ${{ $item->price }} |
        @endforeach
    @endcomponent

    @component('mail::button', ['url' => 'https://northland-co.com'])
        View Order
    @endcomponent

    Thank you for shopping with us!

    Best Regards,<br>
    {{ config('app.name') }}
@endcomponent
