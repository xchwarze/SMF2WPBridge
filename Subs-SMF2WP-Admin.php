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

function smf2wp_admin_areas(&$admin_location) {
	global $txt;
	loadLanguage('SMF2WPBridge');
	$admin_location['config']['areas']['modsettings']['subsections']['smf2wp'] = array($txt['smf2wp_admin']);
}

function smf2wp_modify_modifications(&$subActions) {
	global $context;
	$subActions['smf2wp'] = 'smf2wp_config';
	$context[ $context['admin_menu_name'] ]['tab_data']['tabs']['smf2wp'] = array();
}

function smf2wp_config($return_config = false) {
	global $txt, $scripturl, $context, $modSettings;

	$config_vars = array(
		array('text', 'smf2wp_wp_path'),
	);
	
	if (empty($modSettings['smf2wp_wp_path']) || 
		!file_exists($modSettings['smf2wp_wp_path'] . 'wp-config.php'))
		$config_vars[] = '<span style="color:#DF013A">' . $txt['smf2wp_error'] . '<span>';
		
	if ($return_config)
		return $config_vars;
		
	$context['post_url'] = $scripturl . '?action=admin;area=modsettings;save;sa=smf2wp';
	$context['settings_title'] = $txt['smf2wp_admin'];
		
	if (isset($_GET['save'])) {
		if (substr($_POST['smf2wp_wp_path'], -1) != '/')
			$_POST['smf2wp_wp_path'] .= '/';
		
		checkSession();
		saveDBSettings($config_vars);
		redirectexit('action=admin;area=modsettings;sa=smf2wp');
	}
	
	prepareDBSettingContext($config_vars);
}
?>