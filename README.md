# My PHP Web System

## Requirements
- PHP 7.x+
- Apache/Nginx
- MySQL/MariaDB

## Setup

1. Clone this repository or download as ZIP and extract to your server root (e.g., `/var/www/html` or `htdocs`).
2. Import the database:
   - Open phpMyAdmin
   - Create a new database (e.g., `mydb`)
   - Import `database.sql` from this repo
3. Configure the database connection:
   - Edit `config.inc.php` (or similar) and set your DB host, username, password, and database name.

4. Open your browser at `http://localhost` (or your server’s domain/IP) to view the site.

## Deploy Online

- Upload files to your hosting provider via FTP or cPanel.
- Import SQL file via phpMyAdmin.
- Set correct permissions and update your configuration with your host’s DB details.

## Security

- **Never upload your real config files (with passwords) to GitHub.** Use `config.inc.php.example` as a template.
