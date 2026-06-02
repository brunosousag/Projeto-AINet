<h1>Your FunShirt order was canceled</h1>

<p>Your order #{{ $order->id }} has been canceled.</p>

@if ($order->reason_for_cancellation)
    <p>Reason: {{ $order->reason_for_cancellation }}</p>
@endif
