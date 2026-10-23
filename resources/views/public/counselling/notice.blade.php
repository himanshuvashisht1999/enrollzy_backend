<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ $title }}</title>
    <style>
        body { font-family: -apple-system, Segoe UI, Arial, sans-serif; background: #f6f7f9; color: #1b2230; margin: 0; padding: 16px; }
        main { max-width: 480px; margin: 12vh auto 0; background: #fff; border: 1px solid #dfe3ea; border-radius: 12px; padding: 28px; }
        h1 { font-size: 22px; margin: 0 0 10px; }
        p { color: #5a6475; line-height: 1.55; margin: 0 0 14px; }
        a.btn { display: inline-block; background: #2c4f8f; color: #fff; padding: 10px 18px; border-radius: 6px; text-decoration: none; }
    </style>
</head>
<body>
    <main>
        <h1>{{ $title }}</h1>
        @if($message)<p>{{ $message }}</p>@endif
        @if(!empty($manageUrl))<a class="btn" href="{{ $manageUrl }}">Manage my alerts</a>@endif
    </main>
</body>
</html>
