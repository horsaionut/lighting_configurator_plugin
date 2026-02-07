<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wp_dev' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'ZJt,iU374R`,?weV`]k Bb-Ef)1X/_h9<K],]j([ByYPF%;Mz9)+D8zzv&pst@i5' );
define( 'SECURE_AUTH_KEY',  ' 7IhqlV/0MHbN3cZ7 %5[GPkSM1cgRqj-?pOg.q~qtgbI$@T1Bu^KoZdtz0Z+;5N' );
define( 'LOGGED_IN_KEY',    '1DHep<5)k=tw A5`E3]i}MyZ1D dhr]ZTB!NnY:6b`iI!yFoHj:HkAc=y@>sUkDj' );
define( 'NONCE_KEY',        'vAGOKrBckRt8Et()0)mF#3H~ukagm4WRpKG%!T<Jj?~wK@.f+W,:hlP*hJ-]A38 ' );
define( 'AUTH_SALT',        '7sR]h_Xn{5~}722QA_XP/rj:e;`pV>u~QG^E*qL;%Y,f#xwM57DhW(rAt4s;WVqG' );
define( 'SECURE_AUTH_SALT', '%kUUp^0i_9Yn$7WM0|M1;_S1gms+uXub_/8W4p&/Gt2X7PYN4?hC(s.<Wa?)$c6r' );
define( 'LOGGED_IN_SALT',   '[Sd`_Fuv2S1*m&)LIyde1B{HqCU%KR|,Uu(D=1h]uiM}(B`Fcz&lQV kx%0$7~|5' );
define( 'NONCE_SALT',       '&E:1uiDxrRxx:SqY8)Pyglx?5oQ45AR.<//bh#f E6+uyH%xJhdx{e#rtQeQ$iM*' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */

define('FS_METHOD', 'direct');

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
