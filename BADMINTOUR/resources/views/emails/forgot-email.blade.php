<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Email Address</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #2C5F4F; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;">
        <h1 style="color: white; margin: 0;">Badminton Tournament Management</h1>
    </div>
    
    <div style="background-color: #f9f9f9; padding: 30px; border: 1px solid #ddd; border-top: none; border-radius: 0 0 8px 8px;">
        <h2 style="color: #2C5F4F; margin-top: 0;">Email Recovery</h2>
        
        <p>Hello {{ $user->first_name }} {{ $user->last_name }},</p>
        
        <p>You requested to recover your email address. Your registered email is:</p>
        
        <div style="background-color: white; padding: 15px; border: 2px solid #D4A574; border-radius: 5px; text-align: center; margin: 20px 0;">
            <p style="font-size: 18px; font-weight: bold; color: #2C5F4F; margin: 0;">{{ $user->email }}</p>
        </div>
        
        <p>If you did not request this, please ignore this email or contact support if you have concerns.</p>
        
        <p style="margin-top: 30px;">Best regards,<br>Badminton Tournament Management Team</p>
    </div>
    
    <div style="text-align: center; margin-top: 20px; color: #666; font-size: 12px;">
        <p>This is an automated message. Please do not reply to this email.</p>
    </div>
</body>
</html>

