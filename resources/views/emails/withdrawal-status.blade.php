<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Withdrawal {{ $isApproved ? 'Approved' : 'Rejected' }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f4f4f4; color: #333; }
        .wrapper { max-width: 600px; margin: 30px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.1); }
        .header { background: #1a1a2e; padding: 30px 40px; text-align: center; }
        .header h1 { color: #fff; font-size: 20px; font-weight: 600; letter-spacing: .5px; }
        .header p { color: #a0aec0; font-size: 13px; margin-top: 4px; }
        .status-bar-approved { background: #38a169; padding: 12px 40px; text-align: center; }
        .status-bar-rejected { background: #e53e3e; padding: 12px 40px; text-align: center; }
        .status-bar-approved span, .status-bar-rejected span { font-weight: 700; color: #fff; font-size: 13px; letter-spacing: .3px; }
        .body { padding: 36px 40px; }
        .greeting { font-size: 16px; font-weight: 600; margin-bottom: 16px; }
        .body p { font-size: 14px; line-height: 1.7; color: #4a5568; margin-bottom: 14px; }
        .info-box { border-left: 4px solid; border-radius: 4px; padding: 16px 20px; margin: 22px 0; }
        .info-box.approved { background: #f0fff4; border-color: #48bb78; }
        .info-box.rejected { background: #fff5f5; border-color: #fc8181; }
        .info-box .row { display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid rgba(0,0,0,.06); font-size: 13px; }
        .info-box .row:last-child { border-bottom: none; }
        .info-box .label { color: #4a5568; }
        .info-box.approved .value { font-weight: 700; color: #276749; }
        .info-box.rejected .value { font-weight: 700; color: #c53030; }
        .amount-box-approved { background: #f0fff4; border: 2px solid #48bb78; border-radius: 6px; padding: 18px 20px; text-align: center; margin: 22px 0; }
        .amount-box-rejected { background: #fff5f5; border: 2px solid #fc8181; border-radius: 6px; padding: 18px 20px; text-align: center; margin: 22px 0; }
        .amount-box-approved .label, .amount-box-rejected .label { font-size: 13px; margin-bottom: 6px; }
        .amount-box-approved .label { color: #276749; }
        .amount-box-rejected .label { color: #c53030; }
        .amount-box-approved .amount { font-size: 28px; font-weight: 800; color: #276749; }
        .amount-box-rejected .amount { font-size: 28px; font-weight: 800; color: #c53030; }
        .footer { background: #f7fafc; padding: 24px 40px; text-align: center; border-top: 1px solid #e2e8f0; }
        .footer p { font-size: 12px; color: #718096; line-height: 1.6; }
    </style>
</head>
<body>
<div class="wrapper">

    <div class="header">
        <h1>Global Visioners International</h1>
        <p>Withdrawal — Status Update</p>
    </div>

    @if($isApproved)
    <div class="status-bar-approved">
        <span>✅ Withdrawal Request Approved</span>
    </div>
    @else
    <div class="status-bar-rejected">
        <span>❌ Withdrawal Request Rejected</span>
    </div>
    @endif

    <div class="body">

        <div class="greeting">Dear {{ $name }},</div>

        @if($isApproved)
        <p>
            We are pleased to inform you that your withdrawal request has been <strong>approved</strong>.
            Your payment is being processed and will be transferred to your registered account shortly.
        </p>

        <div class="info-box approved">
            <div class="row">
                <span class="label">Username</span>
                <span class="value">{{ $username }}</span>
            </div>
            <div class="row">
                <span class="label">Withdrawal Method</span>
                <span class="value">{{ $requestType }}</span>
            </div>
            <div class="row">
                <span class="label">Status</span>
                <span class="value">✅ Approved</span>
            </div>
        </div>

        <div class="amount-box-approved">
            <div class="label">Approved Withdrawal Amount</div>
            <div class="amount">{{ $amount }}</div>
        </div>

        <p>
            Please allow 1–3 business days for the funds to reflect in your account depending on your withdrawal method.
        </p>

        @else
        <p>
            We regret to inform you that your withdrawal request has been <strong>rejected</strong>.
            The amount has been <strong>refunded</strong> back to your online wallet.
        </p>

        <div class="info-box rejected">
            <div class="row">
                <span class="label">Username</span>
                <span class="value">{{ $username }}</span>
            </div>
            <div class="row">
                <span class="label">Withdrawal Method</span>
                <span class="value">{{ $requestType }}</span>
            </div>
            <div class="row">
                <span class="label">Status</span>
                <span class="value">❌ Rejected</span>
            </div>
        </div>

        <div class="amount-box-rejected">
            <div class="label">Amount Refunded to Wallet</div>
            <div class="amount">{{ $amount }}</div>
        </div>

        <p>
            If you believe this was an error or need further clarification, please contact our support team through your member portal.
        </p>
        @endif

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
