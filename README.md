# Pretty Index

## A simple yet useful index page for developers

### Features
* Displays important server related information
	- Displays public IP, LAN IP, host IP and remote IP in one place
	- Displays important server and PHP configurations, such as PHP version, loaded configuration, timezone, phpinfo()
* Web search
* Directory listing
* Evaluate PHP code
* Custom bookmarks
  - This requires pdo-sqlite and write permission on script's directory.
  - Alternatively, if you do not want to use sqlite, then a writable json file named *pretty_index.json* in the script's directory will do. This json file is automatically created if the server has write permission on the directory.

**Note: This index.php is ONLY for development purpose. DO NOT put this in your production server.**
