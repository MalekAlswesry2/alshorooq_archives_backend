<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt</title>
    <style>
        /* body { font-family: DejaVu Sans, sans-serif; } */
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
    </style>
</head>
<body>
    <h1>Receipt #{{ $receipt->custom_id }}</h1>
    <p>لتاريخ: {{ $receipt->created_at->format('d-m-Y') }}</p>
    <p>السوق: {{ $receipt->market->name }}</p>
    <p>القيمة: {{ $receipt->amount }}</p>
    <p>Payment Method: {{ $receipt->payment_method }}</p>

    <h3>User Details</h3>
    <p>Name: {{ $receipt->user->name }}</p>
    <p>Department: {{ $receipt->department->name }}</p>
    <p>Branch: {{ $receipt->branch->name }}</p>

    @if ($receipt->image)
        <h3>Attached Image</h3>
        <img src="{{ asset('storage/' . $receipt->image) }}" style="max-width: 100%;">
    @endif
</body>
</html>
