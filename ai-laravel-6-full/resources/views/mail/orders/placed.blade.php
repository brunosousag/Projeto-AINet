<h1>Thank you for your FunShirt order</h1>

<p>Your order #{{ $order->id }} has been received and is now pending.</p>
<p>Total: {{ number_format((float) $order->total_price, 2) }} EUR</p>

<p>We will email your receipt when the order is completed.</p>
