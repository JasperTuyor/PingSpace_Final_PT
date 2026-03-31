# PingSpace — Setup Guide for domain (Addon / Other Domain)
---

## Step 1 — Create your MySQL Database

1. Log in to your domain **cPanel**
2. Go to **MySQL Databases**
3. Create a new database — e.g. `pingspace`
4. Create a new database user and set a strong password
5. Add that user to the database with **All Privileges**
6. Write down the database name, username, and password

---

## Step 2 — Import the SQL Schema

1. cPanel → **phpMyAdmin**
2. Click on your new database in the left panel
3. Click **Import** tab → **Choose File** → select `sql/pingspace.sql`
4. Click **Go**

You should see 7 tables created: users, posts, comments, likes, follows, messages, notifications.

---

## Step 3 — Edit config/database.php

Open `config/database.php` and fill in only the lines marked `<-- CHANGE THIS`:

```php
define('DB_NAME',   'pingspace');        // your database name
define('DB_USER',   'username');        // your database username
define('DB_PASS',   'yourpassword');         // your database password
define('SITE_URL',  'https://sample.mysite.com');  // your addon domain URL
```

> **SITE_URL** must be exactly your addon domain — no trailing slash.
> Example: `https://social.mysite.com` or `https://myotherdomain.com`

---

## Step 4 — Upload Files

Using **File Manager** or **FTP**, go to your addon domain's root folder.

For addon domains the root is usually at:
```
/home/username/addondomain.com/
```
or sometimes created as a subfolder of public_html:
```
/home/username/public_html/addondomain.com/
```
Either way — **upload ALL files from inside the `socialapp/` folder** directly into that root.

After uploading it should look like this:
```
(addon domain root)/
├── index.php          ← front controller
├── assets/
│   ├── css/app.css
│   ├── js/app.js
│   └── images/uploads/
│       ├── avatars/
│       ├── covers/
│       └── posts/
├── app/               ← protected — browser cannot access
├── config/            ← protected — browser cannot access
└── sql/               ← protected — browser cannot access
```

---

## Step 5 — Set Upload Folder Permissions

In cPanel **File Manager** (or via FTP), set these three folders to **755**:

- `assets/images/uploads/avatars/`
- `assets/images/uploads/covers/`
- `assets/images/uploads/posts/`

In cPanel File Manager: right-click the folder → **Change Permissions** → check all boxes except "world write" → set to 755.

---

## Step 6 — Visit Your Site

Open your addon domain in a browser — e.g. `https://example.mysite.com`

You will be redirected to the login page. Register your first account and you're live!


