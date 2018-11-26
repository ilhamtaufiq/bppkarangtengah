<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'bppk');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'fzUIMm9xT+Bx[v#>~^9SRfy!ex,bEzU>>6z4`UX^zH`m)no-;5&<^mI%[y,xhmCO');
define('SECURE_AUTH_KEY',  '> Rs8PVMxqhgj>;5)M=dsPzn45%R6wHUj6kMS9GVV]kI35Q=*um*A5ut}W8s -B9');
define('LOGGED_IN_KEY',    'yh,f%h^})+Yv,8<1V<oT:Xf04xFy.3Sm*W0C&80%QE4cveR%o(}7HFF6ppK=T9Aj');
define('NONCE_KEY',        'w4oo7oQQ`8rf$Aq-[-Q&!L#T/oOz x4G:^FtA+$B9f~2cuOlx >AYP1*13g!o_y<');
define('AUTH_SALT',        '42:^+#Em-N%5 t4]rp,VYSWxS&l|[3l;vtwr{p,OSX@Jo`-P2dw:]^Z,Rh.BB<s0');
define('SECURE_AUTH_SALT', 'fm$4]:T?~`azEH%/9s30o#uZ~X|uU9f?mG:Zd5[MrQ7?T$NJW%ie1bFSOA@$!DUE');
define('LOGGED_IN_SALT',   'Y`VY}O{C*%YWUb[).f;UiA5pxl!*q&mt>6QuW/@(e4Y#i+Qb25>A-V~k;f #*CM!');
define('NONCE_SALT',       '<GcK$?=0]ouQkwbPFd}J,#;VrY6_&L>&N9_.?jiT[u+fCew?Tl:OX,$^a^$gk2uX');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
