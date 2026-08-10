<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Suspended — {{ config('saas.name') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            padding: 24px;
        }
        .card {
            background: #fff; border-radius: 20px; max-width: 480px; width: 100%;
            padding: 48px 40px; text-align: center;
            box-shadow: 0 25px 60px rgba(0,0,0,.35);
        }
        .icon {
            width: 72px; height: 72px; border-radius: 50%; margin: 0 auto 24px;
            background: #fef2f2; color: #dc2626; display: flex; align-items: center; justify-content: center;
            font-size: 34px;
        }
        h1 { font-size: 22px; color: #0f172a; margin-bottom: 12px; }
        p { color: #64748b; font-size: 15px; line-height: 1.6; margin-bottom: 8px; }
        .status {
            display: inline-block; margin: 16px 0 24px; padding: 6px 14px; border-radius: 999px;
            background: #fef2f2; color: #dc2626; font-weight: 600; font-size: 13px; text-transform: capitalize;
        }
        .actions { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
        .btn {
            display: inline-block; padding: 12px 22px; border-radius: 10px; font-weight: 600;
            font-size: 14px; text-decoration: none; border: none; cursor: pointer;
        }
        .btn-primary { background: #2563eb; color: #fff; }
        .btn-ghost { background: #f1f5f9; color: #334155; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">&#9888;</div>
        <h1>{{ $organization->name }} is currently locked</h1>
        <p>Your school's access to the admin panel has been paused.</p>
        <span class="status">{{ $organization->status instanceof \App\Enums\Platform\OrganizationStatus ? $organization->status->label() : $organization->status }}</span>
        <p>If you believe this is a billing issue, you can renew your subscription below.
            Otherwise please contact <a href="mailto:{{ \App\Models\PlatformSetting::get('support_email', config('saas.support_email')) }}">{{ \App\Models\PlatformSetting::get('support_email', config('saas.support_email')) }}</a>.</p>
        <div class="actions" style="margin-top: 24px;">
            <a href="{{ route('admin.billing.index') }}" class="btn btn-primary">Go to Billing</a>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="btn btn-ghost">Log out</button>
            </form>
        </div>
    </div>
</body>
</html>
