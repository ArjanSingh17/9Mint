-- Create local database + app user for 9Mint (run as root in MySQL Workbench)

CREATE DATABASE 9mint
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

CREATE USER 'mint'@'localhost' IDENTIFIED BY 'devpass';

GRANT ALL PRIVILEGES ON 9mint.* TO 'mint'@'localhost';
FLUSH PRIVILEGES;
