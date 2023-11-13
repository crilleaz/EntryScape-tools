# EntryScape-tools

This repository contains a collection of scripts designed to interact with EntryScape.

We use cookies for user authentication. You can find the cookies used [here](https://swagger.entryscape.com/#/auth/loginCookie). These scripts create two necessary files: "cookies.txt" and "log.txt." Ensure that the respective permissions are set to allow the creation of these files.

## Bash Scripts

### autoSend.sh

- **Usage:** `./autoSend.sh -u username -p password -f file -r resourceID -s storeID -e endpoint`
- Example: `./autoSend.sh -u myusername -p mypassword -f myfile.json -r 123 -s 456 -e eskilstuna.entryscape.net`
- Optional: To enable MySQL interaction for saving logs to your database, edit lines 15 to 18 in the script.

### sendInput.sh

- **Usage:** `./sendInput.sh`
- Follow the instructions provided in your terminal.

## Python Scripts

### autoSend.py

- **Usage:** `python autoSend.py -u username -p password -f file -r resourceID -s storeID -e endpoint`
- Example: `python autoSend.py -u myusername -p mypassword -f myfile.json -r 123 -s 456 -e eskilstuna.entryscape.net`
- Ensure you review the import statements to identify and install any required modules.

### sendInput.py

- **Usage:** `python sendInput.py`
- Follow the instructions provided in your terminal.

## PHP Scripts

### sendInput.php

- Change line 11: `$endpointUrl = "YOUR_ENDPOINT";` (e.g., `$endpointUrl = "eskilstuna.entryscape.net";`)
- Upload `sendInput.php` to your webserver and visit `URL/sendInput.php`, then follow the on-screen instructions.

### autoSend.php

- **Usage:** `php autoSend.php -u username -p password -f filename -s storeID -r resourceID -e endpoint`
- Example: `php autoSend.php -u myusername -p mypassword -f myfile.json -s 456 -r 123 -e eskilstuna.entryscape.net`

## Dependencies

- [jq](https://jqlang.github.io/jq/): Used to parse JSON responses. You can install it with `apt-get install jq`.
- (OPTIONAL) [MySQL](https://www.mysql.com/): Used to store and manage data. You can set up the required MySQL database. Here's the SQL table definition for your project:

```sql
CREATE TABLE logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    file VARCHAR(255),
    store_id INT,
    resource_id INT,
    endpoint VARCHAR(255),
    status VARCHAR(20)
);
```

## More Resources

For more EntryScape-related tools and information, please visit the official EntryScape Git repository at [EntryScape Community Tools](https://github.com/entryscape/community-tools).

## Contact

If you have any questions or need further assistance, please feel free to reach out to me:

- Email: [christoffer.alvarsson3@eskilstuna.se](mailto:christoffer.alvarsson3@eskilstuna.se)
