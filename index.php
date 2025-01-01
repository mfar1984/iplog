<?php
// Set the log file path
$logFile = __DIR__ . '/ip_log.txt'; // Use __DIR__ for the current directory

// Function to get the user's IP address
function getUserIP(): string {
    // Check for IP from shared internet
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    }
    // Check for IP from the proxy
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]; // Get the first IP in the list
    }
    // Return the remote IP address
    return $_SERVER['REMOTE_ADDR'];
}

// Get the current date and time adjusted by +8 hours
$dateTime = new DateTime('now', new DateTimeZone('UTC'));
$dateTime->modify('+8 hours');
$date = $dateTime->format('Y-m-d H:i:s');

// Get the user's IP address
$userIP = getUserIP();

// Prepare the log entry
$logEntry = "[$date] IP: $userIP\n";

// Append the log entry to the log file
if (file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX) === false) {
    // Handle error in logging
    echo "Error logging IP address.";
} else {
    // Optionally, you can display a message
    echo "Your IP address has been logged.";
}
?>
