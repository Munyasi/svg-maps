<?php
/**
 * WBB Projects.
 *
 * @package   wbb-projects-master
 * @author    Webberty <support@webberty.com>
 * @license   GPL-2.0+
 * @link      http://webberty.com
 * @copyright 2014 Webberty
 */

/**
 * Plugin class. This class should ideally be used to work with the
 * public-facing side of the WordPress site.
 *
 * @package   wbb-projects-master
 * @author    Webberty <support@webberty.com>
 */
class wbb_projects_public
{

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	const VERSION = '1.0.0';

	/**
	 * Unique identifier for your plugin.
	 *
	 * The variable name is used as the text domain when internationalizing strings
	 * of text. Its value should match the Text Domain file header in the main
	 * plugin file.
	 *
	 * @since    1.0.0
	 * @var      string
	 */
	protected $plugin_slug = 'wbb-projects-master';

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = NULL;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     1.0.0
	 */
	private function __construct ()
	{

		// Load plugin text domain
		add_action ( 'init' , array (
			$this ,
			'load_plugin_textdomain'
		) );

		// Activate plugin when new blog is added
		add_action ( 'wpmu_new_blog' , array (
			$this ,
			'activate_new_site'
		) );

		add_action ( 'wp_enqueue_scripts' , array (
			$this ,
			'enqueue_styles'
		) );
		add_action ( 'wp_enqueue_scripts' , array (
			$this ,
			'enqueue_scripts'
		) );

		//Add taxonomies to front-end
		add_action ( 'init' , array (
			$this ,
			'add_tax'
		) , 0 );

		add_shortcode ( 'show_projects_main_view' , array (
			$this ,
			'show_projects_main_view'
		) );

		add_action ( 'wp_ajax_get_initial_countries_front_end' , array (
			$this ,
			'get_initial_countries_front_end'
		) );
		add_action ( 'wp_ajax_nopriv_get_initial_countries_front_end' , array (
			$this ,
			'get_initial_countries_front_end'
		) );


		add_action ( 'wp_ajax_getProjectCategories' ,array (
			$this ,'getProjectCategories'
		));

		add_action ( 'wp_ajax_nopriv_getProjectCategories' , array (
			$this ,
			'getProjectCategories'
		) );

		add_action ( 'wp_ajax_get_initial_categories' ,array (
			$this ,'get_initial_categories'
		));

		add_action ( 'wp_ajax_nopriv_get_initial_data' , array (
			$this ,
			'get_initial_data'
		) );

		add_action ( 'wp_ajax_get_initial_data' ,array (
			$this ,'get_initial_data'
		));
		/*add_action('wbb_projects_public', array (
			$this,
			'get_initial_categories'
			));*/
		//Show result, filtered by map/categories
		add_action ( 'wp_ajax_show_project_query' , array (
			$this ,
			'show_project_query'
		) );
		add_action ( 'wp_ajax_nopriv_show_project_query' , array (
			$this ,
			'show_project_query'
		) );

		add_shortcode ( 'wbb_projects_front_end_list' , array (
			$this ,
			'wbb_projects_front_end_list'
		) );

		add_action ( "wbb_get_country_name" , array (
			$this ,
			"wbb_get_country_name"
		) , 10 , 1 );
                

                //Show total of each kind of applications in country, when you mouseover
		add_action ( 'wp_ajax_get_filters_amount_mouseover' , array (
			$this ,
			'get_filters_amount_mouseover'
		) );
		add_action ( 'wp_ajax_get_filters_amount_mouseover' , array (
			$this ,
			'get_filters_amount_mouseover'
		) );

		add_action ( 'getCountryCode' , array (
			$this ,
			'getCountryCode'
		) );
                
                
	}

	public static function get_related_by_country ( $post_id )
	{

		//Get country
		$code = get_post_meta ( get_the_ID () , "country_connected" , TRUE );

		$args = array (
			'post_type'      => 'project' ,
			'paged'          => 1 ,
			'posts_per_page' => - 1 ,
			'post__not_in'   => array ( $post_id )
		);

		$args[ "meta_query" ] = array (
			'relation' => 'OR'
		);


		$countries = explode ( "," , $code );

		foreach ( $countries as $country )
		{

			array_push ( $args[ "meta_query" ] , array (
					'key'     => 'country_connected' ,
					'value'   => $country ,
					'compare' => 'LIKE' ,
					'type'    => 'CHAR' ,
				)

			);

		}


		// Search by country
		$query = new WP_Query( $args );

		// The Loop
		if ( $query->have_posts () )
		{

			$project_in = self::wbb_get_country_name ( $code );

			$result_items = array ();

			while ( $query->have_posts () )
			{
				$query->the_post ();

				$item = "<a href='" . get_permalink () . "'>" . get_the_title () . "</a>";
				array_push ( $result_items , $item );

			}


			$template = self::wbb_project_load_template ( "single-view/loop" , "results" );
			include ( $template );

		}

		// Restore original Post Data
		wp_reset_postdata ();

	}


	public static function get_related_by_topic ( $post_id )
	{

		//Get topic

		$category = get_the_terms ( $post_id , "project_category" );

		$args = array (
			'post_type'      => 'project' ,
			'paged'          => 1 ,
			'posts_per_page' => - 1 ,
			'post__not_in'   => array ( $post_id )
		);

		$args[ "tax_query" ] = array ();

		foreach ( $category as $cat )
		{

			array_push ( $args[ "tax_query" ] , array (
					'taxonomy' => 'project_category' ,
					'field'    => 'slug' ,
					'terms'    => $cat->slug
				)

			);

			$cat_name = $cat->slug;
		}

		//Search by topic
		$query = new WP_Query( $args );

		// The Loop
		if ( $query->have_posts () )
		{

			$project_in = $cat_name;

			$result_items = array ();

			while ( $query->have_posts () )
			{
				$query->the_post ();

				$item = "<a href='" . get_permalink () . "'>" . get_the_title () . "</a>";
				array_push ( $result_items , $item );

			}


			$template = self::wbb_project_load_template ( "single-view/loop" , "results" );
			include ( $template );

		}

		// Restore original Post Data
		wp_reset_postdata ();

	}

