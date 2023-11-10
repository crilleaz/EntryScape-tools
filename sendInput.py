# Written for Eskilstuna Kommun's implementation of EntryScape, this script requests an authentication cookie from EntryScape to authenticate with the API.
# The script is responsible for uploading a single file to the endpoint.
# Created by Christoffer Alvarsson

import requests
import os
import json
import time

# Initialize variables
USERNAME = input("Username: ")
PASSWORD = input("Password: ")
FILE = input("File to upload: ")
RESOURCE = input("Resource ID: ")
STOREID = input("Store ID: ")
ENDPOINT = input("Endpoint (e.g., eskilstuna.entryscape.net): ")

LOG_FILE = "log.txt"

# URL for authentication and file upload
AUTH_URL = f"https://{ENDPOINT}/store/auth/cookie"
UPLOAD_URL = f"https://{ENDPOINT}/taskrunner/v1/distribution/replaceFile?resourceURI=https://{ENDPOINT}/store/{STOREID}/resource/{RESOURCE}"

# Perform authentication and save the received cookie
auth_data = {
    "auth_username": USERNAME,
    "auth_password": PASSWORD,
    "auth_maxage": 86400
}

auth_response = requests.post(AUTH_URL, data=auth_data)

# Check if the authentication was successful
if "Login successful" in auth_response.text:
    print("Authentication successful")

    # Extract the auth_token from the response cookies
    auth_token = auth_response.cookies.get("auth_token")

    # Upload a file using the saved cookie and extracted auth_token
    with open(FILE, "rb") as file:
        files = {"file": (os.path.basename(FILE), file)}
        upload_response = requests.post(UPLOAD_URL, headers={"Cookie": f"auth_token={auth_token}"}, files=files)

    # Check the upload response and handle accordingly
    if "jobId" in upload_response.text:
        print("File upload initiated. Checking job status...")

        # Extract the job ID from the response
        job_id = json.loads(upload_response.text)["jobId"]

        # Loop to check the job status
        while True:
            job_status_response = requests.get(f"https://{ENDPOINT}/taskrunner/v1/job/{job_id}")
            job_status = job_status_response.json()["status"]
            if job_status == "Success":
                print("File upload successfully")
                with open(LOG_FILE, "a") as log_file:
                    log_file.write(f"File {FILE} upload successfully at {time.strftime('%Y-%m-%d %H:%M:%S')}\n")
                break
            elif job_status == "Failed":
                print("File upload failed")
                with open(LOG_FILE, "a") as log_file:
                    log_file.write(f"File {FILE} failed to upload at {time.strftime('%Y-%m-%d %H:%M:%S')}\n")
                break
            else:
                print(f"Job status: {job_status}. Waiting...")
                time.sleep(5)
    else:
        print("File upload failed")
else:
    print("Authentication failed")
