<?php
// Written for Eskilstuna Kommun's implementation of EntryScape, this script requests an authentication cookie from EntryScape to authenticate with the API.
// The script is responsible for uploading a single file to the endpoint.
// Created by Christoffer Alvarsson

// Default values
$username = "";
$password = "";
$filename = "";
$endpoint = "";
$store = "";
$resource = "";

$options = getopt("u:p:f:s:r:e:");
if (isset($options['u'])) {
    $username = $options['u'];
}
if (isset($options['p'])) {
    $password = $options['p'];
}
if (isset($options['f'])) {
    $filename = $options['f'];
}
if (isset($options['s'])) {
    $store = $options['s'];
}
if (isset($options['r'])) {
    $resource = $options['r'];
}
if (isset($options['e'])) {
    $endpoint = $options['e'];
}

if (empty($username) || empty($password) || empty($filename) || empty($store) || empty($resource) || empty($endpoint)) {
    echo "Usage: php autoSend.php -u username -p password -f filename -s storeID -r resourceID -e endpoint\n";
    exit(1);
}

$authUrl = "https://$endpoint/store/auth/cookie";
$uploadUrl = "https://$endpoint/taskrunner/v1/distribution/replaceFile?resourceURI=https://$endpoint/store/$store/resource/$resource";


// Log file path
$logFile = "log.txt";

// File to be uploaded
$file = $filename;

// Perform authentication and save the received cookie
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $authUrl);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, "auth_username=$username&auth_password=$password&auth_maxage=86400");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, "cookies.txt");
$authResponse = curl_exec($ch);
curl_close($ch);

// Check if the authentication was successful (you can customize this based on the response)
if (strpos($authResponse, "Login successful") !== false) {
    echo "Authentication successful\n";

    // Extract the auth_token from cookies.txt
    $cookieFile = file_get_contents("cookies.txt");
    $lines = explode("\n", $cookieFile);
    $authToken = "";
    foreach ($lines as $line) {
        if (strpos($line, "auth_token") !== false) {
            $parts = explode("\t", $line);
            $authToken = end($parts);
            break;
        }
    }

    // Upload a file using the saved cookie and extracted auth_token
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $uploadUrl);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $postFields = array(
        'file' => new CURLFile($file),
    );
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
    $headers = array(
        "Cookie: auth_token=$authToken",
    );
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $uploadResponse = curl_exec($ch);
    curl_close($ch);

    // Check the upload response and handle accordingly
    if (strpos($uploadResponse, "jobId") !== false) {
        echo "File upload initiated. Checking job status...\n";

        // Extract the job ID from the response
        $uploadResponseArray = json_decode($uploadResponse, true);
        $jobId = $uploadResponseArray['jobId'];

        // Loop to check the job status
        while (true) {
            $jobStatus = json_decode(file_get_contents("https://$endpoint/taskrunner/v1/job/$jobId"), true)['status'];
            if ($jobStatus === "Success") {
                echo "File upload successful\n";
                // Append a success message to the log file
                file_put_contents($logFile, "File $file upload successful at " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
                break;
            } elseif ($jobStatus === "Failed") {
                echo "File upload failed\n";
                file_put_contents($logFile, "File $file failed to upload at " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
                break;
            } else {
                echo "Job status: $jobStatus. Waiting...\n";
                sleep(5); // Adjust the interval as needed
            }
        }
    } else {
        echo "File upload failed\n";
    }
} else {
    echo "Authentication failed\n";
}
?>
