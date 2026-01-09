<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Not Found</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html,
        body {
            height: 100%;
            width: 100%;
        }

        body {
            min-height: 100vh;
            background: #f1f5f9;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            padding: 1.5rem;
        }

        .background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background:
                radial-gradient(circle at 30% 20%, rgba(239, 68, 68, 0.06) 0%, transparent 50%),
                radial-gradient(circle at 70% 80%, rgba(249, 115, 22, 0.05) 0%, transparent 50%);
            pointer-events: none;
            z-index: 0;
        }

        .container {
            text-align: center;
            position: relative;
            z-index: 1;
        }

        h1 {
            font-size: 1.625rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.5rem;
            letter-spacing: -0.03em;
        }

        p {
            color: #64748b;
            font-size: 0.875rem;
            line-height: 1.6;
            max-width: 320px;
            margin: 0 auto;
        }

        .domain {
            margin-top: 2rem;
            font-size: 0.8125rem;
            font-weight: 600;
            color: #475569;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 0.75rem 1.5rem;
            display: inline-flex;
            align-items: center;
            gap: 0.625rem;
            box-shadow:
                0 1px 3px rgba(0, 0, 0, 0.04),
                0 4px 12px rgba(0, 0, 0, 0.03);
        }

        .status-dot {
            width: 8px;
            height: 8px;
            background: #ef4444;
            border-radius: 50%;
        }

        .error-code {
            margin-top: 2rem;
            font-size: 0.75rem;
            font-weight: 500;
            color: #94a3b8;
            letter-spacing: 0.05em;
        }
    </style>
</head>

<body>
    <div class="background"></div>

    <div class="container">
        <h1>Workspace Not Found</h1>

        <p>The requested workspace doesn't exist or has been removed.</p>

        <div class="domain">
            <span class="status-dot"></span>
            {{ $subdomain }}
        </div>

        <div class="error-code">ERROR 404</div>
    </div>
</body>

</html>
