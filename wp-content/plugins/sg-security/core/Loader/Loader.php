<?php
namespace SG_Security\Loader;

use SG_Security;
use SG_Security\Options_Service\Options_Service;


/**
 * Loader functions and main initialization class.
 */
class Loader {

	/**
	 * Dependencies.
	 *
	 * @var array
	 */
	public $dependencies = array(
		'admin',
		'helper',
		'usernames_service',
		'feed_service',
		'wp_version_service',
		'editors_service',
		'password_service',
		'sg_2fa',
		'login_service',
		'activity_log',
		'rest',
		'i18n',
		'block_service',
		'install_service',
		'cli',
		'custom_login_url',
	);

	/**
	 * Create a new helper.
	 */
	public function __construct() {
		$this->load_dependencies();
		$this->add_hooks();
	}

	/**
	 * Load the main plugin dependencies.
	 *
	 * @since  1.0.0
	 */
	public function load_dependencies() {
		foreach ( $this->dependencies as $dependency ) {
			$this->factory( $dependency );
		}
	}

	/**
	 * Create a new dependency.
	 *
	 * @since 1.0.0
	 *
	 * @param string $dependency The type of the dependency.
	 *
	 * @throws \Exception Exception If the type is not supported.
	 */
	public function factory( $dependency ) {
		$type = str_replace( ' ', '_', ucwords( str_replace( '_', ' ', $dependency ) ) );

		$class = 'SG_Security\\' . $type . '\\' . $type;

		if ( ! class_exists( $class ) ) {
			throw new \Exception( 'Unknown dependency type "' . $type . '".' );
		}

		$this->$dependency = new $class();
	}

	/**
	 * Add the hooks that the plugin will use to do the magic.
	 *
	 * @since  1.0.0
	 */
	public function add_hooks() {
		foreach ( $this->dependencies as $type ) {
			call_user_func( array( $this, 'add_' . $type . '_hooks' ) );
		}
	}

	/**
	 * Add install service hooks.
	 *
	 * @since 1.0.1
	 */
	public function add_install_service_hooks() {
		add_action( 'upgrader_process_complete', array( $this->install_service, 'install' ) );
	}

