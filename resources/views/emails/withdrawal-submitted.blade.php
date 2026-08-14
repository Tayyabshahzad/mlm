<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Withdrawal Request Submitted</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f4f4f4; color: #333; }
        .wrapper { max-width: 600px; margin: 30px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.1); }
        .header { background: #1a1a2e; padding: 30px 40px; text-align: center; }
        .header h1 { color: #fff; font-size: 20px; font-weight: 600; letter-spacing: .5px; }
        .header p { color: #a0aec0; font-size: 13px; margin-top: 4px; }
        .status-bar { background: #3182ce; padding: 12px 40px; text-align: center; }
        .status-bar span { font-weight: 700; color: #fff; font-size: 13px; letter-spacing: .3px; }
        .body { padding: 36px 40px; }
        .greeting { font-size: 16px; font-weight: 600; margin-bottom: 16px; }
        .body p { font-size: 14px; line-height: 1.7; color: #4a5568; margin-bottom: 14px; }
        .info-box { background: #ebf8ff; border-left: 4px solid #3182ce; border-radius: 4px; padding: 16px 20px; margin: 22px 0; }
        .info-box .row { display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid #bee3f8; font-size: 13px; }
        .info-box .row:last-child { border-bottom: none; }
        .info-box .label { color: #4a5568; }
        .info-box .value { font-weight: 700; color: #2b6cb0; }
        .notice-box { background: #fffbeb; border: 1px solid #f6e05e; border-radius: 6px; padding: 14px 18px; margin: 18px 0; font-size: 13px; color: #744210; }
        .footer { background: #f7fafc; padding: 24px 40px; text-align: center; border-top: 1px solid #e2e8f0; }
        .footer p { font-size: 12px; color: #718096; line-height: 1.6; }
    </style>
</head>
<body>
<div class="wrapper">

    <div class="header">
        <h1>Global Visioners International</h1>
        <p>Withdrawal — Request Confirmation</p>
    </div>

    <div class="status-bar">
        <span>📋 Withdrawal Request Submitted</span>
    </div>

    <div class="body">

        <div class="greeting">Dear {{ $name }},</div>

        <p>
            Your withdrawal request has been successfully submitted and is now under review.
            Our team will process it shortly.
        </p>

        <div class="info-box">
            <div class="row">
                <span class="label">Username</span>
                <span class="value">{{ $username }}</span>
            </div>
            <div class="row">
                <span class="label">Withdrawal Amount</span>
                <span class="value">{{ $amount }}</span>
            </div>
            <div class="row">
                <span class="label">Transfer Fee</span>
                <span class="value">{{ $fee }}</span>
            </div>
            <div class="row">
                <span class="label">Net Receivable</span>
                <span class="value">{{ $net }}</span>
            </div>
            <div class="row">
                <span class="label">Withdrawal Method</span>
                <span class="value">{{ $requestType }}</span>
            </div>
            <div class="row">
                <span class="label">Status</span>
                <span class="value">Pending Review</span>
            </div>
        </div>

        <div class="notice-box">
            ⏳ Please allow 24–48 hours for processing. You will receive a confirmation email once your request is approved or if any action is required.
        </div>

        <p>
            If you did not submit this withdrawal request, please contact our support team immediately.
        </p>

        <p style="margin-top:20px;">
            Warm regards,<br>
            <strong>Global Visioners International</strong><br>
            <span style="font-size:12px;color:#718096;">Support Team</span>
        </p>

    </div>

    <div class="footer">
        <p>
            <strong>Global Visioners International</strong><br>
            This is an automated notification. Please do not reply to this email.<br>
            For support, contact us through your member portal.
        </p>
    </div>

</div>
</body>
</html>
