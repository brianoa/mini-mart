<?php

return [
    'adminEmail' => 'admin@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Example.com mailer',

    
    'mpesa' => [
        'env'           => 'sandbox',
        'baseUrl'       => 'https://sandbox.safaricom.co.ke',
        'consumerKey'   => 'WU7m6dxg4pGumr1jwDBmKIqCJCMEe1ykJLuWMuXeyjAR3a23',
        'consumerSecret'=> 'aTe02HfBSlmB7KByMz8mCyauE0lSrTGW1oeA4xfmJltWV6vMdeh6wCd3njPx9kPN',
        'shortCode'     => '174379', // BusinessShortCode
        'passKey'       => 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919',
        // temp callback; will replace with your actual public URL
        'callbackUrl'   => 'https://291d479ce9a0.ngrok-free.app',
    ],

];
