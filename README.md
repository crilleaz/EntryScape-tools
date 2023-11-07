# EntryScape-tools
Compilation of scripts I made to interact with EntryScape.

We're using cookies (https://swagger.entryscape.com/#/auth/loginCookie) to authenticate the user.<br>
Files "cookie.txt" and "log.txt" are created with these scripts; each respective file needs permission to create these files.

Using jq (https://jqlang.github.io/jq/) to parse JSON responses, install via "apt-get install jq".

# How to?
autoSend.sh
- Usage ./autoSend.sh -u username -p password -f file -r resourceID -s storeID -e endpoint (e.g eskilstuna.entryscape.net)

sendInput.sh
- Usage ./sendInput.sh
- Follow the instructions on screen

sendInput.php
- Upload to your webserver and visit URL/sendInput.php, then follow instructions on screen

autoSend.php
- Usage: php autoSend.php -u username -p password -f filename -s storeID -r resourceID -e endpoint



# Contact
christoffer.alvarsson3@eskilstuna.se
