<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تحميل التطبيق</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: linear-gradient(to right, #007bff, #00d4ff);
            margin: 0;
            color: #fff;
            text-align: center;
        }
        .container {
            background: rgba(255, 255, 255, 0.15);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            max-width: 400px;
        }
        h1 {
            font-size: 24px;
            margin-bottom: 10px;
        }
        p {
            font-size: 16px;
            margin-bottom: 20px;
        }
        .download-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 15px;
            background-color: #fff;
            color: #007bff;
            text-decoration: none;
            font-size: 18px;
            border-radius: 30px;
            font-weight: bold;
            transition: 0.3s;
        }
        .download-btn i {
            margin-right: 10px;
        }
        .download-btn:hover {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📲 تحميل التطبيق</h1>
        <p>اضغط على الزر أدناه لتحميل التطبيق مباشرة.</p>
        <a href="{{ $downloadLink }}" class="download-btn">
            <i class="fas fa-download"></i> تحميل الآن
        </a>
    </div>
</body>
</html>
