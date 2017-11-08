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

require_once (dirname(__FILE__) . "/tequila_client.php");

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
        add_action('wp_authenticate', array( $this, 'start_authentication' ) );

    }

    function get_option($name, $default = false, $use_cache = true) {
        if ( $this->is_network_version() ) {
            return get_site_option($name, $default, $use_cache);
        } else {
            return get_option($name, $default);
        }
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
        // Use the settings API rather than writing our own <form>s and
        // validators therefor.
        // More at https://wordpress.stackexchange.com/a/100137
           $option_name   = 'plugin:epfl-tequila';

    // Fetch existing options.
    $option_values = get_option( $option_name );

    $default_values = array (
        'number' => 500,
        'color'  => 'blue',
        'long'   => ''
    );

    // Parse option values into predefined keys, throw the rest away.
    $data = shortcode_atts( $default_values, $option_values );

    register_setting(
        'plugin:epfl-tequila-optiongroup', // group, used for settings_fields()
        $option_name,  // option name, used as key in database
        't5_sae_validate_option'      // validation callback
    );

    /* No argument has any relation to the prvious register_setting(). */
    add_settings_section(
        'section_1', // ID
        'Some text fields', // Title
        't5_sae_render_section_1', // print output
        'epfl_tequila_slug' // menu slug, see action_admin_menu()
    );

    add_settings_field(
        'section_1_field_1',
        'A Number',
        't5_sae_render_section_1_field_1',
        'epfl_tequila_slug',  // menu slug, see action_admin_menu()
        'section_1',
        array (
            'label_for'   => 'label1', // makes the field name clickable,
            'name'        => 'number', // value for 'name' attribute
            'value'       => esc_attr( $data['number'] ),
            'option_name' => $option_name
        )
    );
    add_settings_field(
        'section_1_field_2',
        'Select',
        't5_sae_render_section_1_field_2',
        'epfl_tequila_slug',  // menu slug, see action_admin_menu()
        'section_1',
        array (
            'label_for'   => 'label2', // makes the field name clickable,
            'name'        => 'color', // value for 'name' attribute
            'value'       => esc_attr( $data['color'] ),
            'options'     => array (
                'blue'  => 'Blue',
                'red'   => 'Red',
                'black' => 'Black'
            ),
            'option_name' => $option_name
        )
    );

    add_settings_section(
        'section_2', // ID
        'Textarea', // Title
        't5_sae_render_section_2', // print output
        'epfl_tequila_slug' // menu slug, see action_admin_menu()
    );

    add_settings_field(
        'section_2_field_1',
        'Notes',
        't5_sae_render_section_2_field_1',
        'epfl_tequila_slug',  // menu slug, see action_admin_menu()
        'section_2',
        array (
            'label_for'   => 'label3', // makes the field name clickable,
            'name'        => 'long', // value for 'name' attribute
            'value'       => esc_textarea( $data['long'] ),
            'option_name' => $option_name
        )
    );
    }

    function eg_setting_section_info() {
        echo '<p>Intro text for our settings section</p>';
    }
    function validate_settings_cb() {
        if (false) {
            add_settings_error(
                        'plugin:epfl-tequila-optiongroup',
                        'number-too-low',
                        'Number must be between 1 and 1000.'
            );
        }
    }

    function eg_setting_info() {
        echo '<input name="eg_setting_name" id="eg_setting_name" type="checkbox" value="1" class="code" ' . checked( 1, get_option( 'eg_setting_name' ), false ) . ' /> Explanation text';
    }

    // Spit out every knob previously registered with
    // https://wordpress.stackexchange.com/questions/100023/settings-api-with-arrays-example
    function action_admin_menu() {
        add_options_page(
            __('Réglages de Tequila', 'epfl-tequila'), // $page_title,
            __('Réglages de Tequila', 'epfl-tequila'), // $menu_title,
            'manage_options',          // $capability,
            'epfl_tequila_slug',       // $menu_slug
            array($this, 'render_settings')       // Callback
        );
    }

    function render_settings() {
?>
    <div class="wrap">
        <h2><?php print $GLOBALS['title']; ?></h2>
        <form action="options.php" method="POST">
            <?php
            settings_fields( 'plugin:epfl-tequila-optiongroup' );
            do_settings_sections( 'epfl_tequila_slug' );
            submit_button();
            ?>
        </form>
    </div>
    <?php    }


    function start_authentication() {
        $client = new TequilaClient();
        $client->SetApplicationName(__('Administration WordPress — ', 'epfl-tequila') . get_bloginfo( 'name' ));
        $client->SetWantedAttributes(array('name', 'firstname', 'displayname', 'username',
                  'personaltitle',
                  'email', 'title', 'title-en',
                  'uniqueid'));
        $client->Authenticate(plugin_dir_url( __FILE__ ) . "/back-from-Tequila.php");
    }
}

function t5_sae_render_section_1()
{
    print '<p>Pick a number between 1 and 1000, and choose a color.</p>';
}
function t5_sae_render_section_1_field_1( $args )
{
    /* Creates this markup:
    /* <input name="plugin:t5_sae_option_name[number]"
     */
    printf(
        '<input name="%1$s[%2$s]" id="%3$s" value="%4$s" class="regular-text">',
        $args['option_name'],
        $args['name'],
        $args['label_for'],
        $args['value']
    );
    // t5_sae_debug_var( func_get_args(), __FUNCTION__ );
}
function t5_sae_render_section_1_field_2( $args )
{
    printf(
        '<select name="%1$s[%2$s]" id="%3$s">',
        $args['option_name'],
        $args['name'],
        $args['label_for']
    );

    foreach ( $args['options'] as $val => $title )
        printf(
            '<option value="%1$s" %2$s>%3$s</option>',
            $val,
            selected( $val, $args['value'], FALSE ),
            $title
        );

    print '</select>';

    // t5_sae_debug_var( func_get_args(), __FUNCTION__ );
}
function t5_sae_render_section_2()
{
    print '<p>Makes some notes.</p>';
}

function t5_sae_render_section_2_field_1( $args )
{
    printf(
        '<textarea name="%1$s[%2$s]" id="%3$s" rows="10" cols="30" class="code">%4$s</textarea>',
        $args['option_name'],
        $args['name'],
        $args['label_for'],
        $args['value']
    );
}

TequilaLogin::getInstance()->hook();
?>
