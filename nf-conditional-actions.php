<?php if ( ! defined( 'ABSPATH' ) ) exit;
   /*
   Plugin Name: NF Conditional Actions
   Plugin URI: http://macnetic-labs.de
   Description: A plugin to add conditional actions.
   Text Domain: nf-conditional-actions
   Domain Path: /lang/
   Version: 2.0
   Author: Jens Brunnert
   Author URI: http://macnetic-labs.de
   License: GPL2
   */


function nf_conditional_actions_dependencies() {
	if ( !is_plugin_active( 'ninja-forms/ninja-forms.php' ) ) {
		add_action("admin_notices", function() {
			echo '<div class="error fade"><p>'. _e("Plugin 'NF Conditional Actions' deactivated, because it requires the Ninja Forms plugin to be installed and active", "nf-conditional-actions").'</p></div>';
		});
		deactivate_plugins( plugin_basename( __FILE__ ) );
		unset($_GET['activate']);
	}
}
add_action( 'admin_init', 'nf_conditional_actions_dependencies' );

function nf_conditional_actions_extend_setup_license() {
	if ( class_exists( 'NF_Extension_Updater' ) ) {
		new NF_Extension_Updater( 'NF Conditional Actions', '2.0', 'Jens Brunnert', __FILE__, 'option_prefix' );
	}
}
add_action( 'admin_init', 'nf_conditional_actions_extend_setup_license' );

function nf_conditional_actions_load_lang() {
	$textdomain = 'nf-conditional-actions';
	$locale = apply_filters( 'plugin_locale', get_locale(), $textdomain );

	load_textdomain( $textdomain, WP_LANG_DIR . '/nf-conditional-actions/' . $textdomain . '-' . $locale . '.mo' );

	load_plugin_textdomain( $textdomain, FALSE, dirname(plugin_basename(__FILE__)) . '/lang/' );
}
add_action( 'init', 'nf_conditional_actions_load_lang');

function nf_conditional_actions_scripts() {
	wp_register_style( 'nf-conditional-actions-style', plugins_url('css/nf-conditional-actions-no-js-style.css', __FILE__));
	wp_enqueue_style( 'nf-conditional-actions-style' );

	wp_enqueue_script( 'nf-conditional-actions-script', plugins_url('js/nf-conditional-actions-script.js', __FILE__), array( 'jquery' ), false, true );
	wp_localize_script( 'nf-conditional-actions-script', 'nf_conditional_actions', array( 'js_style_url' => plugins_url('css/nf-conditional-actions-style.css', __FILE__) ) );

}
add_action('wp_enqueue_scripts', 'nf_conditional_actions_scripts');
add_action('admin_enqueue_scripts', 'nf_conditional_actions_scripts');

function nf_conditional_actions( $types ) {
	$types['ninja-forms-conditional_action_message'] = require_once (plugin_dir_path( __FILE__ ) . "classes/notification-conditional-action-message.php");
    $types['ninja-forms-conditional_action_email'] = require_once (plugin_dir_path( __FILE__ ) . "classes/notification-conditional-action-email.php");
    return $types;
}
add_filter( 'nf_notification_types', 'nf_conditional_actions' );

add_action( 'wp_ajax_add_conditional_action_message', array('NF_Action_Conditional_Action_Message', 'nf_add_condition_action_message_callback') );
add_action( 'wp_ajax_add_conditional_action_email_message', array('NF_Action_Conditional_Action_Email', 'nf_add_condition_action_email_callback') );