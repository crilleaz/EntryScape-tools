#!/bin/bash
# Written for Eskilstuna Kommun's implementation of EntryScape, this script requests an authentication cookie from EntryScape to authenticate with the API.
# The script is responsible for uploading a single file to the endpoint.
# Created by Christoffer Alvarsson

# Ask for user input
echo "Enter username:"
read USERNAME
echo "Enter password:"
read -s PASSWORD
echo "File to upload? (e.g. /home/Grillplatser.json)"
read FILE
echo "Resource ID?"
read RESOURCE
echo "Store ID?"
read STOREID
echo "Endpoint?"
read ENDPOINT


# Set your login credentials
USERNAME=$USERNAME
PASSWORD=$PASSWORD
RESOURCE=$RESOURCE
FILE=$FILE
STOREID=$STOREID
ENDPOINT=$ENDPOINT

# URL for authentication and file upload
AUTH_URL="https://$ENDPOINT/store/auth/cookie"
UPLOAD_URL="https://$ENDPOINT/taskrunner/v1/distribution/replaceFile?resourceURI=https://$ENDPOINT/store/$STOREID/resource/$RESOURCE"

# Log file path
LOG_FILE="log.txt"

# Perform authentication and save the received cookie
auth_response=$(curl -s -c cookies.txt -X POST "$AUTH_URL" -H "Content-Type: application/x-www-form-urlencoded" -d "auth_username=$USERNAME&auth_password=$PASSWORD&auth_maxage=86400")

# Check if the authentication was successful (you can customize this based on the response)
if [[ "$auth_response" == *"Login successful"* ]]; then
    echo "Authentication successful"

    # Extract the auth_token from cookies.txt
    auth_token=$(awk -F'\t' '$6 == "auth_token" {print $7}' cookies.txt)

    # Upload a file using the saved cookie and extracted auth_token
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
                echo "File upload successful"
                # Append a success message to the log file
                echo "File $FILE upload successful at $(date)" >> "$LOG_FILE"
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
