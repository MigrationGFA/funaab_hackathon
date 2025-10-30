<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('Africa/Lagos'); 

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once 'config.php';
require_once 'functions.php';

// Database connection
$conn = mysqli_connect(Config::$host, Config::$databaseUsername, Config::$databasePassword, Config::$databaseName);
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}

// Collect and sanitize form inputs
$Team_name              = trim($_POST['Team_name'] ?? '');
$Team_Email             = strtolower(trim($_POST['Team_Email'] ?? ''));
$Team_website           = trim($_POST['Team_website'] ?? '');
$Team_year              = trim($_POST['Team_year'] ?? '');
$Project_state          = trim($_POST['Project_state'] ?? '');
$Phone_number           = trim($_POST['Phone_number'] ?? '');
$Team_ContactPerson     = trim($_POST['Team_ContactPerson'] ?? '');
$Team_ContactEmail      = strtolower(trim($_POST['Team_ContactEmail'] ?? ''));
$Team_ContactDepartment = trim($_POST['Team_ContactDepartment'] ?? '');
$Team_ContactLevel      = trim($_POST['Team_ContactLevel'] ?? '');
$Team_matricNo           = trim($_POST['Team_matricNo'] ?? '');
$Team_problem          = trim($_POST['Team_problem'] ?? '');
$Team_needs            = trim($_POST['Team_needs'] ?? '');
$Team_members      = trim($_POST['Team_members'] ?? '');

// Basic validation
if (empty($Team_name) || empty($Team_Email) || empty($Team_ContactEmail)) {
    echo json_encode(['success' => false, 'message' => 'Team name, Team email, and Contact email are required.']);
    exit;
}

// Check if this email already exists
$check = $conn->prepare("SELECT id FROM participants WHERE Team_Email = ? OR Team_ContactEmail = ?");
$check->bind_param("ss", $Team_Email, $Team_ContactEmail);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'A participant with this email already exists.']);
    $check->close();
    $conn->close();
    exit;
}
$check->close();

// Insert participant record
$stmt = $conn->prepare("
    INSERT INTO participants (
        Team_name, Team_Email, Team_website, Team_year, Project_state, 
        Phone_number, Team_ContactPerson, Team_ContactEmail, Team_ContactDepartment, Team_ContactLevel, 
        Team_matricNo, Team_problem, Team_needs, Team_members
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "ssssssssssssss",
    $Team_name,
    $Team_Email,
    $Team_website,
    $Team_year,
    $Project_state,
    $Phone_number,
    $Team_ContactPerson,
    $Team_ContactEmail,
    $Team_ContactDepartment,
    $Team_ContactLevel,
    $Team_matricNo,
    $Team_problem,
    $Team_needs,
    $Team_members
);

// If insert is successful
if ($stmt->execute()) {

    // --- Email Acknowledgment ---
    $from = 'Agrotech Hackathon';
    $subject = "Acknowledgement of Your Application to Agrotech Hackathon";
    $message = "
    <!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <title>Agrotech Hackathon Registration</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            h2 { color: #198754; }
        </style>
    </head>
    <body>
        <div class='container'>
            <h2>Welcome to Agrotech Hackathon</h2>
            <p>Dear <strong>{$Team_ContactPerson}</strong>,</p>
            <p>
                Thank you for registering your team, <strong>{$Team_name}</strong>, for the Agrotech Hackathon.
                Your submission has been received successfully.
            </p>
            <p>
                Our team will review all applications carefully, and you will receive updates via email shortly.
            </p>
            <p>Kind regards,<br><strong>Agrotech Hackathon Organizing Team</strong></p>
            <hr>
            <p style='font-size:12px;color:#888;'>© 2025 Remsana Technologies. All rights reserved.</p>
        </div>
    </body>
    </html>
    ";

    $recipient_email = $Team_ContactEmail;
    $mailResult = ACMail($from, $subject, $message, $recipient_email);

    if ($mailResult['success']) {
        echo json_encode(['success' => true, 'message' => 'Thank you for signing up as a sponsor! We will contact you soon.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Sponsor added, but email failed to send.']);
        // echo json_encode(['success' => false, 'message' => 'Sponsor added, but email failed to send. Debug: '.$mailResult['message']]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to submit form. Please try again.']);
}

$stmt->close();
$conn->close();
?>
