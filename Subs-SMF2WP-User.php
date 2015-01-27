<?php
/*
Plugin Name: SMF2WPBridge
Plugin URI: https://github.com/xchwarze/SMF2WPBridge
Description: Login bridge for use WP with SMF.
Author: DSR!
Version: 1.0
Author URI: https://github.com/xchwarze
License: GPL2 or later.
*/

function smf2wp_integrate_login($memberName, $hash_password, $cookieTime){
	global $modSettings, $wpdb, $smcFunc;
	if (empty($modSettings['smf2wp_wp_path']) || !file_exists($modSettings['smf2wp_wp_path'] . 'wp-config.php'))
		return;
	
	require $modSettings['smf2wp_wp_path'] . 'wp-config.php';

	$user = $wpdb->get_row("SELECT ID, user_login, user_pass FROM $wpdb->users WHERE user_login = '$memberName'");
	if ($user) {
		if ($modSettings['smf2wp_validate_wp_pass'] && !empty($_POST['passwrd'])){
			require $modSettings['smf2wp_wp_path'] . 'wp-requires/class-phpass.php';
			$wp_hasher = new PasswordHash(8, true);
			if (!$wp_hasher->CheckPassword($_POST['passwrd'], $user->user_pass))
				return;
		}
		
		wp_set_auth_cookie($user->ID);
		wp_set_current_user($user->ID, $user->user_login);
	} else if (!empty($_POST['passwrd'])) {
		$request = $smcFunc['db_query'](
			'', 
			'SELECT email_address FROM {db_prefix}members WHERE member_name = {string:member_name} LIMIT 1',
			array('member_name' => $memberName)
		);
		
		$email = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);
		if (!email_exists($email[0]))
			wp_create_user($memberName, $_POST['passwrd'], $email[0]);
	}
}

function smf2wp_integrate_logout($memberName){
	global $modSettings;
	if (empty($modSettings['smf2wp_wp_path']) || !file_exists($modSettings['smf2wp_wp_path'] . 'wp-config.php'))
		return;
		
	require $modSettings['smf2wp_wp_path'] . 'wp-config.php';

	if (username_exists($memberName))
		wp_logout();
}

function smf2wp_integrate_reset_pass($memberName, $memberName, $password){
	global $modSettings;
	if (empty($modSettings['smf2wp_wp_path']) || !file_exists($modSettings['smf2wp_wp_path'] . 'wp-config.php'))
		return;
		
	require $modSettings['smf2wp_wp_path'] . 'wp-config.php';
	
	$user = username_exists($memberName);
	if ($user)
		wp_set_password($password, $user);
}

function smf2wp_integrate_register($regOptions, $theme_vars){
	global $modSettings;
	if (empty($modSettings['smf2wp_wp_path']) || !file_exists($modSettings['smf2wp_wp_path'] . 'wp-config.php'))
		return;
	
	//TODO: openid support!?
	if ($regOptions['openid'] !== '')
		return;
	
	require $modSettings['smf2wp_wp_path'] . 'wp-config.php';
	
	if (!(username_exists($regOptions['username']) || email_exists($regOptions['email']))) {
		$user_id = wp_create_user($regOptions['username'], $regOptions['password'], $regOptions['email']);

		if (is_int($user_id) && ($regOptions['require'] == 'nothing')) {
			wp_set_auth_cookie($user_id);
			wp_set_current_user($user_id, $regOptions['username']);
		}
	}
}

?>