<!DOCTYPE html>
<html>

<head>
    <title>Invoice</title>
</head>

<body>
    <h1>Order Invoice</h1>
    <p>Order ID: {{ $order->id }}</p>
    <p>Customer Name: {{ $order->customer_name }}</p>
    <p>Total: ${{ $order->total }}</p>
</body>

</html>
