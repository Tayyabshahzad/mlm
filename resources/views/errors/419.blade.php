<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Expired</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #0e1015;
            color: #fff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            background: #1a1d24;
            border-top: 3px solid #DFC82E;
            padding: 2.5rem 2rem;
            text-align: center;
            max-width: 420px;
            width: 90%;
        }
        .icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        h1 {
            font-size: 1.2rem;
            font-weight: 700;
            color: #DFC82E;
            margin-bottom: .5rem;
            letter-spacing: .3px;
        }
        p {
            font-size: .87rem;
            color: #9ca3af;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }
        a {
            display: inline-block;
            padding: .55rem 1.4rem;
            background: #DFC82E;
            color: #000;
            font-weight: 700;
            font-size: .88rem;
            text-decoration: none;
            letter-spacing: .3px;
            transition: background .15s;
        }
        a:hover { background: #c9b428; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">⏱</div>
        <h1>Session Expired</h1>
        <p>Your session has timed out for security reasons.<br>Please go back and try again.</p>
        <a href="javascript:history.back()">← Go Back</a>
    </div>
    <script>
        // Auto redirect back after 3 seconds
        setTimeout(function() { history.back(); }, 3000);
    </script>
</body>
</html>
