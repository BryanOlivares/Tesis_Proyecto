<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'boutique_ecuador' );

/** MySQL database username */
define( 'DB_USER', 'boutique_ecuador' );

/** MySQL database password */
define( 'DB_PASSWORD', 'dMeLKK!T9YLZ' );

/** MySQL hostname */
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
define( 'AUTH_KEY',         '{>p0bZxW,EHIB2?likYrL?7^a(iEdga2HSDah1hugkV_+v#8Dqpk?NyX)8>!u-8z' );
define( 'SECURE_AUTH_KEY',  'CXP$i&Z7UW0cjIa4RJm!Ve>|*Mg5~T&~>0DP)+_41K$lk*X](6_YDjD|r2v:%Kiz' );
define( 'LOGGED_IN_KEY',    'L}g[l-W/]k5CKC&N9s|Z~PYX]EFn!}f`gl;n7aVh]xN[-8I7w*NnI8]?xdX[@bhf' );
define( 'NONCE_KEY',        '|<:nh[SW[}X^Rxz5x$S]I`k5&:&aX%Xc:ldp5(<cM4uIX|YRbrIV*xylvhvK>;rs' );
define( 'AUTH_SALT',        'SaT!saI!oC7%RKXazm EhR $p2io<?BA[zFBX:wb`VE?N,GzVv_|;974Ok5:qH[x' );
define( 'SECURE_AUTH_SALT', 'Ho &6R1L|;zs}C.1e|1t:$EGcC<J_S>HHr.:kJy+ZUziv:3{,}7Bou5n[HZGZ@Ks' );
define( 'LOGGED_IN_SALT',   'FR8;Y[+Zq=]*lZOR58iKVuj|%+/P>F5iM2`y$HUtGJG2F]->UpW(E4s(0%SYzcto' );
define( 'NONCE_SALT',       'sx5b1<YjzaA-G,4<^Z+<<(kpZ[&ZjD}xK@84bi|Q;{JX>R4VnZ2OlXJ|oyz==iT`' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'varadero_';

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
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
