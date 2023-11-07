#!/bin/bash
# Written for Eskilstuna Kommun's implementation of EntryScape, this script requests an authentication cookie from EntryScape to authenticate with the API.
# The script is responsible for uploading a single file to the endpoint.
# Created by Christoffer Alvarsson

# Initialize variables with default values
USERNAME=""
PASSWORD=""
FILE=""
RESOURCE=""
LOG_FILE="log.txt"

# Parse command-line options using getopts
while getopts "u:p:f:r:e:" opt; do
    case $opt in
        u) USERNAME="$OPTARG";;
        p) PASSWORD="$OPTARG";;
        f) FILE="$OPTARG";;
        r) RESOURCE="$OPTARG";;
        e) ENDPOINT="$OPTARG";;
        \?) echo "Invalid option: -$OPTARG" >&2; exit 1;;
    esac
done

# Check if any of the required options are missing
if [ -z "$USERNAME" ] || [ -z "$PASSWORD" ] || [ -z "$FILE" ] || [ -z "$RESOURCE" ]; then
    echo "Usage: $0 -u <username> -p <password> -f <file> -r <resource_id> -e <endpoint (e.g eskilstuna.entryscape.net)>"
    exit 1
fi

# URL for authentication and file upload
AUTH_URL="https://$ENDPOINT/store/auth/cookie"
UPLOAD_URL="https://$ENDPOINT/taskrunner/v1/distribution/replaceFile?resourceURI=https://$ENDPOINT/store/1/resource/$RESOURCE"

# Perform authentication and save the received cookie
auth_response=$(curl -s -c cookies.txt -X POST "$AUTH_URL" -H "Content-Type: application/x-www-form-urlencoded" -d "auth_username=$USERNAME&auth_password=$PASSWORD&auth_maxage=86400")

# Check if the authentication was successful (you can customize this based on the response)
if [[ "$auth_response" == *"Login successful"* ]]; then
    echo "Authentication successful"

    # Extract the auth_token from cookies.txt
    auth_token=$(awk -F'\t' '$6 == "auth_token" {print $7}' cookies.txt)

    # Upload a file using the savd cookie and extracted auth_token
    upload_response=$(curl --location \
    --request POST "$UPLOAD_URL" \
    --header 'Content-Type: multipart/form-data' \
    --form "file=@$FILE" \
    --header "Cookie: auth_token=$auth_token")

    # Check the upload response and handle accordingly
    if [[ "$upload_response" == *"jobId"* ]]; then
        echo "File upload initiated. Checking job status..."

        # Extract the job ID from the response
        job_id=$(echo "$upload_response" | jq -r '.jobId')

        # Loop to check the job status
        while true; do
            job_status=$(curl -s --location "https://$ENDPOINT/taskrunner/v1/job/$job_id" | jq -r '.status')
            if [[ "$job_status" == "Success" ]]; then
                echo "File upload successfully"
                # Append a success message to the log file
                echo "File $FILE upload successfully at $(date)" >> "$LOG_FILE"
                break
            elif [[ "$job_status" == "Failed" ]]; then
                echo "File upload failed"
                echo "File $FILE failed to upload at $(date)" >> "$LOG_FILE"
                break
            else
                echo "Job status: $job_status. Waiting..."
                sleep 5  # Adjust the interval as needed
            fi
        done
    else
        echo "File upload failed"
    fi
else
    echo "Authentication failed"
fi