	/**
	 * Return the plugin slug.
	 *
	 * @since    1.0.0
	 *
	 * @return    Plugin slug variable.
	 */
	public function get_plugin_slug ()
	{
		return $this->plugin_slug;
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance ()
	{

		// If the single instance hasn't been set, set it now.
		if ( NULL == self::$instance )
		{
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean $network_wide       True if WPMU superadmin uses
	 *                                       "Network Activate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       activated on an individual blog.
	 */
	public static function activate ( $network_wide )
	{

		if ( function_exists ( 'is_multisite' ) && is_multisite () )
		{

			if ( $network_wide )
			{

				// Get all blog ids
				$blog_ids = self::get_blog_ids ();

				foreach ( $blog_ids as $blog_id )
				{

					switch_to_blog ( $blog_id );
					self::single_activate ();
				}

				restore_current_blog ();

			}
			else
			{
				self::single_activate ();
			}

		}
		else
		{
			self::single_activate ();
		}

	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean $network_wide       True if WPMU superadmin uses
	 *                                       "Network Deactivate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       deactivated on an individual blog.
	 */
	public static function deactivate ( $network_wide )
	{

		if ( function_exists ( 'is_multisite' ) && is_multisite () )
		{

			if ( $network_wide )
			{

				// Get all blog ids
				$blog_ids = self::get_blog_ids ();

				foreach ( $blog_ids as $blog_id )
				{

					switch_to_blog ( $blog_id );
					self::single_deactivate ();

				}

				restore_current_blog ();

			}
			else
			{
				self::single_deactivate ();
			}

		}
		else
		{
			self::single_deactivate ();
		}

	}

	/**
	 * Fired when a new site is activated with a WPMU environment.
	 *
	 * @since    1.0.0
	 *
	 * @param    int $blog_id ID of the new blog.
	 */
	public function activate_new_site ( $blog_id )
	{

		if ( 1 !== did_action ( 'wpmu_new_blog' ) )
		{
			return;
		}

		switch_to_blog ( $blog_id );
		self::single_activate ();
		restore_current_blog ();

	}

	/**
	 * Get all blog ids of blogs in the current network that are:
	 * - not archived
	 * - not spam
	 * - not deleted
	 *
	 * @since    1.0.0
	 *
	 * @return   array|false    The blog ids, false if no matches.
	 */
	private static function get_blog_ids ()
	{

		global $wpdb;

		// get an array of blog ids
		$sql = "SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0'
			AND deleted = '0'";

		return $wpdb->get_col ( $sql );

	}

	/**
	 * Fired for each blog when the plugin is activated.
	 *
	 * @since    1.0.0
	 */
	private static function single_activate ()
	{
		// @TODO: Define activation functionality here
	}

	/**
	 * Fired for each blog when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 */
	private static function single_deactivate ()
	{
		// @TODO: Define deactivation functionality here
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain ()
	{

		$domain = $this->plugin_slug;
		$locale = apply_filters ( 'plugin_locale' , get_locale () , $domain );

		load_textdomain ( $domain , trailingslashit ( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain ( $domain , FALSE , basename ( plugin_dir_path ( dirname ( __FILE__ ) ) ) . '/languages/' );

	}

	/**
	 * Register and enqueue public-facing style sheet.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles ()
	{
		wp_enqueue_style ( $this->plugin_slug . '-public' , plugins_url ( 'wbb-projects-master/public/assets/css/public.css' ) , array () , self::VERSION );
		wp_enqueue_style ( $this->plugin_slug . '-jvector-css' , plugins_url ( 'wbb-projects-master/assets/css/jquery-jvectormap-1.2.2.css' ) , array () , self::VERSION );
	}

	/**
	 * Register and enqueues public-facing JavaScript files.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts ()
	{

		wp_enqueue_script ( $this->plugin_slug . '-jvector' , plugins_url ( 'wbb-projects-master/assets/js/jquery-jvectormap-1.2.2.min.js' ) , array ( 'jquery' ) );

		wp_enqueue_script ( $this->plugin_slug . '-jvector_worldmap' , plugins_url ( 'wbb-projects-master/assets/js/jquery-jvectormap-world-mill-en.js' ) , array ( 'jquery' ) );

	}

	public static function enqueue_projects_scripts ()
	{
		// embed the javascript file that makes the AJAX request
		//wp_enqueue_script( $this->plugin_slug . '-public', plugins_url( 'wbb-projects-master/public/assets/js/public.js'), array( 'jquery' ) );
		wp_enqueue_script ( 'x-public' , plugins_url ( 'wbb-projects-master/public/assets/js/public.js' ) , array ( 'jquery' ) );

		// declare the URL to the file that handles the AJAX request (wp-admin/admin-ajax.php)
		//wp_localize_script( $this->plugin_slug . '-public', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
		wp_localize_script ( 'x-public' , "MyAjax" , array ( 'ajaxurl' => admin_url ( 'admin-ajax.php' ) ) );

	}

	public static function wbb_project_load_template ( $slug , $name = '' )
	{

		//$template = '';
		$wbb_project_template_folder = "templates/plugins/wbb-projects-master/";

		// Look in yourtheme/slug-name.php and yourtheme/woocommerce/slug-name.php
		if ( $name )
		{
			$template = locate_template ( array (
				"{$slug}-{$name}.php" ,
				"{$wbb_project_template_folder}{$slug}-{$name}.php"
			) );
		}

		// Get default slug-name.php
		if ( ! $template && $name && file_exists ( WBB_PROJECTS_PLUGIN_DIR_PATH . "templates/{$slug}-{$name}.php" ) )
		{
			$template = WBB_PROJECTS_PLUGIN_DIR_PATH . "templates/{$slug}-{$name}.php";

		}

		// If template file doesn't exist, look in yourtheme/slug.php and yourtheme/woocommerce/slug.php
		if ( ! $template )
		{
			$template = locate_template ( array (
				"{$slug}.php" ,
				"{wbb-projects-master/}{$slug}.php"
			) );

		}


		if ( $template )
		{
			//include ( $template );
		}

		return $template;
	}


	public static function show_projects_main_view ()
	{

		self::enqueue_projects_scripts ();
		//Main project query
		$template = self::wbb_project_load_template ( "project" , "main-view" );
		include ( $template );

	}
	public function getCountryName($strCountryCode) {
		$countrycodes = array (
		  'AF' => 'Afghanistan',
		  'AX' => 'Åland Islands',
		  'AL' => 'Albania',
		  'DZ' => 'Algeria',
		  'AS' => 'American Samoa',
		  'AD' => 'Andorra',
		  'AO' => 'Angola',
		  'AI' => 'Anguilla',
		  'AQ' => 'Antarctica',
		  'AG' => 'Antigua and Barbuda',
		  'AR' => 'Argentina',
		  'AU' => 'Australia',
		  'AT' => 'Austria',
		  'AZ' => 'Azerbaijan',
		  'BS' => 'Bahamas',
		  'BH' => 'Bahrain',
		  'BD' => 'Bangladesh',
		  'BB' => 'Barbados',
		  'BY' => 'Belarus',
		  'BE' => 'Belgium',
		  'BZ' => 'Belize',
		  'BJ' => 'Benin',
		  'BM' => 'Bermuda',
		  'BT' => 'Bhutan',
		  'BO' => 'Bolivia',
		  'BA' => 'Bosnia and Herzegovina',
		  'BW' => 'Botswana',
		  'BV' => 'Bouvet Island',
		  'BR' => 'Brazil',
		  'IO' => 'British Indian Ocean Territory',
		  'BN' => 'Brunei Darussalam',
		  'BG' => 'Bulgaria',
		  'BF' => 'Burkina Faso',
		  'BI' => 'Burundi',
		  'KH' => 'Cambodia',
		  'CM' => 'Cameroon',
		  'CA' => 'Canada',
		  'CV' => 'Cape Verde',
		  'KY' => 'Cayman Islands',
		  'CF' => 'Central African Republic',
		  'TD' => 'Chad',
		  'CL' => 'Chile',
		  'CN' => 'China',
		  'CX' => 'Christmas Island',
		  'CC' => 'Cocos (Keeling) Islands',
		  'CO' => 'Colombia',
		  'KM' => 'Comoros',
		  'CG' => 'Congo',
		  'CD' => 'Democratic Republic of the Congo',
		  'CK' => 'Cook Islands',
		  'CR' => 'Costa Rica',
		  'CI' => 'Ivory Coast',
		  'HR' => 'Croatia',
		  'CU' => 'Cuba',
		  'CY' => 'Cyprus',
		  'CZ' => 'Czech Republic',
		  'DK' => 'Denmark',
		  'DJ' => 'Djibouti',
		  'DM' => 'Dominica',
		  'DO' => 'Dominican Republic',
		  'EC' => 'Ecuador',
		  'EG' => 'Egypt',
		  'SV' => 'El Salvador',
		  'GQ' => 'Equatorial Guinea',
		  'ER' => 'Eritrea',
		  'EE' => 'Estonia',
		  'ET' => 'Ethiopia',
		  'FK' => 'Falkland Islands (Malvinas)',
		  'FO' => 'Faroe Islands',
		  'FJ' => 'Fiji',
		  'FI' => 'Finland',
		  'FR' => 'France',
		  'GF' => 'French Guiana',
		  'PF' => 'French Polynesia',
		  'TF' => 'French Southern Territories',
		  'GA' => 'Gabon',
		  'GM' => 'Gambia',
		  'GE' => 'Georgia',
		  'DE' => 'Germany',
		  'GH' => 'Ghana',
		  'GI' => 'Gibraltar',
		  'GR' => 'Greece',
		  'GL' => 'Greenland',
		  'GD' => 'Grenada',
		  'GP' => 'Guadeloupe',
		  'GU' => 'Guam',
		  'GT' => 'Guatemala',
		  'GG' => 'Guernsey',
		  'GN' => 'Guinea',
		  'GW' => 'Guinea-Bissau',
		  'GY' => 'Guyana',
		  'HT' => 'Haiti',
		  'HM' => 'Heard Island and Mcdonald Islands',
		  'VA' => 'Vatican City State',
		  'HN' => 'Honduras',
		  'HK' => 'Hong Kong',
		  'HU' => 'Hungary',
		  'IS' => 'Iceland',
		  'IN' => 'India',
		  'ID' => 'Indonesia',
		  'IR' => 'Iran, Islamic Republic of',
		  'IQ' => 'Iraq',
		  'IE' => 'Ireland',
		  'IM' => 'Isle of Man',
		  'IL' => 'Israel',
		  'IT' => 'Italy',
		  'JM' => 'Jamaica',
		  'JP' => 'Japan',
		  'JE' => 'Jersey',
		  'JO' => 'Jordan',
		  'KZ' => 'Kazakhstan',
		  'KE' => 'KENYA',
		  'KI' => 'Kiribati',
		  'KP' => 'Korea, Democratic People\'s Republic of',
		  'KR' => 'Korea, Republic of',
		  'KW' => 'Kuwait',
		  'KG' => 'Kyrgyzstan',
		  'LA' => 'Lao People\'s Democratic Republic',
		  'LV' => 'Latvia',
		  'LB' => 'Lebanon',
		  'LS' => 'Lesotho',
		  'LR' => 'Liberia',
		  'LY' => 'Libyan Arab Jamahiriya',
		  'LI' => 'Liechtenstein',
		  'LT' => 'Lithuania',
		  'LU' => 'Luxembourg',
		  'MO' => 'Macao',
		  'MK' => 'Macedonia, the Former Yugoslav Republic of',
		  'MG' => 'Madagascar',
		  'MW' => 'Malawi',
		  'MY' => 'Malaysia',
		  'MV' => 'Maldives',
		  'ML' => 'Mali',
		  'MT' => 'Malta',
		  'MH' => 'Marshall Islands',
		  'MQ' => 'Martinique',
		  'MR' => 'Mauritania',
		  'MU' => 'Mauritius',
		  'YT' => 'Mayotte',
		  'MX' => 'Mexico',
		  'FM' => 'Micronesia, Federated States of',
		  'MD' => 'Moldova, Republic of',
		  'MC' => 'Monaco',
		  'MN' => 'Mongolia',
		  'ME' => 'Montenegro',
		  'MS' => 'Montserrat',
		  'MA' => 'Morocco',
		  'MZ' => 'Mozambique',
		  'MM' => 'Myanmar',
		  'NA' => 'Namibia',
		  'NR' => 'Nauru',
		  'NP' => 'Nepal',
		  'NL' => 'Netherlands',
		  'AN' => 'Netherlands Antilles',
		  'NC' => 'New Caledonia',
		  'NZ' => 'New Zealand',
		  'NI' => 'Nicaragua',
		  'NE' => 'Niger',
		  'NG' => 'Nigeria',
		  'NU' => 'Niue',
		  'NF' => 'Norfolk Island',
		  'MP' => 'Northern Mariana Islands',
		  'NO' => 'Norway',
		  'OM' => 'Oman',
		  'PK' => 'Pakistan',
		  'PW' => 'Palau',
		  'PS' => 'Palestinian Territory, Occupied',
		  'PA' => 'Panama',
		  'PG' => 'Papua New Guinea',
		  'PY' => 'Paraguay',
		  'PE' => 'Peru',
		  'PH' => 'Philippines',
		  'PN' => 'Pitcairn',
		  'PL' => 'Poland',
		  'PT' => 'Portugal',
		  'PR' => 'Puerto Rico',
		  'QA' => 'Qatar',
		  'RE' => 'Réunion',
		  'RO' => 'Romania',
		  'RU' => 'Russian Federation',
		  'RW' => 'Rwanda',
		  'SH' => 'Saint Helena',
		  'KN' => 'Saint Kitts and Nevis',
		  'LC' => 'Saint Lucia',
		  'PM' => 'Saint Pierre and Miquelon',
		  'VC' => 'Saint Vincent and the Grenadines',
		  'WS' => 'Samoa',
		  'SM' => 'San Marino',
		  'ST' => 'Sao Tome and Principe',
		  'SA' => 'Saudi Arabia',
		  'SN' => 'Senegal',
		  'RS' => 'Serbia',
		  'SC' => 'Seychelles',
		  'SL' => 'Sierra Leone',
		  'SG' => 'Singapore',
		  'SK' => 'Slovakia',
		  'SI' => 'Slovenia',
		  'SB' => 'Solomon Islands',
		  'SO' => 'Somalia',
		  'ZA' => 'South Africa',
		  'GS' => 'South Georgia and the South Sandwich Islands',
		  'ES' => 'Spain',
		  'LK' => 'Sri Lanka',
		  'SD' => 'Sudan',
		  'SR' => 'Suriname',
		  'SJ' => 'Svalbard and Jan Mayen',
		  'SZ' => 'Swaziland',
		  'SE' => 'Sweden',
		  'CH' => 'Switzerland',
		  'SY' => 'Syrian Arab Republic',
		  'TW' => 'Taiwan, Province of China',
		  'TJ' => 'Tajikistan',
		  'TZ' => 'Tanzania',
		  'TH' => 'Thailand',
		  'TL' => 'Timor-Leste',
		  'TG' => 'Togo',
		  'TK' => 'Tokelau',
		  'TO' => 'Tonga',
		  'TT' => 'Trinidad and Tobago',
		  'TN' => 'Tunisia',
		  'TR' => 'Turkey',
		  'TM' => 'Turkmenistan',
		  'TC' => 'Turks and Caicos Islands',
		  'TV' => 'Tuvalu',
		  'UG' => 'Uganda',
		  'UA' => 'Ukraine',
		  'AE' => 'United Arab Emirates',
		  'GB' => 'United Kingdom',
		  'US' => 'United States',
		  'UM' => 'United States Minor Outlying Islands',
		  'UY' => 'Uruguay',
		  'UZ' => 'Uzbekistan',
		  'VU' => 'Vanuatu',
		  'VE' => 'Venezuela',
		  'VN' => 'Viet Nam',
		  'VG' => 'Virgin Islands, British',
		  'VI' => 'Virgin Islands, U.S.',
		  'WF' => 'Wallis and Futuna',
		  'EH' => 'Western Sahara',
		  'YE' => 'Yemen',
		  'ZM' => 'Zambia',
		  'ZW' => 'Zimbabwe',
		);
		foreach ($countrycodes as $key => $value) {
			if($key==$strCountryCode){
				return $value;
			}
		}
		return '';
		//return array_search(strtolower($strCountryName), array_map('strtolower', $countrycodes));
	}


// ISO 3166-1 country names and codes from http://opencountrycodes.appspot.com/javascript               
 
	public function show_project_query ()
	{
		global $wpdb;
		if ( ! isset( $_POST[ "page" ] ) )
		{
			$paged = 1;
		}
		else
		{
			$paged = $_POST[ "page" ];
		}

		// WP_Query arguments
		$args = array (
			'post_type'      => 'projects' ,
			'paged'          => $paged ,
			'posts_per_page' => '-1' ,
		);


		if ( isset( $_POST[ "country" ] ) && $_POST[ "country" ] !== "" )
		{

			$countries_code = $_POST[ "country" ];

			$args[ "meta_query" ] = array (
				'relation' => 'OR'
			);

			$countries = explode ( "," , $countries_code );

			foreach ( $countries as $country )
			{
				$countryName =$this->getCountryName($country);

				array_push ( $args[ "meta_query" ] , array (
						'key'     => 'ocsdnet_project_countries' ,
						'value'   => $countryName ,
						'compare' => 'LIKE' ,
						'type'    => 'CHAR' ,
					)

				);

			}
		}






		// The Query
		

		$query = new WP_Query( $args );
		
		$count=0;
		// The Loop
		if ( $query->have_posts () )
		{
			//echo "<pre>";

			while ( $query->have_posts () )
			{
			/*	if($count==0){
					$style="style='margin-top:-400px'";
				}else if($count==1 ){
					$style="style='margin-top:-150px'";
				}else{
					$style="";
				}*/

				$query->the_post ();
				//return var_dump(get_the_ID ());

				$post_meta = get_post_meta( get_the_ID());

				$post_terms = wp_get_post_terms ( get_the_ID () , "projects" );


				$template = self::wbb_project_load_template ( "project" , "result" );
				include ( $template );

				$count++;

			} 



		}
		else
		{
			$this::wbb_project_load_template ( "project" , "result-no-results" );
		}

		if ( $query->found_posts > 9 )
		{

			$num_pag = ceil ( $query->found_posts / 12 );
			$template=$this::wbb_project_load_template ( "project" , "pagination" );
			include ( $template );
		}

		// Restore original Post Data
		wp_reset_postdata ();

		die();
	}

	public function limit_words($string, $word_limit)
	{
	    $words = explode(" ",$string);
	    return implode(" ",array_splice($words,0,$word_limit));
	}

	public function get_initial_categories(){
		$customPostTaxonomies='categories';
		$args = array(
		         	  'orderby' => 'name',
			          'show_count' => 0,
		        	  'pad_counts' => 0,
			          'hierarchical' => 1,
		        	  'taxonomy' => $customPostTaxonomies,
		        	  'title_li' => ''
		        	);
		$p_categories=get_categories( $args );

		$p_list="";
	    foreach ($p_categories as $key => $value) {
	     	$p_list.="<li>".$value->name."</li>";
	    }
		    
		
		print_r($p_list);
		die();
	

	}

	function get_initial_data(){
		global $wpdb;
		$initialdata=array();
		$countries=$this->getCountries();
		$title=$this->getProjectTitles();

		foreach ($title as $key => $value) {
			foreach ($countries as $key => $value2) {
				if($value['post_id']==$value2['post_id']){
					$sql="SELECT terms.slug FROM wp_posts as post
							JOIN wp_term_relationships as rel ON post.ID = rel.object_ID 
							JOIN wp_term_taxonomy as ttax ON rel.term_taxonomy_id = ttax.term_taxonomy_id
							JOIN wp_terms as terms ON ttax.term_id = terms.term_id
							WHERE ( ttax.taxonomy='categories' AND post.ID='".$value2['post_id']."' AND post.post_type='projects' AND post.post_status != 'revision')";
					$results=$wpdb->get_results($sql);
					$categories=json_decode(json_encode($results),true);
					$slug="";
					foreach ($categories as $key3 => $value3) {
						$slug.=$value3['slug']."|";
					}
					$slug=rtrim($slug,'|');
					//echo $slug;
					$initialdata[]=array('post_id'=>$value2['post_id'],'title'=>$value['meta_value'],'countries'=>$value2['meta_value'],'link'=>get_permalink ($value2['post_id']),'categories'=>$slug);
				}
			}
		}
		//var_dump($title);die();
		echo json_encode($initialdata);
		exit();
	}
    function getProjectCategories(){
    	global $wpdb;

    	$results=$wpdb->get_results("SELECT terms.slug FROM wp_posts as post
				JOIN wp_term_relationships as rel ON post.ID = rel.object_ID 
				JOIN wp_term_taxonomy as ttax ON rel.term_taxonomy_id = ttax.term_taxonomy_id
				JOIN wp_terms as terms ON ttax.term_id = terms.term_id
				WHERE ( ttax.taxonomy='categories' AND post.post_type='projects' AND post.ID='1077' AND post.post_status != 'revision')");
    	echo json_encode($results);
    	exit();
    }

	function getProjectTitles(){
		global $wpdb;
		$results = $wpdb->get_results ( "SELECT DISTINCT wm.meta_value,wm.post_id

                                        FROM " . $wpdb->prefix . "postmeta wm
                                        INNER JOIN " . $wpdb->prefix . "posts wp ON wp.ID = wm.post_id

                                        WHERE meta_key = 'ocsdnet_project_title'
                                        AND wp.post_status = 'publish'" );
		return json_decode(json_encode($results),true);
	}

	function getCountries(){
		global $wpdb;


		
		$results = $wpdb->get_results ( "SELECT DISTINCT wm.meta_value,wm.post_id

                                        FROM " . $wpdb->prefix . "postmeta wm
                                        INNER JOIN " . $wpdb->prefix . "posts wp ON wp.ID = wm.post_id

                                        WHERE meta_key = 'ocsdnet_project_countries'
                                        AND wp.post_status = 'publish'" );

		//$projectTitles = array_unique ( $results );

		//asort ( $countries );
		return json_decode(json_encode($results),true);
	}

	public function get_initial_countries_front_end ()
	{

		global $wpdb;


		$results = $wpdb->get_col ( "SELECT DISTINCT meta_value

                                        FROM " . $wpdb->prefix . "postmeta wm
                                        INNER JOIN " . $wpdb->prefix . "posts wp ON wp.ID = wm.post_id

                                        WHERE meta_key = 'ocsdnet_project_countries'
                                        AND wp.post_status = 'publish'" );

		$countries = array_unique ( $results );

		asort ( $countries );

		$countries                = implode ( "," , $countries );
		$countries_filtered_again = explode ( ',' , $countries );
		$final_countries_exploded = array_unique ( $countries_filtered_again );
		$countries                = implode ( "," , $final_countries_exploded );


		echo $countries;

		die();

	}

	public function add_tax ()
	{


		// Add new taxonomy, project_category.
		$labels = array (
			'name'              => _x ( 'Project Categories' , 'taxonomy general name' ) ,
			'singular_name'     => _x ( 'Project Category' , 'taxonomy singular name' ) ,
			'search_items'      => __ ( 'Search Project Categories' ) ,
			'all_items'         => __ ( 'All Project Categories' ) ,
			'parent_item'       => __ ( 'Parent Project Category' ) ,
			'parent_item_colon' => __ ( 'Parent Project Category:' ) ,
			'edit_item'         => __ ( 'Edit Project Category' ) ,
			'update_item'       => __ ( 'Update Project Category' ) ,
			'add_new_item'      => __ ( 'Add New Project Category' ) ,
			'new_item_name'     => __ ( 'New Project Category Name' ) ,
			'menu_name'         => __ ( 'Project Category' ) ,
		);
		$args   = array (
			'hierarchical'      => TRUE ,
			'labels'            => $labels ,
			'show_ui'           => TRUE ,
			'show_admin_column' => TRUE ,
			'query_var'         => TRUE ,
			'rewrite'           => array ( 'slug' => 'project_category' ) ,
		);

		/*register_taxonomy ( 'project_category' , array ( 'project' ) , $args );

		register_taxonomy_for_object_type ( 'project_category' , 'project' );*/
	}


	public function wbb_projects_front_end_list ( $atts )
	{
		extract ( shortcode_atts ( array (
			'n_items' => 'n_items'
		) , $atts ) );

		$args = array (
			'post_type'      => 'project' ,
			'paged'          => 1 ,
			'posts_per_page' => $n_items ,
		);


		// The Query
		$query = new WP_Query( $args );

		// The Loop
		if ( $query->have_posts () )
		{
			while ( $query->have_posts () )
			{
				$query->the_post ();

				$post_terms = wp_get_post_terms ( get_the_ID () , "project_category" );
				$terms_slug = "";
				$terms_name = "";
				foreach ( $post_terms as $term )
				{
					$terms_slug .= " " . $term->slug;
					$terms_name .= " " . $term->name;
				}

				$country_connected = get_post_meta ( get_the_ID () , "country_connected" , TRUE );

				$country_connected = explode ( "," , $country_connected );

				$country_names = "";
				foreach ( $country_connected as $code )
				{
					$country_names .= self::wbb_get_country_name ( $code );
				}
				$omo = "xxx";

				//$this::wbb_project_load_template("project","front-end-list");

				$template = $this::wbb_project_load_template ( "project" , "front-end-list" );
				include ( $template );

			}
		}
		/*else
		{
			include ( WBB_PROJECTS_PLUGIN_DIR_PATH . 'public/views/no_posts.php' );
		}*/

		// Restore original Post Data
		wp_reset_postdata ();


	}

	public static function wbb_get_country_name ( $code )
	{

		$countries[ 'Afghanistan' ]                         = 'AF';
		$countries[ 'Angola' ]                              = 'AO';
		$countries[ 'Albania' ]                             = 'AL';
		$countries[ 'United Arab Emirates' ]                = 'AE';
		$countries[ 'Argentina' ]                           = 'AR';
		$countries[ 'Armenia' ]                             = 'AM';
		$countries[ 'Antarctica' ]                          = '-99';
		$countries[ 'French Southern and Antarctic Lands' ] = '-99';
		$countries[ 'Australia' ]                           = 'AU';
		$countries[ 'Austria' ]                             = 'AT';
		$countries[ 'Azerbaijan' ]                          = 'AZ';
		$countries[ 'Burundi' ]                             = 'BI';
		$countries[ 'Belgium' ]                             = 'BE';
		$countries[ 'Benin' ]                               = 'BJ';
		$countries[ 'Burkina Faso' ]                        = 'BF';
		$countries[ 'Bangladesh' ]                          = 'BD';
		$countries[ 'Bulgaria' ]                            = 'BG';
		$countries[ 'The Bahamas' ]                         = 'BS';
		$countries[ 'Bosnia and Herzegovina' ]              = 'BA';
		$countries[ 'Belarus' ]                             = 'BY';
		$countries[ 'Belize' ]                              = 'BZ';
		$countries[ 'Bolivia' ]                             = 'BO';
		$countries[ 'Brazil' ]                              = 'BR';
		$countries[ 'Brunei' ]                              = 'BN';
		$countries[ 'Bhutan' ]                              = 'BT';
		$countries[ 'Botswana' ]                            = 'BW';
		$countries[ 'Central African Republic' ]            = 'CF';
		$countries[ 'Canada' ]                              = 'CA';
		$countries[ 'Switzerland' ]                         = 'CH';
		$countries[ 'Chile' ]                               = 'CL';
		$countries[ 'China' ]                               = 'CN';
		$countries[ 'Ivory Coast' ]                         = 'CI';
		$countries[ 'Cameroon' ]                            = 'CM';
		$countries[ 'Democratic Republic of the Congo' ]    = 'ZR';
		$countries[ 'Republic of Congo' ]                   = 'CG';
		$countries[ 'Colombia' ]                            = 'CO';
		$countries[ 'Costa Rica' ]                          = 'CR';
		$countries[ 'Cuba' ]                                = 'CU';
		$countries[ 'Northern Cyprus' ]                     = '-99';
		$countries[ 'Cyprus' ]                              = 'CY';
		$countries[ 'Czech Republic' ]                      = 'CZ';
		$countries[ 'Germany' ]                             = 'DE';
		$countries[ 'Djibouti' ]                            = 'DJ';
		$countries[ 'Denmark' ]                             = 'DK';
		$countries[ 'Dominican Republic' ]                  = 'DO';
		$countries[ 'Algeria' ]                             = 'DZ';
		$countries[ 'Ecuador' ]                             = 'EC';
		$countries[ 'Egypt' ]                               = 'EG';
		$countries[ 'Eritrea' ]                             = 'ER';
		$countries[ 'Spain' ]                               = 'ES';
		$countries[ 'Estonia' ]                             = 'EE';
		$countries[ 'Ethiopia' ]                            = 'ET';
		$countries[ 'Finland' ]                             = 'FI';
		$countries[ 'Fiji' ]                                = 'FJ';
		$countries[ 'Falkland Islands' ]                    = '-99';
		$countries[ 'France' ]                              = 'FR';
		$countries[ 'Gabon' ]                               = 'GA';
		$countries[ 'United Kingdom' ]                      = 'GB';
		$countries[ 'Georgia' ]                             = 'GE';
		$countries[ 'Ghana' ]                               = 'GH';
		$countries[ 'Guinea' ]                              = 'GN';
		$countries[ 'Gambia' ]                              = 'GM';
		$countries[ 'Guinea Bissau' ]                       = 'GW';
		$countries[ 'Equatorial Guinea' ]                   = 'GQ';
		$countries[ 'Greece' ]                              = 'GR';
		$countries[ 'Greenland' ]                           = 'GL';
		$countries[ 'Guatemala' ]                           = 'GT';
		$countries[ 'Guyana' ]                              = 'GY';
		$countries[ 'Honduras' ]                            = 'HN';
		$countries[ 'Croatia' ]                             = 'HR';
		$countries[ 'Haiti' ]                               = 'HT';
		$countries[ 'Hungary' ]                             = 'HU';
		$countries[ 'Indonesia' ]                           = 'ID';
		$countries[ 'India' ]                               = 'IN';
		$countries[ 'Ireland' ]                             = 'IE';
		$countries[ 'Iran' ]                                = 'IR';
		$countries[ 'Iraq' ]                                = 'IQ';
		$countries[ 'Iceland' ]                             = 'IS';
		$countries[ 'Israel' ]                              = 'IL';
		$countries[ 'Italy' ]                               = 'IT';
		$countries[ 'Jamaica' ]                             = 'JM';
		$countries[ 'Jordan' ]                              = 'JO';
		$countries[ 'Japan' ]                               = 'JP';
		$countries[ 'Kazakhstan' ]                          = 'KZ';
		$countries[ 'Kenya' ]                               = 'KE';
		$countries[ 'Kyrgyzstan' ]                          = 'KG';
		$countries[ 'Cambodia' ]                            = 'KH';
		$countries[ 'South Korea' ]                         = 'KR';
		$countries[ 'Kosovo' ]                              = 'KV';
		$countries[ 'Kuwait' ]                              = 'KW';
		$countries[ 'Laos' ]                                = 'LA';
		$countries[ 'Lebanon' ]                             = 'LB';
		$countries[ 'Liberia' ]                             = 'LR';
		$countries[ 'Libya' ]                               = 'LY';
		$countries[ 'Sri Lanka' ]                           = 'LK';
		$countries[ 'Lesotho' ]                             = 'LS';
		$countries[ 'Lithuania' ]                           = 'LT';
		$countries[ 'Luxembourg' ]                          = 'LU';
		$countries[ 'Latvia' ]                              = 'LV';
		$countries[ 'Morocco' ]                             = 'MA';
		$countries[ 'Moldova' ]                             = 'MD';
		$countries[ 'Madagascar' ]                          = 'MG';
		$countries[ 'Mexico' ]                              = 'MX';
		$countries[ 'Macedonia' ]                           = 'MK';
		$countries[ 'Mali' ]                                = 'ML';
		$countries[ 'Myanmar' ]                             = 'MM';
		$countries[ 'Montenegro' ]                          = 'ME';
		$countries[ 'Mongolia' ]                            = 'MN';
		$countries[ 'Mozambique' ]                          = 'MZ';
		$countries[ 'Mauritania' ]                          = 'MR';
		$countries[ 'Malawi' ]                              = 'MW';
		$countries[ 'Malaysia' ]                            = 'MY';
		$countries[ 'Namibia' ]                             = 'NA';
		$countries[ 'New Caledonia' ]                       = 'NC';
		$countries[ 'Niger' ]                               = 'NE';
		$countries[ 'Nigeria' ]                             = 'NG';
		$countries[ 'Nicaragua' ]                           = 'NI';
		$countries[ 'Netherlands' ]                         = 'NL';
		$countries[ 'Norway' ]                              = 'NO';
		$countries[ 'Nepal' ]                               = 'NP';
		$countries[ 'New Zealand' ]                         = 'NZ';
		$countries[ 'Oman' ]                                = 'OM';
		$countries[ 'Pakistan' ]                            = 'PK';
		$countries[ 'Panama' ]                              = 'PA';
		$countries[ 'Peru' ]                                = 'PE';
		$countries[ 'Philippines' ]                         = 'PH';
		$countries[ 'Papua New Guinea' ]                    = 'PG';
		$countries[ 'Poland' ]                              = 'PL';
		$countries[ 'Puerto Rico' ]                         = 'PR';
		$countries[ 'North Korea' ]                         = 'KP';
		$countries[ 'Portugal' ]                            = 'PT';
		$countries[ 'Paraguay' ]                            = 'PY';
		$countries[ 'Palestine' ]                           = 'GZ';
		$countries[ 'Qatar' ]                               = 'QA';
		$countries[ 'Romania' ]                             = 'RO';
		$countries[ 'Russia' ]                              = 'RU';
		$countries[ 'Rwanda' ]                              = 'RW';
		$countries[ 'Western Sahara' ]                      = '-99';
		$countries[ 'Saudi Arabia' ]                        = 'SA';
		$countries[ 'Sudan' ]                               = 'SD';
		$countries[ 'South Sudan' ]                         = 'SS';
		$countries[ 'Senegal' ]                             = 'SN';
		$countries[ 'Solomon Islands' ]                     = 'SB';
		$countries[ 'Sierra Leone' ]                        = 'SL';
		$countries[ 'El Salvador' ]                         = 'SV';
		$countries[ 'Somaliland' ]                          = '-99';
		$countries[ 'Somalia' ]                             = 'SO';
		$countries[ 'Republic of Serbia' ]                  = 'YF';
		$countries[ 'Suriname' ]                            = 'SR';
		$countries[ 'Slovakia' ]                            = 'SK';
		$countries[ 'Slovenia' ]                            = 'SI';
		$countries[ 'Sweden' ]                              = 'SE';
		$countries[ 'Swaziland' ]                           = 'SZ';
		$countries[ 'Syria' ]                               = 'SY';
		$countries[ 'Chad' ]                                = 'TD';
		$countries[ 'Togo' ]                                = 'TG';
		$countries[ 'Thailand' ]                            = 'TH';
		$countries[ 'Tajikistan' ]                          = 'TJ';
		$countries[ 'Turkmenistan' ]                        = 'TM';
		$countries[ 'East Timor' ]                          = 'TP';
		$countries[ 'Trinidad and Tobago' ]                 = 'TT';
		$countries[ 'Tunisia' ]                             = 'TN';
		$countries[ 'Turkey' ]                              = 'TR';
		$countries[ 'Taiwan' ]                              = '-99';
		$countries[ 'Tanzania' ]                            = 'TZ';
		$countries[ 'Uganda' ]                              = 'UG';
		$countries[ 'Ukraine' ]                             = 'UA';
		$countries[ 'Uruguay' ]                             = 'UY';
		$countries[ 'United States of America' ]            = 'US';
		$countries[ 'Uzbekistan' ]                          = 'UZ';
		$countries[ 'Venezuela' ]                           = 'VE';
		$countries[ 'Vietnam' ]                             = 'VN';
		$countries[ 'Vanuatu' ]                             = 'VU';
		$countries[ 'Yemen' ]                               = 'RY';
		$countries[ 'South Africa' ]                        = 'ZA';
		$countries[ 'Zambia' ]                              = 'ZM';
		$countries[ 'Zimbabwe' ]                            = 'ZW';
		$countries[ 'Pan-Africa' ]                          = 'VE';

		$country_name = array_search ( $code , $countries );

		return $country_name;

	}


	/**
	 * Add a custom post type :: Projects
	 */
	public static function add_project_post_type ()
	{


		$labels = array (
			'name'               => _x ( 'Projects' , 'Post Type General Name' , 'post_type' ) ,
			'singular_name'      => _x ( 'Project' , 'Post Type Singular Name' , 'post_type' ) ,
			'menu_name'          => __ ( 'Project' , 'post_type' ) ,
			'parent_item_colon'  => __ ( 'Parent Project:' , 'post_type' ) ,
			'all_items'          => __ ( 'All Projects' , 'post_type' ) ,
			'view_item'          => __ ( 'View Project' , 'post_type' ) ,
			'add_new_item'       => __ ( 'Add New Project' , 'post_type' ) ,
			'add_new'            => __ ( 'Add Project' , 'post_type' ) ,
			'edit_item'          => __ ( 'Edit Project' , 'post_type' ) ,
			'update_item'        => __ ( 'Update Project' , 'post_type' ) ,
			'search_items'       => __ ( 'Search Project' , 'post_type' ) ,
			'not_found'          => __ ( 'Not found' , 'post_type' ) ,
			'not_found_in_trash' => __ ( 'Not found in Trash' , 'post_type' ) ,
		);
		$args   = array (
			'label'               => __ ( 'project' , 'post_type' ) ,
			'description'         => __ ( 'Project' , 'post_type' ) ,
			'labels'              => $labels ,
			'supports'            => array (
				'title' ,
				'editor' ,
				'thumbnail' ,
				'excerpt'
			) ,

			'hierarchical'        => FALSE ,
			'public'              => TRUE ,
			'show_ui'             => TRUE ,
			'show_in_menu'        => TRUE ,
			'show_in_nav_menus'   => TRUE ,
			'show_in_admin_bar'   => TRUE ,
			'menu_position'       => 5 ,
			'menu_icon'           => WBB_PROJECTS_ICON_URI ,
			'can_export'          => TRUE ,
			'has_archive'         => TRUE ,
			'exclude_from_search' => FALSE ,
			'publicly_queryable'  => TRUE ,
			'capability_type'     => 'post' ,
		);
		//register_post_type ( 'project' , $args );


		// Add new taxonomy, project_category.
		$labels = array (
			'name'              => _x ( 'Project Categories' , 'taxonomy general name' ) ,
			'singular_name'     => _x ( 'Project Category' , 'taxonomy singular name' ) ,
			'search_items'      => __ ( 'Search Project Categories' ) ,
			'all_items'         => __ ( 'All Project Categories' ) ,
			'parent_item'       => __ ( 'Parent Project Category' ) ,
			'parent_item_colon' => __ ( 'Parent Project Category:' ) ,
			'edit_item'         => __ ( 'Edit Project Category' ) ,
			'update_item'       => __ ( 'Update Project Category' ) ,
			'add_new_item'      => __ ( 'Add New Project Category' ) ,
			'new_item_name'     => __ ( 'New Project Category Name' ) ,
			'menu_name'         => __ ( 'Project Category' ) ,
		);

		$args = array (
			'hierarchical'      => TRUE ,
			'labels'            => $labels ,
			'show_ui'           => TRUE ,
			'show_admin_column' => TRUE ,
			'query_var'         => TRUE ,
			'rewrite'           => array ( 'slug' => 'project_category' ) ,
		);

		//register_taxonomy ( 'project_category' , array ( 'project' ) , $args );

		//register_taxonomy_for_object_type ( 'project_category' , 'project' );

	}

	public function where_page ()
	{


		$template = self::wbb_project_load_template ( "project" , "where" );
		include ( $template );

	}

	public function where_page_map ()
	{

		//Include countdown script, css ands js.
		self::map_enqueue_styles ();
		self::map_enqueue_scripts ();

		$template = self::wbb_project_load_template ( "project" , "where-map" );
		include ( $template );

	}

	/**
	 * Register and enqueue public-facing style sheet.
	 *
	 * @since    1.0.0
	 */
	public function map_enqueue_styles ()
	{

		wp_enqueue_style ( $this->plugin_slug . '-where_map' , plugins_url ( 'wbb-projects-master/public/assets/where-map/css/where_map.css' ) , array () , self::VERSION );
	}

	/**
	 * Register and enqueues public-facing JavaScript files.
	 *
	 * @since    1.0.0
	 */
	public function map_enqueue_scripts ()
	{

		wp_enqueue_script ( $this->plugin_slug . '-jvector' , plugins_url ( 'wbb-projects-master/assets/js/jquery-jvectormap-1.2.2.min.js' ) , array ( 'jquery' ) );

		wp_enqueue_script ( $this->plugin_slug . '-jvector_worldmap' , plugins_url ( 'wbb-projects-master/assets/js/jquery-jvectormap-world-mill-en.js' ) , array ( 'jquery' ) );

		wp_enqueue_script ( $this->plugin_slug . '-highcharts' , "http://code.highcharts.com/highcharts.js" , array ( 'jquery' ) );

		wp_enqueue_script ( $this->plugin_slug . '-jquery-knob' , plugins_url ( 'wbb-projects-master/public/assets/where-map/js/jquery.knob.js' ) , array ( 'jquery' ) );

		wp_enqueue_script ( $this->plugin_slug . '-where_map' , plugins_url ( 'wbb-projects-master/public/assets/where-map/js/where_map.js' ) , array ( 'jquery' ) );

	}

        public function get_filters_amount_mouseover(){
            
            //Get from DATABASE the filters results.
            
            global $wpdb;
            
            $countries_code = $wpdb->get_col(" 
                                        SELECT DISTINCT meta_value
                                        FROM ".$wpdb->prefix."postmeta wm
                                        WHERE wm.meta_key =  'country_connected'
                                     ");
            
            $prepare_codes = implode(",", $countries_code );
            $prepare_codes = explode(",", $prepare_codes );
            $prepare_codes = array_unique($prepare_codes);
            
            $result         = array();
            
            $scaling_id = get_terms( 'project_category', array(
                    'slug'    => 'scaling'
             ) );
            
            $scaling_id = $scaling_id[0]->term_taxonomy_id;
            
            $research_id = get_terms( 'project_category', array(
                    'slug'    => 'research'
             ) );
            
            $research_id = $research_id[0]->term_taxonomy_id;
            
            $innovation_id = get_terms( 'project_category', array(
                    'slug'    => 'innovation'
             ) );
            
            $innovation_id = $innovation_id[0]->term_taxonomy_id;
            
            foreach( $prepare_codes as $code )
            {
                
                
                $scaling = $wpdb->get_var(" 
                    
                                    SELECT count(DISTINCT ID)

                                    FROM wp_posts wp

                                    INNER JOIN wp_term_relationships wt ON wp.ID = wt.object_id
                                    INNER JOIN wp_postmeta wm ON wp.ID = wm.post_id

                                    WHERE post_type = 'project'
                                    AND term_taxonomy_id = $scaling_id
                                    AND wm.meta_key = 'country_connected'
                                    AND wm.meta_value LIKE '%$code%'
                                    AND post_status  = 'publish'

                          ");
                $research = $wpdb->get_var(" 
                                    SELECT count(DISTINCT ID)

                                    FROM wp_posts wp

                                    INNER JOIN wp_term_relationships wt ON wp.ID = wt.object_id
                                    INNER JOIN wp_postmeta wm ON wp.ID = wm.post_id

                                    WHERE post_type = 'project'
                                    AND term_taxonomy_id = $research_id
                                    AND wm.meta_key = 'country_connected'
                                    AND wm.meta_value LIKE '%$code%'
                                    AND post_status  = 'publish'
                          ");
                $innovation = $wpdb->get_var(" 
                                    SELECT count(DISTINCT ID)

                                    FROM wp_posts wp

                                    INNER JOIN wp_term_relationships wt ON wp.ID = wt.object_id
                                    INNER JOIN wp_postmeta wm ON wp.ID = wm.post_id

                                    WHERE post_type = 'project'
                                    AND term_taxonomy_id = $innovation_id
                                    AND wm.meta_key = 'country_connected'
                                    AND wm.meta_value LIKE '%$code%'   
                                    AND post_status = 'publish'
                          ");
                
                
                $result[$code] = array(
                          "SCA" => $scaling
                        , "RES" => $research
                        , "INN" => $innovation
                    );
                
            }
            
            asort($result);
            echo json_encode( $result );
            
            die();
            
        }
        
        
        
}
