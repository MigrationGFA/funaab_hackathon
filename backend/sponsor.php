<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('UTC'); 

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once 'config.php';
require_once 'functions.php';

// Database Connection
$conn = mysqli_connect(
    Config::$host,
    Config::$databaseUsername,
    Config::$databasePassword,
    Config::$databaseName
);

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}

// Collect and sanitize inputs
$Sponsors_name       = trim($_POST['Sponsors_name'] ?? '');
$Sponsors_Emailaddress = trim(strtolower($_POST['Sponsors_Emailaddress'] ?? ''));
$Phone_number         = trim($_POST['Phone_number'] ?? '');
$Sponsor_type         = trim($_POST['Sponsor_type'] ?? '');
$Company_name         = trim($_POST['Company_name'] ?? '');

// Validate mandatory fields
if (empty($Sponsors_name) || empty($Sponsors_Emailaddress)) {
    echo json_encode(['success' => false, 'message' => 'Sponsor name and email address are required.']);
    $conn->close();
    exit;
}

// Check for duplicate email
$checkStmt = $conn->prepare("SELECT id FROM sponsors WHERE Sponsors_Emailaddress = ?");
$checkStmt->bind_param("s", $Sponsors_Emailaddress);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'A sponsor with this email already exists.']);
    $checkStmt->close();
    $conn->close();
    exit;
}
$checkStmt->close();

// Insert new sponsor
$stmt = $conn->prepare("
    INSERT INTO sponsors (Sponsors_name, Sponsors_Emailaddress, Phone_number, Sponsor_type, Company_name)
    VALUES (?, ?, ?, ?, ?)
");
$stmt->bind_param("sssss", $Sponsors_name, $Sponsors_Emailaddress, $Phone_number, $Sponsor_type, $Company_name);

if ($stmt->execute()) {
    // Prepare Email Content
    $from = 'Agrotech Hackathon Team';
    $subject = "Acknowledgement of Your Sponsorship - Agrotech Hackathon";
    $message = "
    <!DOCTYPE html>
    <html lang='en'>
    <head>
      <meta charset='UTF-8'>
      <meta name='viewport' content='width=device-width, initial-scale=1.0'>
      <title>Thank You for Sponsoring Agrotech Hackathon</title>
      <style>
        body {
          font-family: Arial, sans-serif;
          background-color: #ffffff;
          color: #333;
          margin: 0;
          padding: 0;
        }
        .container {
          width: 90%;
          max-width: 600px;
          margin: 0 auto;
          padding: 20px;
        }
        header {
          text-align: center;
          margin-bottom: 20px;
        }
        header img {
          width: auto;
          height: 70px;
        }
        .content {
          line-height: 1.6;
        }
        footer {
          text-align: center;
          font-size: 12px;
          margin-top: 20px;
          color: #666;
        }
      </style>
    </head>
    <body>
      <div class='container'>
        <header>
          <img src='../images/h5-logo-dark.png' alt='Agrotech Hackathon'>
        </header>
        <div class='content'>
          <p>Dear {$Sponsors_name},</p>

          <p>Greetings from <strong>Federal University of Agriculture, Abeokuta (FUNAAB)</strong>, 
          the <strong>Centre for Entrepreneurial Studies</strong>, and <strong>REMSANA Technologies</strong>, 
          a subdivision of <strong>GFA Technologies Group</strong>.</p>

          <p>Thank you for signing up to be our most valued contributor. We truly appreciate your 
          partnership and commitment to driving innovation in agriculture through technology.</p>

          <p>We will reach out to you soon with more details.</p>

          <p>In the meantime, if you have any enquiries or would like to establish a personal contact, 
          please reach out to:</p>

          <ul>
            <li><strong>Professor Ayo-John Emily</strong> — ayo-johnei@funaab.edu.ng</li>
            <li><strong>Promise Adetoro</strong> — promise@gfa-tech.com</li>
          </ul>

          <p>Kind regards,<br>
          <strong>Agrotech Hackathon Organizing Team</strong></p>
        </div>
        <footer>
        <p style='font-size:12px;color:#888;'>© 2025 Remsana Technologies. All rights reserved.</p>
        </footer>
      </div>
    </body>
    </html>
    ";

    // Send acknowledgment email
    $recipient_email = $Sponsors_Emailaddress;

    $mailResult = ACMail($from, $subject, $message, $recipient_email);

    if ($mailResult['success']) {
        echo json_encode(['success' => true, 'message' => 'Thank you for signing up as a sponsor! We will contact you soon.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Sponsor added, but email failed to send.']);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Failed to submit sponsor information. Please try again.']);
}

$stmt->close();
$conn->close();
?>
