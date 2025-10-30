<?php 
function ACMail($from, $subject, $message, $recipient_email) {
    $url = 'https://getfundedafrica.com/email/sendmail.php';
    $ch = curl_init($url);

    $postData = [
        'recipient_email' => $recipient_email,
        'message' => $message,
        'subject' => $subject,
        'fromName' => $from
    ];

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $postData,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/x-www-form-urlencoded',
            'Referer: https://getfundedafrica.com'
        ],
        CURLOPT_SSL_VERIFYPEER => false, // for testing
        CURLOPT_SSL_VERIFYHOST => false,
    ]);

    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        return ['success' => false, 'message' => "cURL error: $error"];
    }

    curl_close($ch);

    if ($httpCode != 200) {
        return ['success' => false, 'message' => "Mail API returned status $httpCode. Response: $result"];
    }

    return ['success' => true, 'message' => 'Mail sent successfully.'];
}

?>