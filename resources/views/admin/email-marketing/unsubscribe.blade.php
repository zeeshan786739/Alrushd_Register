<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Unsubscribe</title>
    <style>body{font-family:system-ui,sans-serif;max-width:480px;margin:48px auto;padding:0 16px}button{padding:10px 16px;border-radius:8px;border:0;background:#0F274A;color:#fff;cursor:pointer}</style>
</head>
<body>
    <h1>Unsubscribe</h1>
    <p>You will be removed from marketing campaigns for this organization.</p>
    <form method="POST" action="{{ route('email-marketing.unsubscribe.store', $token) }}">
        @csrf
        <button type="submit">Confirm unsubscribe</button>
    </form>
</body>
</html>
