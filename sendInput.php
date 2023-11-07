<?php
// Written for Eskilstuna Kommun's implementation of EntryScape, this script requests an authentication cookie from EntryScape to authenticate with the API.
// The script is responsible for uploading a single file to the endpoint.
// Created by Christoffer Alvarsson

// Set your login credentials
$username = "";
$password = "";

// Settings
$endpointUrl = "eskilstuna.entryscape.net";
$authUrl = "https://$endpointUrl/store/auth/cookie";
$logFile = "log.txt";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["uploadFile"])) {
	// URL for authentication and file upload
	$resourceId = $_POST['resourceid'];
	$uploadUrl = "https://$endpointUrl/taskrunner/v1/distribution/replaceFile?resourceURI=https://$endpointUrl/store/1/resource/$resourceId";
	
    // Get the uploaded file
    $file = $_FILES["uploadFile"];
    $fileTmpPath = $file["tmp_name"];
    $fileName = $file["name"];
    $fileType = $file["type"];
    
    // Move the uploaded file to the /uploads directory
    $uploadPath = "./uploads/" . $fileName;
    move_uploaded_file($fileTmpPath, $uploadPath);

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
        echo "Authentication successful" . '<br>';

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

        // Upload the saved file using the saved cookie and extracted auth_token
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $uploadUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $postFields = array(
            'file' => new CURLFile($uploadPath),
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
            echo "File upload initiated. Checking job status..." . '<br>';

            // Extract the job ID from the response
            $uploadResponseArray = json_decode($uploadResponse, true);
            $jobId = $uploadResponseArray['jobId'];

            // Loop to check the job status
            while (true) {
                $jobStatus = json_decode(file_get_contents("https://$endpointUrl/taskrunner/v1/job/$jobId"), true)['status'];
                if ($jobStatus === "Success") {
                    echo "File upload successful" . '<br>';
                    // Append a success message to the log file
                    file_put_contents($logFile, "File $uploadPath upload successful at " . date('Y-m-d H:i:s') . "\n", FILE_APPEND) . '<br>';
                    break;
                } elseif ($jobStatus === "Failed") {
                    echo "File upload failed" . '<br>';
                    file_put_contents($logFile, "File $uploadPath failed to upload at " . date('Y-m-d H:i:s') . "\n", FILE_APPEND). '<br>';
                    break;
                } else {
                    echo "Job status: $jobStatus. Waiting..." . '<br>';
                    sleep(5); // Adjust the interval as needed
                }
            }
        } else {
            echo "File upload failed\n";
        }
    } else {
        echo "Authentication failed\n";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>File Upload</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        form {
            background-color: #ffffff;
            border: 1px solid #ccc;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
            width: 300px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="file"], input[type="resourceid"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        input[type="submit"] {
            background-color: #0074D9;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <form method="POST" action="" enctype="multipart/form-data">
        <label for="uploadFile">Select a file:</label>
        <input type="file" name="uploadFile" id="uploadFile" accept=".jpg, .jpeg, .png" required>
        
        <label for="resourceid">Resource ID:</label>
        <input type="text" name="resourceid" id="resourceid" required>
        
        <input type="submit" value="Upload File">
    </form>
</body>
</html>

