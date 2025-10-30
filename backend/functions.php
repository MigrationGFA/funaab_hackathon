<?php 
function ACMail($from, $subject, $message, $recipient_email){
    $url = 'https://getfundedafrica.com/email/sendmail.php';
    $ch = curl_init($url);

    $postData = [
        'recipient_email' => $recipient_email,
        'message' => $message,
        'subject' => $subject,
        'fromName' => $from
    ];

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // temporarily disable for debugging
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);

    $result = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        error_log("cURL error while sending mail: $error");
        return ['success' => false, 'message' => $error];
    }

    curl_close($ch);

    // Log result for debugging
    error_log("Mail API response: HTTP $httpcode - $result");

    if ($httpcode !== 200) {
        return ['success' => false, 'message' => "Mail API returned status $httpcode"];
    }

    // Try to decode JSON if applicable
    $decoded = json_decode($result, true);
    if (is_array($decoded) && isset($decoded['success']) && $decoded['success'] === true) {
        return ['success' => true];
    }

    return ['success' => false, 'message' => "Mail API did not confirm success: $result"];
}

?>