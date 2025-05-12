<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $subject }}</title>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            color: #333;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        .header img {
            width: 150px;
            height: auto;
            margin-right: 20px;
        }
        .header h1 {
            font-size: 24px;
            margin: 0;
            color: #2c3e50;
        }
        .content {
            padding: 20px;
            background-color: #fff;
            border-radius: 6px;
        }
        .content p {
            margin: 0 0 15px;
            font-size: 16px;
        }
        .footer {
            text-align: center;
            font-size: 12px;
            color: #777;
            margin-top: 20px;
        }
        .highlight {
            font-weight: bold;
            color: #2980b9;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ $logoUrl }}" alt="Logo">
            <h1>{{ $subject }}</h1>
        </div>
        <div class="content">
            <p>{{ trans('greeting', ['name' => $userName]) }}</p>

            @if($action === 'invitation_deleted')
                @if($isInviter)
                    <p>{!! trans('invitation_deleted.inviter', ['identity' => $identityName, 'role' => $role]) !!}</p>
                @else
                    <p>{!! trans('invitation_deleted.invitee', ['role' => $role, 'identity' => $identityName]) !!}</p>
                @endif
                <p>{!! trans('contact_admin', ['admin_name' => $adminName, 'admin_email' => $adminEmail]) !!}</p>

            @elseif($action === 'invitation_deleted_permanently')
                @if($isInviter)
                    <p>{!! trans('invitation_deleted_permanently.inviter', ['identity' => $identityName, 'role' => $role]) !!}</p>
                @else
                    <p>{!! trans('invitation_deleted_permanently.invitee', ['role' => $role, 'identity' => $identityName]) !!}</p>
                @endif
                <p>{!! trans('contact_admin', ['admin_name' => $adminName, 'admin_email' => $adminEmail]) !!}</p>

            @elseif($action === 'invitation_restored')
                @if($isInviter)
                    <p>{!! trans('invitation_restored.inviter', ['identity' => $identityName, 'role' => $role]) !!}</p>
                @else
                    <p>{!! trans('invitation_restored.invitee', ['role' => $role, 'identity' => $identityName]) !!}</p>
                @endif
                <p>{!! trans('contact_us', ['email' => $adminEmail]) !!}</p>

            @elseif($action === 'suspended')
                @if($isInviter)
                    <p>{!! trans('identity_suspended_body_inviter') !!}</p>
                @else
                    <p>{!! trans('identity_suspended_body_guest') !!}</p>
                @endif
                @if($reason)
                    <p>{!! trans('reason', ['reason' => $reason]) !!}</p>
                @endif
                <p>{!! trans('contact_admin', ['admin_name' => $adminName, 'admin_email' => $adminEmail]) !!}</p>

            @elseif($action === 'reactivated')
                @if($isInviter)
                    <p>{!! trans('identity_reactivated_body_inviter') !!}</p>
                @else
                    <p>{!! trans('identity_reactivated_body_guest') !!}</p>
                @endif
                <p>{!! trans('contact_us', ['email' => $adminEmail]) !!}</p>

            @else
                <p>{!! trans('action_line', ['identity' => $identityName, 'action' => $action]) !!}</p>
                @if($reason)
                    <p>{!! trans('reason', ['reason' => $reason]) !!}</p>
                @endif
            @endif

            @if($requestedChanges)
                <p>{!! trans('requested_changes', ['changes' => $requestedChanges]) !!}</p>
            @endif

            <p>{!! trans('signature', ['app' => $appName]) !!}</p>
        </div>
        <div class="footer">
            <p>{!! trans('footer', ['year' => $year, 'app' => $appName]) !!}</p>
        </div>
    </div>
</body>
</html>