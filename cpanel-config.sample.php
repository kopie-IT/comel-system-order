<?php

// -------------------------------------------------------
// cPanel Database Configuration
//
// INSTRUCTIONS:
//   1. Copy this file and rename it to: cpanel-config.php
//   2. Place it ONE LEVEL ABOVE public_html (i.e. ~/cpanel-config.php)
//      It must NOT be inside public_html — it would be publicly accessible.
//   3. Fill in your actual cPanel database credentials below.
//   4. DO NOT commit cpanel-config.php to git (it is in .gitignore).
//
// cPanel server layout:
//   ~/                        ← cPanel home directory / project root
//   ├── cpanel-config.php     ← THIS FILE (outside web root)
//   ├── src/                  ← app source (outside web root)
//   ├── database/             ← SQL dumps (outside web root)
//   ├── index.php             ← web entry point
//   ├── .htaccess
//   ├── assets/               ← CSS, JS, images
//   └── uploads/
// -------------------------------------------------------

define('CPANEL_DB_HOST', 'localhost');
define('CPANEL_DB_PORT', '3306');
define('CPANEL_DB_NAME', 'your_cpanel_username_dbname');
define('CPANEL_DB_USER', 'your_cpanel_username_dbuser');
define('CPANEL_DB_PASS', 'your_strong_password_here');
