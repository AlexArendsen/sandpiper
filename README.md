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
* **Flat**: To keep things simple, *folders are intentionally ommitted from
Sandpiper*. Instead, files are described using text **tags**.
* **Responsive**: The Sandpiper UI is built on
[MaterializeCSS](http://materializecss.com/), making it just as usable on
mobile devices as it is on desktop.

## Installation

1. Place into desired server directory.
2. Create database called `SANDPIPER` (or whatever name you'd like)
3. Modify the connection variables at the top of `init.php` to match your MySQL
server name, login credentials, and the db name you picked in step 2.
4. Run `install.sql` and `backdoor.bcrypt.sql` found within the `install/`
directory against your databse.
5. Ensure that your server user and group have read and write permissions in
the `tmp/` and `uploads/` directories
6. Run `bower install` to download the necessary dependencies.

## Credit

Sandpiper by Alex Arendsen, licensed under GPLv2.0 (see `LICENSE`).

File type icons found in `static/img/filetypes` is Teambox's
[Free-file-icons](https://github.com/teambox/Free-file-icons), licensed under
the MIT license.
