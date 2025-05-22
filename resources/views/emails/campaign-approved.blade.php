<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Campaign Approved</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333333; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
        <h1 style="color: #2d3748; margin-bottom: 20px;">Campaign Approved!</h1>

        <p>Dear {{ $campaign->user->name }},</p>

        <p>We're pleased to inform you that your campaign "{{ $campaign->name }}" has been approved and is now live on our platform.</p>

        <div style="margin: 25px 0;">
            <p>Campaign Details:</p>
            <ul style="list-style: none; padding-left: 0;">
                <li>Campaign Name: {{ $campaign->name }}</li>
                <li>Target Amount: ${{ number_format($campaign->target_amount, 2) }}</li>
                <li>Start Date: {{ $campaign->start_date->format('M d, Y') }}</li>
            </ul>
        </div>

        <p>You can now start sharing your campaign with potential donors.</p>

        <div style="margin: 30px 0;">
            <a href="{{ url('/campaigns/' . $campaign->id) }}" style="background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">View Your Campaign</a>
        </div>

        <p>If you have any questions, please don't hesitate to contact our support team.</p>

        <p>Best regards,<br>The Team</p>
    </div>
</body>
</html>
