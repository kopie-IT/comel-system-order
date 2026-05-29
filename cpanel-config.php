<?php

// -------------------------------------------------------
// cPanel Database Configuration
//
// INSTRUCTIONS:
//   1. Place this file at the PROJECT ROOT (same level as src/, public_html/)
//      i.e. ~/cpanel-config.php on your cPanel server
//      It must NOT be inside public_html — it would be publicly accessible.
//   2. DO NOT commit this file to git (it is in .gitignore).
//
// cPanel server layout:
//   ~/                        ← cPanel home directory (project root)
//   ├── cpanel-config.php     ← THIS FILE
//   ├── src/                  ← app source (outside web root)
//   ├── database/             ← SQL dumps (outside web root)
//   └── public_html/          ← web root
// -------------------------------------------------------

define('CPANEL_DB_HOST', 'localhost');
define('CPANEL_DB_PORT', '3306');
define('CPANEL_DB_NAME', 'rc126893_comel');
define('CPANEL_DB_USER', 'rc126893_comel');
define('CPANEL_DB_PASS', 'Malaysia@2413');
