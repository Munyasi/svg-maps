<?php
/**
 * Plugin Name.
 *
 * @package   OCSDNET PROJECTS
 * @author    
 * @license   closed
 * @link     
 * @copyright 2015 W
 */

/**
 * Plugin class. This class should ideally be used to work with the
 * administrative side of the WordPress site.
 *
 * If you're interested in introducing public-facing
 * functionality, then refer to `class-plugin-name.php`
 *
 *
 * @package wbb_projects_admin
 * @author  
 */
class wbb_projects_admin {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		$plugin = wbb_projects_public::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();

		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Add the options page and menu item.
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

		// Add an action link pointing to the options page.
		$plugin_basename = plugin_basename( plugin_dir_path( __DIR__ ) . $this->plugin_slug . '.php' );

                //Add meta post type and meta fields
                add_action ( 'add_meta_boxes' ,  array( $this, 'wbb_projects_country_meta_fields' ), 0 );
                
                //Update post_meta
                add_action( 'wp_ajax_update_wbb_country_connection_post_meta', array( $this, 'update_wbb_country_connection_post_meta' ) );
                
                //Get initial connected countries
                add_action( 'wp_ajax_get_initial_countries', array( $this, 'get_initial_countries' ) );
                
                add_action( 'save_post', array($this, 'wbb_projects_save_meta') );
	}




	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

        
	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {

		global $menu;
		$menuExist = FALSE;
		foreach ( $menu as $item )
		{
		    if ( strtolower ( $item[ 0 ] ) == strtolower ( 'WBB Plugins' ) )
		    {
			  //$menuExist = TRUE;
		    }
		}
		if ( ! $menuExist )
		{
		    //add_menu_page ( "WBB Plugins" , "WBB Plugins" , 'manage_options' , "wbb-plugins" , array ($this ,'wbb_plugins_view') );
		}

		// Add a submenu to the custom top-level menu:
		/*add_submenu_page ( 
                            "wbb-plugins" 
                            , "WBB Projects" 
                            , "WBB Projects" 
                            , 'manage_options' 
                            , 'wbb-plugins-wbb-projects' 
                            , array ( $this , 'wbb_projects_organization_name') 
                        );
*/
	}


    function wbb_projects_country_connection_view(){
         // include ( WBB_PROJECTS_PLUGIN_DIR_PATH . 'admin/views/wbb_projects_organization_name.php' );
    }
        
        
	/**
	 * Register and enqueue admin-specific style sheet.
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_styles() {
            
                wp_enqueue_style( $this->plugin_slug .'-admin-styles', plugins_url( 'assets/css/admin.css', __FILE__ ));
                wp_enqueue_style( $this->plugin_slug .'-admin-styles', WBB_PROJECTS_PLUGIN_DIR_PATH."assets/css/jquery-jvectormap-1.2.2.css");

	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_scripts() {
            
            wp_enqueue_script( $this->plugin_slug . '-admin-jvector', plugins_url("wbb-projects/assets/js/jquery-jvectormap-1.2.2.min.js"), array( 'jquery' ));
            wp_enqueue_script( $this->plugin_slug . '-admin-jvector-world-map', plugins_url("wbb-projects/assets/js/jquery-jvectormap-world-mill-en.js"), array( 'jquery' ));
            
            wp_enqueue_script( $this->plugin_slug . '-admin-script', plugins_url( 'assets/js/admin.js', __FILE__ ), array( 'jquery' ));
	}

        /**
         * Add a meta fields to connect projects-countries
         */
        public function wbb_projects_country_meta_fields(){
            
            add_meta_box ( 'wbb-projects-organization-name' ,      // ID
                '<h3>Organization Info</h3>' ,             // Title
                array( $this, 'wbb_projects_organization_name') ,    // Function that show meta
                "project" , // Post type. 'post', 'page', 'link', or 'custom_post_type'
                'normal' , // Place to show the meta box. 'normal', 'advanced', o 'side'
                'low' // Priority 'high', 'core', 'default' o 'low'
            );
            
            add_meta_box ( 'country_connection_meta' ,      // ID
                '<h3>Country connection</h3>' ,             // Title
                array( $this, 'wbb_projects_country_connection_view') ,    // Function that show meta
                "project" , // Post type. 'post', 'page', 'link', or 'custom_post_type'
                'normal' , // Place to show the meta box. 'normal', 'advanced', o 'side'
                'high' // Priority 'high', 'core', 'default' o 'low'
            );
            
            
        }        

        
        /**
        * Show in post edit page, the meta box with map and connections.
        *
        * @global type $wpdb
        */
        public function wbb_projects_organization_name ( $post )
        {
            
            $org_name = get_post_meta( $post->ID, "_wbb_projects_organization_name", true );
            
            include ( WBB_PROJECTS_PLUGIN_DIR_PATH . 'admin/views/wbb_projects_organization_name.php' );
            
        }
        
        /**
         * Save the post_meta when the post is updated.
         * @param type $post_id
         */
        public function wbb_projects_save_meta ($post_id){
            
            if( isset( $_POST["_wbb_projects_organization_name"] ) )
            {
                update_post_meta ($post_id, "_wbb_projects_organization_name", $_POST["_wbb_projects_organization_name"] );
            }
            
        }
        
        /**
        * Show in post edit page, the meta box with map and connections.
        *
        * @global type $wpdb
        */
       /* public function wbb_projects_country_connection_view ()
        {
            
            //include ( WBB_PROJECTS_PLUGIN_DIR_PATH . 'admin/views/show_country_connection.php' );
            
        }
 */
        
        public function update_wbb_country_connection_post_meta(){
            
            if( isset( $_POST["countries"]) && isset( $_POST["post_id"]) )
            {
                
                $post_id = $_POST["post_id"];
                $countries = implode(",", $_POST["countries"]);
                
                update_post_meta($post_id, "country_connected", $countries);
                
                return true;
                
            }
            else
            {
                return false;
            }

            die();
        }
        
        
        public function get_initial_countries(){
            
            $post_id = $_POST["post_id"];
            echo get_post_meta($post_id, "country_connected", true);
            die();
            
        }
        
}
