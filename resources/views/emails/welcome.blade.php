<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ __('Welcome to Our Platform') }}</title>
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
        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #2980b9;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('assets/img/LogoLargo.png') }}" alt="Logo">
            <h1>{{ __('Welcome to Our Platform') }}</h1>
        </div>
        <div class="content">
            <p>{{ trans('greeting', ['name' => $user->name]) }}</p>
            <p>{{ __('Your account has been created successfully.') }}</p>
            <p>{{ __('Please set your password by clicking the button below. This link will expire in 2 days.') }}</p>
            <p>
                <a href="{{ $resetUrl }}" class="button">{{ __('Set Your Password') }}</a>
            </p>
            <p>{{ __('If you did not request this, please ignore this email.') }}</p>
            <p>{!! trans('signature', ['app' => $appName]) !!}</p>
        </div>
        <div class="footer">
            <p>{!! trans('footer', ['year' => $year, 'app' => $appName]) !!}</p>
        </div>
    </div>
</body>
</html>