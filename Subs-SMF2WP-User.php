<?php
/*
Plugin Name: SMF2WPBridge
Plugin URI: https://github.com/xchwarze/SMF2WPBridge
Description: Login bridge for use WP with SMF.
Author: DSR!
Version: 1.1.2
Author URI: https://github.com/xchwarze
License GNU/GPL: http://www.gnu.org/copyleft/gpl.html
*/

function smf2wp_wp_requires() {
	global $modSettings, $wp_version;
	if (empty($modSettings['smf2wp_wp_path']) ||
		!file_exists($modSettings['smf2wp_wp_path'] . 'wp-config.php'))
		return false;

	define('SHORTINIT', true); //change this if a wp upgrade breaks smf2wp functions
	require $modSettings['smf2wp_wp_path'] . 'wp-config.php';

	if (!SHORTINIT || version_compare($wp_version, '4.3', '<='))
		return true;

	//savaged from wp-settings.php
	require_once( ABSPATH . WPINC . '/l10n.php' );
	require( ABSPATH . WPINC . '/formatting.php' );
	require( ABSPATH . WPINC . '/capabilities.php' );
	require( ABSPATH . WPINC . '/class-wp-roles.php' );
	require( ABSPATH . WPINC . '/class-wp-role.php' );
	require( ABSPATH . WPINC . '/class-wp-user.php' );
	require( ABSPATH . WPINC . '/user.php' );
	require( ABSPATH . WPINC . '/session.php' );
	require( ABSPATH . WPINC . '/meta.php' );
	require( ABSPATH . WPINC . '/kses.php' );
	require( ABSPATH . WPINC . '/pluggable.php' );

	wp_plugin_directory_constants();
	wp_cookie_constants();
	//wp_functionality_constants();
	//wp_set_internal_encoding();

	/*$locale = get_locale();
	$locale_file = WP_LANG_DIR . "/$locale.php";
	if ( ( 0 === validate_file( $locale ) ) && is_readable( $locale_file ) )
		require( $locale_file );

	require_once( ABSPATH . WPINC . '/locale.php' );*/

	return true;
}

function smf2wp_login($memberName, $hash_password, $cookieTime){
	global $modSettings, $wpdb;
	if (!smf2wp_wp_requires())
		return;

	$user = $wpdb->get_row("SELECT ID, user_login, user_pass FROM $wpdb->users WHERE user_login = '$memberName'");
	if ($user) {
        /*
		if ($modSettings['smf2wp_validate_wp_pass'] && !empty($_POST['passwrd'])){
			require $modSettings['smf2wp_wp_path'] . 'wp-requires/class-phpass.php';
			$wp_hasher = new PasswordHash(8, true);
			if (!$wp_hasher->CheckPassword($_POST['passwrd'], $user->user_pass))
				return;
		}
        */
		wp_set_auth_cookie($user->ID);
		wp_set_current_user($user->ID, $user->user_login);
	} else if (!empty($_POST['passwrd'])) {
		global $smcFunc;
		$request = $smcFunc['db_query'](
			'',
			'SELECT email_address FROM {db_prefix}members WHERE member_name = {string:member_name} LIMIT 1',
			array('member_name' => $memberName)
		);

		$email = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);

		if (!email_exists($email[0])){
			$user_id = wp_create_user($memberName, $_POST['passwrd'], $email[0]);
			if (is_int($user_id)){
				wp_set_auth_cookie($user_id);
				wp_set_current_user($user_id, $memberName);
			}
		}
	}
}

function smf2wp_logout($memberName){
	if (!smf2wp_wp_requires())
		return;

	if (username_exists($memberName))
		wp_logout();
}

function smf2wp_reset_pass($memberName, $memberName2, $password){
	if (!smf2wp_wp_requires())
		return;

	$user = username_exists($memberName);
	if ($user)
		wp_set_password($password, $user);

	if ($memberName != 	$memberName2)
	{
		$user = username_exists($memberName2);
		if ($user)
			wp_set_password($password, $user);
	}

}

function smf2wp_register($regOptions, $theme_vars){
	if (!smf2wp_wp_requires())
		return;

	//TODO: openid support!?
	if ($regOptions['openid'] !== '' || username_exists($regOptions['username']) ||
		email_exists($regOptions['email']))
		return;

	$user_id = wp_create_user($regOptions['username'], $regOptions['password'], $regOptions['email']);
	if (is_int($user_id) && ($regOptions['require'] == 'nothing')) {
		wp_set_auth_cookie($user_id);
		wp_set_current_user($user_id, $regOptions['username']);
	}
}

?>