<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Account Balance Adjustment</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f4f4f4; color: #333; }
        .wrapper { max-width: 600px; margin: 30px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.1); }
        .header { background: #1a1a2e; padding: 30px 40px; text-align: center; }
        .header img { height: 45px; margin-bottom: 10px; }
        .header h1 { color: #fff; font-size: 20px; font-weight: 600; letter-spacing: .5px; }
        .header p { color: #a0aec0; font-size: 13px; margin-top: 4px; }
        .alert-bar { background: #f6ad55; padding: 12px 40px; text-align: center; }
        .alert-bar span { font-weight: 700; color: #7b341e; font-size: 13px; letter-spacing: .3px; }
        .body { padding: 36px 40px; }
        .greeting { font-size: 16px; font-weight: 600; margin-bottom: 16px; }
        .body p { font-size: 14px; line-height: 1.7; color: #4a5568; margin-bottom: 14px; }
        .info-box { background: #ebf8ff; border-left: 4px solid #3182ce; border-radius: 4px; padding: 16px 20px; margin: 22px 0; }
        .info-box .row { display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid #bee3f8; font-size: 13px; }
        .info-box .row:last-child { border-bottom: none; }
        .info-box .label { color: #4a5568; }
        .info-box .value { font-weight: 700; color: #2b6cb0; }
        .balance-box { background: #f0fff4; border: 2px solid #48bb78; border-radius: 6px; padding: 18px 20px; text-align: center; margin: 22px 0; }
        .balance-box .label { font-size: 13px; color: #276749; margin-bottom: 6px; }
        .balance-box .amount { font-size: 28px; font-weight: 800; color: #276749; }
        .footer { background: #f7fafc; padding: 24px 40px; text-align: center; border-top: 1px solid #e2e8f0; }
        .footer p { font-size: 12px; color: #718096; line-height: 1.6; }
        .footer strong { color: #4a5568; }
    </style>
</head>
<body>
<div class="wrapper">

    <div class="header">
        <h1>Global Visioners International</h1>
        <p>Saving Plan — Account Notification</p>
    </div>

    <div class="alert-bar">
        <span>⚠️ Important: Account Balance Adjustment</span>
    </div>

    <div class="body">

        <div class="greeting">Dear {{ $name }},</div>

        <p>
            We are writing to inform you about an important correction that has been made to your saving plan commission wallet.
        </p>

        <p>
            Due to a <strong>technical error in our system</strong>, excess commission was incorrectly credited to your account between
            <strong>{{ $periodFrom }}</strong> and <strong>{{ $periodTo }}</strong>.
            The error caused commission payments to be calculated at double the correct rate for Instalment #1 activations.
        </p>

        <div class="info-box">
            <div class="row">
                <span class="label">Username</span>
                <span class="value">{{ $username }}</span>
            </div>
            <div class="row">
                <span class="label">Excess commission credited (error)</span>
                <span class="value">{{ $excessAmount }}</span>
            </div>
            <div class="row">
                <span class="label">Amount adjusted / deducted</span>
                <span class="value">{{ $excessAmount }}</span>
            </div>
            @if($withdrawalCount > 0)
            <div class="row">
                <span class="label">Withdrawals made during this period</span>
                <span class="value">{{ $withdrawalCount }} withdrawal(s) — {{ $totalWithdrawn }}</span>
            </div>
            @endif
        </div>

        <p>
            The excess amount of <strong>{{ $excessAmount }}</strong> has been deducted from your commission wallet balance.
            This adjustment ensures your account reflects the correct commission amounts as per our saving plan terms.
        </p>

        <div class="balance-box">
            <div class="label">Your Current Commission Wallet Balance</div>
            <div class="amount">{{ $currentBalance }}</div>
        </div>

        <p>
            We sincerely apologize for any inconvenience this technical error may have caused. The underlying issue has been
            fully resolved and no future discrepancies will occur.
        </p>

        <p>
            If you have any questions or concerns regarding this adjustment, please contact our support team immediately.
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