	/**
	 * Add hooks that will be called in the dashboard.
	 *
	 * @since 1.0.0
	 */
	public function add_admin_hooks() {
		add_action( 'admin_menu', array( $this->admin, 'add_plugin_pages' ) );
		add_filter( 'custom_menu_order', array( $this->admin, 'reorder_submenu_pages' ) );
		add_action( 'admin_enqueue_scripts', array( $this->admin, 'enqueue_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this->admin, 'enqueue_scripts' ) );
		add_action( 'admin_print_styles', array( $this->admin, 'admin_print_styles' ) );
		add_action( 'admin_init', array( $this->admin, 'hide_errors_and_notices' ), PHP_INT_MAX );
	}

	/**
	 * Add hooks needed for helper functions.
	 *
	 * @since 1.0.0
	 */
	public function add_helper_hooks() {
		add_action( 'init', array( $this->helper, 'hide_warnings_in_rest_api' ) );
		add_action( '_core_updated_successfully', array( $this->helper, 'set_server_ip' ) );
		add_filter( 'wp_die_handler', array( $this->helper, 'custom_wp_die_handler' ) );
	}

	/**
	 * Add rest hooks.
	 *
	 * @since 1.0.0
	 */
	public function add_rest_hooks() {
		add_action( 'rest_api_init', array( $this->rest, 'register_rest_routes' ) );
	}

	/**
	 * Add custom login url hooks.
	 *
	 * @since 1.1.0
	 */
	public function add_custom_login_url_hooks() {
		// Bail if custom URL is not set.
		if ( 'custom' !== get_option( 'sg_security_login_type', false ) ) {
			return;
		}

		add_filter( 'user_request_action_email_content', array( $this->custom_login_url, 'change_email_confirmation_url' ), 10, 2 );
		add_filter( 'site_url', array( $this->custom_login_url, 'change_site_url' ), 100, 2 );
		add_filter( 'wp_logout', array( $this->custom_login_url, 'wp_logout' ) );
		add_filter( 'network_site_url', array( $this->custom_login_url, 'change_site_url' ), 100, 2 );
		add_filter( 'wp_redirect', array( $this->custom_login_url, 'change_site_url' ) );
		add_filter( 'plugins_loaded', array( $this->custom_login_url, 'handle_request' ), 1 );
		add_filter( 'wp_new_user_notification_email', array( $this->custom_login_url, 'change_email_links' ) );
		add_action( 'update_option_users_can_register', array( $this->custom_login_url, 'handle_user_registration_change' ), 10, 2 );
		add_action( 'wp_ajax_dismiss_sg_security_notice', array( $this->custom_login_url, 'hide_notice' ) );
		add_action( 'admin_notices', array( $this->custom_login_url, 'show_notices' ) );
	}

	/**
	 * Add username hooks.
	 *
	 * @since 1.0.0
	 */
	public function add_usernames_service_hooks() {
		// Bail if the option is not enabled.
		if ( ! Options_Service::is_enabled( 'disable_usernames' ) ) {
			return;
		}

		add_action( 'illegal_user_logins', array( $this->usernames_service, 'get_illegal_usernames' ) );
	}

	/**
	 * Remove the plugin/theme editor.
	 *
	 * @since 1.0.0
	 */
	public function add_editors_service_hooks() {
		// Bail if the option is not enabled.
		if ( ! Options_Service::is_enabled( 'disable_file_edit' ) ) {
			return;
		}

		// Disable the themes/plugins editor.
		add_action( 'map_meta_cap', array( $this->editors_service, 'disable_file_edit' ), 10, 2 );
	}

	/**
	 * Remove the WordPress version meta tag and parameter.
	 *
	 * @since 1.0.0
	 */
	public function add_wp_version_service_hooks() {
		// Bail if the option is not enabled.
		if ( ! Options_Service::is_enabled( 'wp_remove_version' ) ) {
			return;
		}

		// Return empty string to the_generator, so no version is shown in the site head section and inside the RSS feed.
		add_filter( 'the_generator', '__return_empty_string' );

		// Strip any version parameter from scripts and styles.
		add_filter( 'style_loader_src', array( $this->wp_version_service, 'remove_script_and_styles_version' ), PHP_INT_MAX );
		add_filter( 'script_loader_src', array( $this->wp_version_service, 'remove_script_and_styles_version' ), PHP_INT_MAX );
	}

	/**
	 * Disable the WordPress feed.
	 *
	 * @since 1.0.0
	 */
	public function add_feed_service_hooks() {

		// Bail if the option is not enabled.
		if ( ! Options_Service::is_enabled( 'disable_feed', 0 ) ) {
			return;
		}

		add_action( 'do_feed', array( $this->feed_service, 'disable_feed' ), 1 );
		add_action( 'do_feed_rdf', array( $this->feed_service, 'disable_feed' ), 1 );
		add_action( 'do_feed_rss', array( $this->feed_service, 'disable_feed' ), 1 );
		add_action( 'do_feed_rss2', array( $this->feed_service, 'disable_feed' ), 1 );
		add_action( 'do_feed_atom', array( $this->feed_service, 'disable_feed' ), 1 );
		add_action( 'do_feed_rss2_comments', array( $this->feed_service, 'disable_feed' ), 1 );
		add_action( 'do_feed_atom_comments', array( $this->feed_service, 'disable_feed' ), 1 );

		remove_action( 'wp_head', 'feed_links_extra', 3 );
		remove_action( 'wp_head', 'feed_links', 2 );
	}

	/**
	 * Reset password hooks.
	 *
	 * @since 1.0.0
	 */
	public function add_password_service_hooks() {
		add_action( 'wp_login', array( $this->password_service, 'force_password_reset' ), 2 );
		add_action( 'resetpass_form', array( $this->password_service, 'hidden_login_field' ) );
		add_action( 'validate_password_reset', array( $this->password_service, 'validate_password' ), 10, 2 );
		add_action( 'password_reset', array( $this->password_service, 'remove_password_reset_meta' ) );
		add_action( 'login_message', array( $this->password_service, 'add_custom_login_message' ) );
	}

	/**
	 * Add two-factor auth hooks.
	 *
	 * @since 1.0.0
	 */
	public function add_sg_2fa_hooks() {
		add_action( 'update_option_sg_security_sg2fa', array( $this->sg_2fa, 'handle_option_change' ), 10, 2 );
		add_action( 'add_option_sg_security_sg2fa', array( $this->sg_2fa, 'handle_option_change' ), 10, 2 );

		// Bail if the option is not enabled.
		if ( ! Options_Service::is_enabled( 'sg2fa' ) ) {
			return;
		}

		add_action( 'wp_login', array( $this->sg_2fa, 'init_2fa' ), 3, 2 );
		add_action( 'login_form_sgs2fa', array( $this->sg_2fa, 'validate_2fa_login' ) );
		add_action( 'login_form_sgs2fabc', array( $this->sg_2fa, 'validate_2fabc_login' ) );
		add_action( 'login_form_load_sgs2fabc', array( $this->sg_2fa, 'load_backup_codes_form' ) );
		add_action( 'user_register', array( $this->sg_2fa, 'enable_2fa_for_user' ), 1 );
		add_action( 'show_user_profile', array( $this->sg_2fa, 'show_profile_security' ), 10, 1 );
		add_action( 'edit_user_profile', array( $this->sg_2fa, 'show_profile_security' ), 10, 1 );
		add_action( 'admin_notices', array( $this->sg_2fa, 'show_backup_codes_notice' ) );
		add_action( 'admin_print_scripts-profile.php', array( $this->sg_2fa, 'dismiss_backup_codes_notice' ) );
	}

	/**
	 * Add login service hooks
	 *
	 * @since 1.0.0
	 */
	public function add_login_service_hooks() {
		add_action( 'login_head', array( $this->login_service, 'restrict_login_to_ips' ), PHP_INT_MAX - 1 );

		// Bail if optimization is disabled.
		if ( 0 === intval( get_option( 'sg_security_login_attempts', 0 ) ) ) {
			return;
		}

		// Check the login attempts for an IP and block the access to the login page.
		add_action( 'login_head', array( $this->login_service, 'maybe_block_login_access' ), PHP_INT_MAX );
		// Add login attempts for ip.
		add_filter( 'login_errors', array( $this->login_service, 'log_login_attempt' ) );
		// Reset login attempts for an ip on successful login.
		add_filter( 'wp_login', array( $this->login_service, 'reset_login_attempts' ) );
	}

	/**
	 * Add data to the activity log for unknown user.
	 *
	 * @since 1.0.0
	 */
	public function add_activity_log_hooks() {
		// Set the cron job for deleting the old logs.
		add_action( 'init', array( $this->activity_log, 'set_sgs_logs_cron' ) );
		// Delete old logs if cron is disabled.
		add_action( 'admin_init', array( $this->activity_log, 'delete_logs_on_admin_page' ) );
		// Run the cron daily to check for expired logs and delete them.
		add_action( 'siteground_security_clear_logs_cron', array( $this->activity_log, 'delete_old_activity_logs' ) );

		// Attachments.
		add_action( 'add_attachment', array( $this->activity_log->attachments, 'log_add_attachment' ) );
		add_action( 'edit_attachment', array( $this->activity_log->attachments, 'log_edit_attachment' ) );
		add_action( 'delete_attachment', array( $this->activity_log->attachments, 'log_delete_attachment' ) );

		// Comments.
		add_action( 'wp_insert_comment', array( $this->activity_log->comments, 'log_comment_insert' ) );
		add_action( 'edit_comment', array( $this->activity_log->comments, 'log_comment_edit' ) );
		add_action( 'delete_comment', array( $this->activity_log->comments, 'log_comment_delete' ) );
		add_action( 'spam_comment', array( $this->activity_log->comments, 'log_comment_spam' ) );
		add_action( 'unspam_comment', array( $this->activity_log->comments, 'log_comment_unspam' ) );
		add_action( 'trash_comment', array( $this->activity_log->comments, 'log_comment_trash' ) );
		add_action( 'untrash_comment', array( $this->activity_log->comments, 'log_comment_untrash' ) );
		// RESEARCH IF WE NEED THIS, SINCE WE HAVE ALL TRANSITIONS ABOVE
		add_action( 'transition_comment_status', array( $this->activity_log->comments, 'log_comment_status_transition' ), 10, 3 );

		// Core.
		add_action( '_core_updated_successfully', array( $this->activity_log->core, 'log_core_update' ) );

		// Export.
		add_action( 'export_wp', array( $this->activity_log->export, 'log_export' ) );

		// Options.
		add_action( 'updated_option', array( $this->activity_log->options, 'log_option_update' ), 10, 3 );

		// Plugins.
		add_action( 'activated_plugin', array( $this->activity_log->plugins, 'log_plugin_activate' ) );
		add_action( 'deactivated_plugin', array( $this->activity_log->plugins, 'log_plugin_deactivate' ) );
		add_filter( 'update_option_recently_edited', array( $this->activity_log->plugins, 'log_plugin_edit' ), 10, 2 );
		add_action( 'upgrader_process_complete', array( $this->activity_log->plugins, 'log_plugin_update' ), 10, 2 );
		add_action( 'upgrader_process_complete', array( $this->activity_log->plugins, 'log_plugin_install' ), 10, 2 );

		// Post.
		add_action( 'delete_post', array( $this->activity_log->posts, 'log_post_delete' ), 10, 2 );
		add_action( 'transition_post_status', array( $this->activity_log->posts, 'log_post_status_transition' ), 10, 3 );

		// Taxonomy.
		add_action( 'created_term', array( $this->activity_log->taxonomies, 'log_term_create' ), 10, 3 );
		add_action( 'edited_term', array( $this->activity_log->taxonomies, 'log_term_edit' ), 10, 3 );
		add_action( 'delete_term', array( $this->activity_log->taxonomies, 'log_term_delete' ), 10, 4 );

		// Theme.
		add_action( 'switch_theme', array( $this->activity_log->themes, 'log_theme_switch' ), 10, 2 );
		add_filter( 'update_option_recently_edited', array( $this->activity_log->themes, 'log_theme_edit' ), 10, 2 );
		add_action( 'upgrader_process_complete', array( $this->activity_log->themes, 'log_theme_update' ), 10, 2 );
		add_action( 'upgrader_process_complete', array( $this->activity_log->themes, 'log_theme_install' ), 10, 2 );
		add_action( 'delete_site_transient_update_themes', array( $this->activity_log->themes, 'log_theme_delete' ) );
		add_action( 'customize_save', array( $this->activity_log->themes, 'log_theme_customizer_edit' ) );

		// User.
		add_action( 'wp_login', array( $this->activity_log->users, 'log_login' ), 1, 2 );
		add_action( 'wp_logout', array( $this->activity_log->users, 'log_logout' ) );
		add_action( 'delete_user', array( $this->activity_log->users, 'log_user_delete' ) );
		add_action( 'user_register', array( $this->activity_log->users, 'log_user_register' ) );
		add_action( 'profile_update', array( $this->activity_log->users, 'log_profile_update' ) );

		// Widgets.
		add_filter( 'widget_update_callback', array( $this->activity_log->widgets, 'log_widget_update' ), 9999, 4 );
		add_filter( 'sidebar_admin_setup', array( $this->activity_log->widgets, 'log_widget_delete' ) );

		// Register unknow activity.
		register_shutdown_function( array( $this->activity_log->unknown, 'log_visit' ) );
	}

	/**
	 * Add i18n hooks.
	 *
	 * @since 1.0.0
	 */
	public function add_i18n_hooks() {
		// Load the plugin textdomain.
		add_action( 'after_setup_theme', array( $this->i18n, 'load_textdomain' ), 9999 );
		// Generate JSON translations.
		add_action( 'upgrader_process_complete', array( $this->i18n, 'update_json_translations' ), 10, 2 );
	}

	/**
	 * Manage blocked users.
	 *
	 * @since 1.0.0.
	 */
	public function add_block_service_hooks() {
		// Block user by ip.
		add_action( 'init', array( $this->block_service, 'block_user_by_ip' ) );
	}

	/**
	 * WP CLI functionality added.
	 *
	 * @since 1.0.2.
	 */
	public function add_cli_hooks() {
		// If we’re in `WP_CLI` load the related files.
		if ( class_exists( 'WP_CLI' ) ) {
			add_action( 'init', array( $this->cli, 'register_commands' ) );
		}
	}
}
