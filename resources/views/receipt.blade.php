{{-- <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt</title>
</head>
<style>
    body {
        font-family: 'XBRiyaz','Lateef', sans-serif;
        direction: rtl
    }
</style>
<body>
    <h1>Receipt #{{ $record->number }}</h1>
    <p style="font-family: 'XBRiyaz','Lateef', sans-serif">User: {{ $record->user->name }}</p>
    <p>Amount: {{ $record->amount }}</p>
    <p>Date: {{ $record->created_at }}</p>
    <!-- أضف المزيد من الحقول حسب الحاجة -->
</body>
</html> --}}
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>سند قبض</title>
    <style>
        body {
            font-family: 'Lateef','Arial', sans-serif;
            direction: rtl;
            text-align: right;
        }
        .container {
            width: 100%;
            border: 1px solid #000;
            padding: 20px;
        }
        .header {
            background-color: #0046ae;
            color: #fff;
            padding: 10px;
            text-align: center;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            font-weight: bold;
        }
        .details {
            border: 1px solid #000;
            padding: 10px;
            margin-top: 10px;
        }
        .row {
            display: flex;
            justify-content: space-between;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>شركة {{ $receipt->department->name }} - ليبيا</h2>
        </div>
        <div class="section">
            <div class="section-title">سند قبض</div>
            <div class="details">
                <div class="row">
                    <div>التاريخ: {{ $receipt->created_at }}</div>
                    <div>رقم: {{ $receipt->custom_id }}</div>
                </div>
                <div>استلمت من الأخ: {{ $receipt->market->name }}</div>
                <div>مبلغ وقدره: {{ $receipt->amount }} دينار</div>
                {{-- <div>وذلك مقابل: {{ $receipt->amount }}</div> --}}
                {{-- <div>الباقي: {{ $receipt->amount }} دينار</div> --}}
            </div>
        </div>
        <div class="section">
            <div>اسم المستلم: {{ $receipt->user->name }}</div>
            <div>التوقيع: '................................'</div>
        </div>
    </div>
</body>
</html>
