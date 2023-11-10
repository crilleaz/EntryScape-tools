# EntryScape-tools
Compilation of scripts I made to interact with EntryScape.

We're using cookies (https://swagger.entryscape.com/#/auth/loginCookie) to authenticate the user.<br>
Files "cookies.txt" and "log.txt" are created with these scripts; each respective file needs permission to create these files.

Using jq (https://jqlang.github.io/jq/) to parse JSON responses, install via "apt-get install jq".

# How to?
autoSend.sh
- Usage ./autoSend.sh -u username -p password -f file -r resourceID -s storeID -e endpoint (e.g eskilstuna.entryscape.net)

sendInput.sh
- Usage ./sendInput.sh
- Follow the instructions in your terminal

sendInput.php
- Change line 11: $endpointUrl = "YOUR_ENDPOINT"; (e.g. $endpointUrl = "eskilstuna.entryscape.net";)
- Upload sendInput.php to your webserver and visit URL/sendInput.php, follow instructions on screen

autoSend.php
- Usage: php autoSend.php -u username -p password -f filename -s storeID -r resourceID -e endpoint (e.g eskilstuna.entryscape.net)

autoSend.py
- Usage: python autoSend.py -u username -p password -f file -r resourceID -s storeID -e endpoint (e.g eskilstuna.entryscape.net)
- Please review the imports to identify the required modules.

# Find more at EntryScapes official Git
https://github.com/entryscape/community-tools

# Contact
christoffer.alvarsson3@eskilstuna.se
