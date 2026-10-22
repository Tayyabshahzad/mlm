<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Digital Card Received</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f4f4f4; color: #333; }
        .wrapper { max-width: 600px; margin: 30px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.1); }
        .header { background: #1a1a2e; padding: 30px 40px; text-align: center; }
        .header h1 { color: #fff; font-size: 20px; font-weight: 600; letter-spacing: .5px; }
        .header p { color: #a0aec0; font-size: 13px; margin-top: 4px; }
        .status-bar { background: #b8913a; padding: 12px 40px; text-align: center; }
        .status-bar span { font-weight: 700; color: #fff; font-size: 13px; letter-spacing: .3px; }
        .body { padding: 36px 40px; }
        .greeting { font-size: 16px; font-weight: 600; margin-bottom: 16px; }
        .body p { font-size: 14px; line-height: 1.7; color: #4a5568; margin-bottom: 14px; }
        .info-box { background: #fdf8ec; border-left: 4px solid #b8913a; border-radius: 4px; padding: 16px 20px; margin: 22px 0; }
        .info-box .row { display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid #f0e2bd; font-size: 13px; }
        .info-box .row:last-child { border-bottom: none; }
        .info-box .label { color: #4a5568; }
        .info-box .value { font-weight: 700; color: #7a5c17; }
        .btn { display: inline-block; background: #1a1a2e; color: #fff !important; text-decoration: none; padding: 12px 26px; border-radius: 6px; font-size: 14px; font-weight: 600; }
        .notice-box { background: #fffbeb; border: 1px solid #f6e05e; border-radius: 6px; padding: 14px 18px; margin: 18px 0; font-size: 13px; color: #744210; }
        .footer { background: #f7fafc; padding: 24px 40px; text-align: center; border-top: 1px solid #e2e8f0; }
        .footer p { font-size: 12px; color: #718096; line-height: 1.6; }
    </style>
</head>
<body>
<div class="wrapper">

    <div class="header">
        <h1>Global Visioners International</h1>
        <p>Digital Card</p>
    </div>

    <div class="status-bar">
        <span>You have received a new card</span>
    </div>

    <div class="body">
        <div class="greeting">Dear {{ $name }},</div>

        <p>{{ $sender }} has shared a digital card with you. It is assigned to your account and can be used to pay a saving-plan instalment.</p>

        <div class="info-box">
            <div class="row"><span class="label">From</span><span class="value">{{ $sender }}</span></div>
            <div class="row"><span class="label">Card</span><span class="value">{{ $masked }}</span></div>
            <div class="row"><span class="label">Amount</span><span class="value">{{ $amount }}</span></div>
            <div class="row"><span class="label">Valid Until</span><span class="value">{{ $expiresAt }}</span></div>
        </div>

        <p style="text-align:center; margin: 26px 0;">
            <a href="{{ $url }}" class="btn">View Card in Portal</a>
        </p>

        <div class="notice-box">
            For your security, the card PIN is shown only inside your portal after you log in.
            This email and the attached image are not proof of ownership. The card can be used only from your own account,
            and its status is always checked against our records.
        </div>
    </div>

    <div class="footer">
        <p>This is an automated message from Global Visioners International. Please do not reply.</p>
    </div>
</div>
</body>
</html>
