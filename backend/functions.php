<?php 
function ACMail($from, $subject, $message, $recipient_email){
	$url = 'https://getfundedafrica.com/email/senderjson.php';
	// $url = 'https://gfa-tech.com/email/sender.php';
	$ch = curl_init($url);

	$postData = array(
		'recipient_email' => "{$recipient_email}",
		'message' => "{$message}",
		'subject' => "{$subject}",
		'fromName' => "{$from}"
	);

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POST, true); 
	curl_setopt($ch, CURLOPT_POSTFIELDS, $postData); 
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); 
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

	$result = curl_exec($ch);
	if (curl_errno($ch)) {
		//echo 'Error:' . curl_error($ch);
		return false;
	}
	curl_close($ch);
	return true;
}

?>