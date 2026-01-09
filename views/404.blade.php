<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Not Available</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        :root {
            --bg: #e5e7eb;
            --card: #e2e8f0;
            --text: #020617;
            --muted: #334155;
            --border: #cbd5e1;
            --accent: #1e293b;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        body {
            min-height: 100vh;
            background: radial-gradient(circle at top, #c7d2fe, #e5e7eb 65%);
            display: grid;
            place-items: center;
            color: var(--text);
        }

        .card {
            background: var(--card);
            border-radius: 14px;
            padding: 2.75rem 3rem;
            max-width: 420px;
            width: 100%;
            text-align: center;
            box-shadow:
                0 20px 40px rgba(15, 23, 42, .22),
                inset 0 1px 0 rgba(255, 255, 255, .6);
        }

        .icon {
            width: 56px;
            height: 56px;
            margin: 0 auto 1.5rem;
            border-radius: 50%;
            background: rgba(30, 41, 59, .12);
            display: grid;
            place-items: center;
            color: var(--accent);
            font-size: 30px;
            font-weight: 600;
        }

        h1 {
            font-size: 1.45rem;
            margin-bottom: .75rem;
            font-weight: 600;
        }

        p {
            color: var(--muted);
            font-size: .95rem;
            line-height: 1.6;
        }

        .domain {
            margin-top: 1.4rem;
            font-size: .85rem;
            color: #020617;
            background: #cbd5e1;
            border-radius: 8px;
            padding: .55rem .9rem;
            display: inline-block;
            letter-spacing: .35px;
        }
    </style>
</head>

<body>
    <div class="card">
        <div class="icon">!</div>

        <h1>Not Ready Yet</h1>

        <p>
            This page hasn’t been created yet or is currently unavailable.
        </p>

        <div class="domain">
            {{ $subdomain }}
        </div>
    </div>
</body>

</html>
