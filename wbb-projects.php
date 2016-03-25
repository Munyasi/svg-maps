<?php
/**
 * The WordPress Plugin Boilerplate.
 *
 * A foundation off of which to build well-documented WordPress plugins that
 * also follow WordPress Coding Standards and PHP best practices.
 *
 * @package   WBB Projects
 * @author    
 * @license   GPL-2.0+
 * @link      
 * @copyright 2015 WBB Projects
 */
/*
@wordpress-plugin
Plugin Name:       OCSDNET PROJECTS
Plugin URI:        
Description:       It integrates the elements necessary to show a map with projects.
Version:           1.0
Author:            
Author URI:        
Text Domain:       
License:           
License URI:       
Domain Path:       
GitHub Plugin URI: 
GitHub Branch:     
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'WBB_PROJECTS_PLUGIN_DIR_PATH' , plugin_dir_path ( __FILE__ ) );
define( 'WBB_PROJECTS_ICON_URI' , plugins_url ( 'assets/images/icn.png' , __FILE__ ) );
/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/

require_once( plugin_dir_path( __FILE__ ) . 'public/wbb-projects-public.php' );

add_action('init', array( 'wbb_projects_public', 'add_project_post_type' ));

/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 *
 */
register_activation_hook( __FILE__, array( 'wbb_projects_public', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'wbb_projects_public', 'deactivate' ) );

/*
 */
add_action( 'plugins_loaded', array( 'wbb_projects_public', 'get_instance' ) );


/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

 if ( is_admin() ) {

     
	require_once( plugin_dir_path( __FILE__ ) . 'admin/wbb-projects-admin.php' );
	add_action( 'plugins_loaded', array( 'wbb_projects_admin', 'get_instance' ) );
        
        
        /*----------------------------------------------------------------------------*
        * Check for updates
        *----------------------------------------------------------------------------*/
        // Prevent loading this file directly and/or if the class is already defined
        if ( !class_exists( 'WP_GitHub_Updater' ) )
        {
            require_once ( WBB_PROJECTS_PLUGIN_DIR_PATH . 'includes/updater/updater.php' );
        }


        function wbb_projects_github_update_init(){

            if( !defined( 'WP_GITHUB_FORCE_UPDATE' ) )
            {
                define( 'WP_GITHUB_FORCE_UPDATE', true );
            }

                    if ( is_admin() ) { // note the use of is_admin() to double check that this is happening in the admin

                            $config = array(
                                    'slug' => plugin_basename( __FILE__ ),
                                    'proper_folder_name' => 'wbb-projects',
                                    'api_url' => '',
                                    'raw_url' => '',
                                    'github_url' => '',
                                    'zip_url' => '',
                                    'sslverify' => true,
                                    'requires' => '3.0',
                                    'tested' => '3.8',
                                    'readme' => 'README.md',
                                    'access_token' => '',
                            );

                            new WP_GitHub_Updater( $config );


                           // add_category_taxonomy_to_projects();


                    }

            }
        //add_action( 'init', 'wbb_projects_github_update_init' );

            

        
}
