# Sandpiper
PHP + MySQL Flat File Server and Web App

---

## What is Sandpiper?

**NOTE**: Sandpiper is still in development. Use at your own risk.

Sandpiper is a simple, flat, and responsive web app for uploading and
downloading your files. It has a minimalist web interface built with
[MaterializeCSS](http://materializecss.com/) and
[AngularJS](http://angularjs.org/) that let you quickly search through and
edit your uploads.

* **Simple**: Sandpiper strives to accomplish the goal of hosting files through
a web interface with as few frills as possible.
* **Flat**: To keep things simple, *folders are intentionally omitted from
Sandpiper*. Instead, files are described using text **tags**.
* **Responsive**: The Sandpiper UI is built on
[MaterializeCSS](http://materializecss.com/), making it just as usable on
mobile devices as it is on desktop.

## Dependencies & Installation

Sandpiper is designed for and tested on LAMP configurations. The following
dependencies are provided for installations on LAMP platforms:

* Apache >= 2.4.7 (not tested on previous versions)
	* Rewrite module (`mod_rewrite`) enabled
	* `.htaccess` permission allowed
* PHP >= 5.5
	* `mysqli` extension enabled
	* `gd` extension enabled


1. Place into desired server directory.
2. Create database called `SANDPIPER` (or whatever name you'd like)
3. Modify the connection variables at the top of `init.php` to match your MySQL
server name, login credentials, and the db name you picked in step 2.
4. Run `install.sql` and `backdoor.bcrypt.sql` found within the `install/`
directory against your databse.
5. Create the directory `uploads/de4d2e5d-b744`
6. Ensure that your server user (eg, `www-data` or `http`) and group have read
and write permissions in the `uploads/` directory and everything within it
7. Run `bower install` to download the necessary dependencies.
8. Create a scheduled job on your server to empty the `uploads/.trash`
directory on a regular interval.

Log into the web interface with the username `DEFAULT` and password
`DEFAULTPASSWORD`. The default user account may be removed and replaced with
additional accounts through the web interface.

## Credit

Sandpiper by Alex Arendsen, licensed under GPLv2.0 (see `LICENSE`).

File type icons found in `static/img/filetypes` is Teambox's
[Free-file-icons](https://github.com/teambox/Free-file-icons), licensed under
the MIT license.

CAPTCHA is provided by Claviska's
[simple-php-captcha](https://github.com/claviska/simple-php-captcha). It is
licensed under the MIT license.

Front-end hashing provided by nevins-b's
[javascript-bcrypt](https://github.com/nevins-b/javascript-bcrypt), which
includes Yves-Marie K. Rinquin's random number generator `issac.js`.