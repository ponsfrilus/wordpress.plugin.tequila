<?php
/*
 * Plugin Name: EPFL Tequila
 * Description: Authenticate with Tequila in the admin page
 * Version:     0.1
 * Author:      Dominique Quatravaux
 * Author URI:  mailto:dominique.quatravaux@epfl.ch
 */


if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

class TequilaLogin {
	static $instance = false;
	var $prefix = 'tequila_';

	public static function getInstance () {
		if ( !self::$instance ) {
		  self::$instance = new self;
		}
		return self::$instance;
	}

    function hook() {
        add_action('admin_menu', array($this, 'action_admin_menu') );
        add_action('admin_init', array($this, 'action_admin_init') );
    }

    function get_option($name, $default = false, $use_cache = true) {
        if ( $this->is_network_version() ) {
            return get_site_option($name, $default, $use_cache);
        } else {
            return get_option($name, $default);
        }
    }

    function get_settings_obj() {
        return $this->get_option("{$this->prefix}settings");
    }

	/**
	 * Returns whether this plugin is currently network activated
	 */
	var $_is_network_version = null;
	function is_network_version() {
		if ( $this->_is_network_version === null) {
            if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
                require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
            }

            $this->_is_network_version = (bool) is_plugin_active_for_network( plugin_basename(__FILE__) );
		}

		return $this->_is_network_version;
	}

	function action_admin_init () {
        if ($_SERVER['REQUEST_METHOD'] === "POST") {
            check_admin_referer('epfl-tequila-save-options');
            $this->admin_menu_save_acl($_POST["acl_level"]);
        }
    }

	function action_admin_menu () {
        add_options_page("EPFL Tequila settings",
                         "Tequila",
                         "manage_options",
                         "epfl-tequila",
                         array($this, 'admin_page'));
	}

	function admin_page () {
		include 'admin-page.php';
	}

    function admin_menu_save_acl($level) {
        $this->add_admin_notice("notice-success is-dismissible",
                                __("Merci pour " . $level . " au revoir", "epfl-tequila"));
        error_log("admin_menu_save_acl(" . $level . ")");
    }

	function add_admin_notice($classes, $message){
         add_action("admin_notices", function() use ($classes, $message) {
                 echo "<div class=\"notice $classes\"><p>$message.</p></div>";
         });
	}

	function saved_admin_OK(){
	    echo '<div class="updated">
	       <p>Configuration Tequila mise à jour.</p>
	    </div>';
    }

	function saved_admin_error(){
	    echo '<div class="error">
	       <p>Erreur !.</p>
	    </div>';
    }

}

TequilaLogin::getInstance()->hook();
?>
