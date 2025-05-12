<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject }}</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f4f4f4; padding: 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" border="0" style="max-width: 600px; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                    <tr>
                        <td style="padding: 20px; text-align: center; background-color: #f8f8f8; border-bottom: 1px solid #eee;">
                            <img src="{{ $logoUrl }}" alt="Logo de WintoWin" style="max-width: 150px; height: auto; display: block; margin: 0 auto;">
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 30px; color: #333;">
                            <h1 style="font-size: 24px; margin: 0 0 20px; color: #333;">{{ __('emails.greeting', ['name' => $userName]) }}</h1>
                            <p style="font-size: 16px; line-height: 1.5; margin: 0 0 20px;">{{ $message }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 20px; text-align: center; background-color: #f8f8f8; border-top: 1px solid #eee; font-size: 12px; color: #777;">
                            <p style="margin: 0;">{{ __('emails.footer_rights', ['year' => date('Y')]) }}</p>
                            <p style="margin: 5px 0 0;">{{ __('emails.contact_us') }} <a href="mailto:supervisor@wintowin.com" style="color: #007bff; text-decoration: none;">supervisor@wintowin.com</a></p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>