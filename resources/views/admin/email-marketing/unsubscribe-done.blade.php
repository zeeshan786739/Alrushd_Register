<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Unsubscribed</title>
    <style>body{font-family:system-ui,sans-serif;max-width:480px;margin:48px auto;padding:0 16px}</style>
</head>
<body>
    @if($success)
        <h1>You are unsubscribed</h1>
        <p>You will no longer receive marketing campaigns from this organization.</p>
    @else
        <h1>Link invalid</h1>
        <p>This unsubscribe link is invalid or has expired.</p>
    @endif
</body>
</html>
