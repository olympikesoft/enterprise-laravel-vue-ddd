<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Donation Confirmation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .content {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Thank You for Your Donation!</h1>
        </div>

        <div class="content">
            <p>Dear {{ $donation->donor_name }},</p>

            <p>Thank you for your generous donation of ${{ number_format($donation->amount, 2) }}. Your support means a lot to us and will help make a difference.</p>

            <p>Donation Details:</p>
            <ul>
                <li>Transaction ID: {{ $donation->transaction_id }}</li>
                <li>Date: {{ $donation->created_at->format('F j, Y') }}</li>
                <li>Amount: ${{ number_format($donation->amount, 2) }}</li>
            </ul>

            <p>If you have any questions about your donation, please don't hesitate to contact us.</p>
        </div>

        <div class="footer">
            <p>This is an automated email. Please do not reply to this message.</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
