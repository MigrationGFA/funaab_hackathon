<?php 
function ACMail($from, $subject, $message, $recipient_email)
{
    $url = 'https://getfundedafrica.com/email/senderjson.php';

    $data = [
        "recipient_email" => $recipient_email,
        "message" => $message,
        "subject" => $subject,
        "fromName" => $from ?: "Xpanda | Remsana"
    ];

    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_SSL_VERIFYPEER => false, // optional, remove if using valid SSL
        CURLOPT_SSL_VERIFYHOST => false,
    ]);

    $response = curl_exec($ch);
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);

    curl_close($ch);

    // Handle errors
    if ($curl_error) {
        return [
            'success' => false,
            'message' => "cURL error: $curl_error"
        ];
    }

    if ($http_status !== 200) {
        return [
            'success' => false,
            'message' => "Mail API returned status $http_status. Response: $response"
        ];
    }

    // Try to parse JSON response from API
    $decoded = json_decode($response, true);

    if (json_last_error() === JSON_ERROR_NONE && isset($decoded['success'])) {
        return [
            'success' => $decoded['success'],
            'message' => $decoded['message'] ?? 'Mail API response received.'
        ];
    }

    // If response not JSON or doesn’t include success field
    return [
        'success' => true, // assume mail sent if API returned 200
        'message' => 'Mail sent successfully (no JSON response).'
    ];
}

?>