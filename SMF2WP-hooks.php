<?php
/*
Plugin Name: SMF2WPBridge
Plugin URI: https://github.com/xchwarze/SMF2WPBridge
Description: Login bridge for use WP with SMF.
Author: DSR!
Version: 1.1.1
Author URI: https://github.com/xchwarze
License GNU/GPL: http://www.gnu.org/copyleft/gpl.html
*/

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');
else if (!defined('SMF'))
	exit('<strong>Error:</strong> Cannot install - please make sure you are calling it from root of your SMF installation.');

$hooks = array(
	'integrate_admin_include' => '$sourcedir/Subs-SMF2WP-Admin.php',
	'integrate_admin_areas' => 'smf2wp_admin_areas',
	'integrate_modify_modifications' => 'smf2wp_modify_modifications',
	'integrate_pre_include' => '$sourcedir/Subs-SMF2WP-User.php',
	//'integrate_verify_user' => 'smf2wp_verify_user',
	//'integrate_validate_login' => 'smf2wp_validate_login',
	'integrate_login' => 'smf2wp_login',
	'integrate_logout' => 'smf2wp_logout',
	'integrate_reset_pass' => 'smf2wp_reset_pass',
	'integrate_register' => 'smf2wp_register',
	//'integrate_change_member_data' => 'smf2wp_change_member_data',
);

if (SMF == 'SSI' && (!isset($_GET['action']) || (isset($_GET['action']) && !in_array($_GET['action'], array('install', 'uninstall')))))
	echo '
		Want to ....<br />
		<a href="' . $boardurl . '/hooks.php?action=install">Install</a><br />
		<a href="' . $boardurl . '/hooks.php?action=uninstall">Uninstall</a>';
else {
	$context['uninstalling'] = isset($context['uninstalling']) ? $context['uninstalling'] : (isset($_GET['action']) && $_GET['action'] == 'uninstall' ? true : false);
	$integration_function = empty($context['uninstalling']) ? 'add_integration_function' : 'remove_integration_function';
	foreach ($hooks as $hook => $function)
		$integration_function($hook, $function);

	if (SMF == 'SSI')
		echo 'Operation Successful!';
}
?>