<?php
// Register Custom Post Type
function country_post_type() {

	$labels = array(
		'name'                => _x( 'Countries', 'Post Type General Name', 'post_type' ),
		'singular_name'       => _x( 'Country', 'Post Type Singular Name', 'post_type' ),
		'menu_name'           => __( 'Country', 'post_type' ),
		'parent_item_colon'   => __( 'Parent Country:', 'post_type' ),
		'all_items'           => __( 'All Countries', 'post_type' ),
		'view_item'           => __( 'View Country', 'post_type' ),
		'add_new_item'        => __( 'Add New Country', 'post_type' ),
		'add_new'             => __( 'Add Country', 'post_type' ),
		'edit_item'           => __( 'Edit Country', 'post_type' ),
		'update_item'         => __( 'Update Country', 'post_type' ),
		'search_items'        => __( 'Search Country', 'post_type' ),
		'not_found'           => __( 'Not found', 'post_type' ),
		'not_found_in_trash'  => __( 'Not found in Trash', 'post_type' ),
	);
	$args = array(
		'label'               => __( 'country', 'post_type' ),
		'description'         => __( 'Country', 'post_type' ),
		'labels'              => $labels,
		'supports'            => array( 'title', 'editor', ),
		'hierarchical'        => false,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'menu_position'       => 5,
		'menu_icon'           => '',
		'can_export'          => true,
		'has_archive'         => true,
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'capability_type'     => 'page',
	);
	register_post_type( 'country', $args );

}

// Hook into the 'init' action
add_action( 'init', 'country_post_type', 0 );


    //Add the Meta Box.
function add_custom_meta_box_countries()
{

    add_meta_box ( 'add_custom_meta_box_countries' , // $id
                   'Charts data' , // $title
                   'show_custom_meta_box_countries' , // $callback
                   'country' , // $page
                   'normal' , // $context
                   'high' ); // $priority

}

add_action ( 'add_meta_boxes' , 'add_custom_meta_box_countries' );

function show_custom_meta_box_countries($post){
    
    $code       = get_post_meta($post->ID, "_charts_value_code", true);
    $innovation = get_post_meta($post->ID, "_charts_value_innovation", true);
    $scaling    = get_post_meta($post->ID, "_charts_value_scaling", true);
    $research   = get_post_meta($post->ID, "_charts_value_research", true);
    ?>

    <table>
        <tr>
            <td>Country Code</td>
            <td><input type="text" name="_charts_value_code" value="<?php echo $code; ?>" /></td>
        </tr>
        <tr>
            <td>Innovation</td>
            <td><input type="text" name="_charts_value_innovation" value="<?php echo $innovation; ?>" /></td>
        </tr>
        <tr>
            <td>Scaling</td>
            <td><input type="text" name="_charts_value_scaling" value="<?php echo $scaling; ?>" /></td>
        </tr>
        <tr>
            <td>Research</td>
            <td><input type="text" name="_charts_value_research" value="<?php echo $research; ?>" /></td>
        </tr>
    </table>

    <?php
}

                
function save_custom_meta_box_countries($post_id){
    
    if( isset( $_POST["_charts_value_code"] ) )
        update_post_meta ($post_id, "_charts_value_code", $_POST["_charts_value_code"]);
    if( isset( $_POST["_charts_value_innovation"] ) )
        update_post_meta ($post_id, "_charts_value_innovation", $_POST["_charts_value_innovation"]);
    if( isset( $_POST["_charts_value_scaling"] ) )
        update_post_meta ($post_id, "_charts_value_scaling", $_POST["_charts_value_scaling"]);
    if( isset( $_POST["_charts_value_research"] ) )
        update_post_meta ($post_id, "_charts_value_research", $_POST["_charts_value_research"]);
    
}
add_action("save_post", "save_custom_meta_box_countries");

?>