<?php
/*
 * Plugin Name: RGB Banners
 * Plugin URI: http://www.rgbmedia.org
 * Description: Bespoke banner image sizes.
 * Version: 1.1
 * Author: RGB Media Ltd
 * Author URI: http://www.rgbmedia.org
 * Licence: GPL
 * Requires at least: 3.0
 * Tested up to: 4.4
 */

/*
 * Copyright 2016 RGB Media Ltd
 *
 */

$rgb_banners = RGB_Banners::instance();

class RGB_Banners {

	/**
	 * Allowed mime types
	 * @var array
	 */
	public static $mimes = array(
		'jpg|jpeg|jpe' => 'image/jpeg',
		'gif'          => 'image/gif',
		'png'          => 'image/png',
	);

	/**
	 * Singleton intance
	 * @var RGB_Banners
	 */
	private static $instance;

	/**
	 * Uri to the plugin folder
	 * @var string
	 */
	private static $plugin_url;

	/**
	 * Absolute path to the plugin folder
	 * @var string
	 */
	private static $plugin_path;

	private static $post_types = array( 'banners' );

	/**
	 * banners sizes
	 * @var array
	 */
	private static $banners_sizes = array();

	private $error;

	/**
	 * Private constructor (singleton)
	 */
	private function __construct() {

		self::$plugin_url  = plugins_url( '', __FILE__ );
		self::$plugin_path = dirname( __FILE__ );

		// add default banners sizes
		self::add_image_size( 'b1x', 215, 420, true );
		self::add_image_size( 'b1x_short', 215, 300, true );
		self::add_image_size( 'b1x_square', 215, 215, true );
		self::add_image_size( 'b2x', 470, 420, true );
		self::add_image_size( 'b2x_short', 470, 300, true );
		self::add_image_size( 'b3x', 725, 420, true );
		self::add_image_size( 'b3x_short', 725, 300, true );
		self::add_image_size( 'b4x', 980, 420, true );
		self::add_image_size( 'b4x_short', 980, 300, true );
		self::add_image_size( 'slider', 325, 230, true );
		self::add_image_size( 'cover', 1280, 380, true );

		register_activation_hook( __FILE__, array( $this, 'activation_hook' ) );

		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ), 5 );
		add_action( 'after_setup_theme', array( $this, 'after_setup_theme' ), 5 );
		add_action( 'admin_init', array( $this, 'admin_init' ) );

	}

	/**
	 * Registers a new post banner size
	 *
	 * @param string $name
	 * @param int $width
	 * @param int $height
	 * @param bool $crop
	 */
	public static function add_image_size( $name, $width = 0, $height = 0, $crop = false ) {
		self::$banners_sizes[ $name ] = array(
			'width'  => absint( $width ),
			'height' => absint( $height ),
			'crop'   => (bool) $crop
		);
	}

	/* =Manages post_types posts image support
	----------------------------------------------- */

	/**
	 * Returns the singleton instance
	 * @return RGB_Banners
	 */
	public static function instance() {

		if ( ! isset( self::$instance ) ) {
			$class_name     = __CLASS__;
			self::$instance = new $class_name;
		}

		return self::$instance;

	}

	/**
	 * Adds banner support for the specified post_type
	 *
	 * @param string $post_type
	 */
	public static function add_post_type_support( $post_type ) {

		$post_types       = (array) $post_type;
		self::$post_types = array_merge( self::$post_types, $post_types );

	}

	/**
	 * Removes banner support for the specified post_type
	 *
	 * @param string $post_type
	 */
	public static function remove_post_type_support( $post_type ) {

		$key = array_search( $post_type, self::$post_types );
		if ( false !== $key ) {
			unset( self::$post_types[ $key ] );
		}

	}

	/* =Manages posts banners sizes
	 ----------------------------------------------- */

	/**
	 * Sets the default banner size
	 *
	 * @param int $width
	 * @param int $height
	 * @param bool $crop
	 */
	public static function set_banner( $width = 0, $height = 0, $crop = false ) {
		self::add_image_size( 'banner', $width, $height, $crop );
	}

	/**
	 * Returns true if the specified post has a banner image for the specified category and size
	 *
	 * @param int $post_id
	 * @param string $size
	 */

	public static function has_post_banner( $post_id, $post_type, $size = null ) {

		$image_infos = self::get_post_image_infos( $post_id, $post_type );

		if ( empty( $image_infos ) ) {
			return false;
		} elseif ( ! $size ) {
			return true;
		}

		if ( isset ( $image_infos['banners'][ $size ] ) ) {
			return true;
		}

		return false;
	}


	/* =Static functions to display posts banners
	 ----------------------------------------------- */

	/**
	 * Return a post's banner meta data for the specified post_type
	 *
	 * @param int $post_id
	 * @param string $post_type
	 *
	 * @return array
	 */
	public static function get_post_image_infos( $post_id, $post_type, $is_on_save = false ) {

		$meta_data = false;
		$post_id = $post_id;
		if (is_object($post_type) ) {
			$post_type = $post_type->post_type;
		}
		if (is_array($post_type) ) {
			$post_type = $post_type['post_type'];
		}
		if ( $post_type ) {
			$meta_data = get_post_meta( $post_id, 'image-' . $post_type, true );
		}

		// compatibility with beta1
		if ( ! $meta_data ) {
			$meta_data = get_post_meta( $post_id, 'image', true );
		}
		// var_dump(get_post_meta($post_id,'cmb_banners_mode_select',true));
		$as_is_mode = get_post_meta($post_id,'cmb_banners_mode_select',true);
		if(!$is_on_save && $as_is_mode == 'as_is_mode'){


		$field_names = array( 'b1x', 'b1x_short',/* 'b1x_square',*/ 'b2x', 'b2x_short', 'b3x', 'b3x_short', 'b4x', 'b4x_short', 'slider'/*, 'cover'*/ );
			$banners = array();
			$image_full_image_id = '';
			$image_full_image_path = '';
			$image_full_image_data = '';
			$image_full_width = '';
			$image_full_height = '';
			$image_full_mime_type = '';
			$image_full_banner_url = '';
			
			foreach ($field_names as $field_name) {
				$banner_url = get_post_meta($post_id, $field_name . '_banner', true);
				if ($banner_url) {
					//$image_id = attachment_url_to_postid($banner_url);
					$image_id = get_post_meta($post_id, $field_name . '_banner_id', true);
					$image_path = get_attached_file($image_id);
					$image_data = wp_get_attachment_metadata($image_id);
					$width = '100%';
					$height = 'auto';
						$height = $image_data['height'];
					if ($image_data && isset($image_data['width'], $image_data['height'],$image_data['file'])) {
						$width = $image_data['width'];
						$height = $image_data['height'];
						$mime_type = wp_check_filetype($image_data['file'], null);
					}
			
					$banner = array(
						'name' => basename($image_path),
						'path' => $image_path,
						'infos' => array(
							'100%',
							'auto',
							'bits' => 8,
							'channels' => 3,
							$mime_type['type']
						),
						'url' => $banner_url,
					);
					$banners[$field_name] = $banner;


					if($field_name == 'b1x'){
						$image_full_image_id = $image_id;
						$image_full_image_path = $image_path;
						$image_full_image_data = $image_data;
						$image_full_width = $width;
						$image_full_height = $height ;
						$image_full_mime_type = $mime_type;
						$image_full_banner_url =  $banner_url;

					}
				}
			}
			$banner_data = array(
				"name" => basename($image_full_image_path),
				"size" => filesize($image_full_image_path),
				"path" => $image_full_image_path,
				"url" => $image_full_banner_url,
				'type' => $image_full_mime_type['type'],
				"ext" => $image_full_mime_type['ext'],
				"infos" => array(
					$image_full_width,
					$image_full_height,
					2,
					"width=\"$image_full_width\" height=\"$image_full_height\"",
					8,
					3,
					$image_full_mime_type['type']
				),
				"banners" => $banners
			);

			
 			$meta_data = $banner_data;
		}
		return $meta_data;
	}

	/**
	 * Returns the specified post's banner's HTML code for the specified post_type and size
	 *
	 * @param int $post_id
	 * @param string $post_type
	 * @param string $size
	 * @param array $attr
	 */
	public static function get_the_post_banner( $post_id, $post_type, $size = 'banner', $attr = '' ) {

		$size = apply_filters( 'post_banner_size', $size, $post_id, $post_type );

		$image = self::get_post_banner( $post_id, $post_type, $size );

		$post = get_post( $post_id, $post_type );

		if ( $image ) {
			do_action( 'begin_fetch_post_banner_html', $post_id, $post_type, $image, $size );

			list( $src, $width, $height ) = $image;
			$hwstring = image_hwstring( $width, $height );

			if ( is_array( $size ) ) {
				$size = $width . 'x' . $height;
			}

			$default_attr = array(
				'src'         => $src,
				'class'       => "attachment-$size $post_type-banner",
				'alt'         => trim( strip_tags( $post->name ) ), // Use Alt field first
				'title'       => trim( strip_tags( $post->name ) ),
			);

			$attr = wp_parse_args( $attr, $default_attr );
			$attr = apply_filters( 'get_the_post_banner_attributes', $attr, $post_id, $post_type, $image, $size );
			$attr = array_map( 'esc_attr', $attr );
			$html = rtrim( "<img $hwstring" );
			foreach ( $attr as $name => $value ) {
				$html .= " $name=" . '"' . $value . '"';
			}
			$html .= ' />';
			do_action( 'end_fetch_post_banner_html', $post_id, $post_type, $image, $size );

		} else {
			$html = '';
		}

		return apply_filters( 'post_banner_html', $html, $post_id, $post_type, $image, $size, $attr );
	}

	/**
	 * Returns the specified post's banner for the specified post_type and size
	 *
	 * @param int $post_id
	 * @param string $post_type
	 * @param string $size
	 */
	public static function get_post_banner( $post_id, $post_type, $size = null ) {

		$infos = self::get_post_image_infos( $post_id, $post_type );
		if ( ! $infos ) {
			return false;
		}

		if ( ! $size ) {
			return array( $infos['url'], $infos['infos'][0], $infos['infos'][1] );
		}

		if ( is_array( $size ) ) {
			/*
			 * @TODO here we need to fing the banner nearest to the asked size
			 */
		}

		if ( ! isset( $infos['banners'][ $size ] ) ) {
			return false;
		}
		$infos = $infos['banners'][ $size ];

		return array( $infos['url'], $infos['infos'][0], $infos['infos'][1] );

	}

	/**
	 * Manage posts image meta
	 */

	/**
	 * Checks PHP version and create the needed database table on plugin activation
	 */
	public function activation_hook() {

		// checks the PHP version
		if ( version_compare( PHP_VERSION, '5.0.0', '<' ) ) {
			deactivate_plugins( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) ); // Deactivate ourself
			wp_die( "Sorry, the RGB Banners plugin requires PHP 5 or higher." );
		}

	}

	/* =Manages the posts images metadata
	 ----------------------------------------------- */

	/**
	 * Filters default banners sizes and supported post_types
	 * Runs on the plugins_loaded action hook
	 */
	public function plugins_loaded() {
		self::$post_types       = apply_filters( 'posts-banners-default-sizes', self::$post_types );
		self::$banners_sizes 	= apply_filters( 'posts-banners-default-sizes', self::$banners_sizes );
	}

	/**
	 * Filters default banners sizes and supported post_types
	 * Runs on the after_setup_theme action hook
	 */
	public function after_setup_theme() {
		self::$post_types       = apply_filters( 'posts-banners-post_types', self::$post_types );
		self::$banners_sizes 	= apply_filters( 'posts-banners-sizes', self::$banners_sizes );
	}

	/* =Static functions to manage posts images
	 ----------------------------------------------- */

	/**
	 * Return true if the specified post_type has posts banners support
	 *
	 * @param string $post_type
	 *
	 * @return bool
	 */
	public static function has_support( $post_type ) {

		if ( in_array( $post_type, self::$post_types ) ) {
			return true;
		}

		return false;

	}

	/**
	 * Init the admin
	 * Runs on the admin_init action hook
	 */
	public function admin_init() {

		// adds scripts and css
		add_action( 'admin_head-post.php', array( $this, 'admin_head' ) );
		add_action( 'admin_head-post-new.php', array( $this, 'admin_head' ) );

		// show our admin notices
		add_action( 'admin_notices', array( $this, 'admin_notice' ) );

		// adds/removes our errors var to url on redirect
		add_filter( 'wp_redirect', array( $this, 'wp_redirect' ) );

		// add a file field to posts add and edit form
		add_action( 'add_meta_boxes', array( $this, 'add_banners_metabox' ) );

		// save image on post save
		add_action( "save_post", array( $this, 'process_upload' ), 10, 2 );
		add_action( "save_post", array( $this, 'set_banner_post_title' ), 12 );
		add_action( "save_post", array( $this, 'set_banner_style' ) );

		// generate banners after a post is saved
		add_action( "save_post", array( $this, 'generate_banners_action' ), 15, 2 );

		// delete image and banners of deleted posts
		add_action( "before_delete_post", array( $this, 'delete_banner' ), 5, 3 );

		// add images column to posts list-table
		add_filter( "manage_edit-banners_columns", array( $this, 'edit_columns' ) );
		add_filter( "manage_banners_posts_custom_column", array( $this, 'columns' ), 10, 3 );

		add_action( 'wp_ajax_delete_post_image', array( $this, 'ajax_delete_post_image' ) );

	}

	/**
	 * Store post title
	 */
	function set_banner_post_title ($post_id) {
		if ( $post_id == null || empty($_POST) )
			return;
	
		if ( !isset( $_POST['post_type'] ) || $_POST['post_type']!='banners' )  
			return; 
	
		if ( wp_is_post_revision( $post_id ) )
			$post_id = wp_is_post_revision( $post_id );
	
		global $post;  
		if ( empty( $post ) )
			$post = get_post($post_id);
	
		if ($_POST['banner_title']!='') {
			global $wpdb;
			$where = array( 'ID' => $post_id );
			$wpdb->update( $wpdb->posts, array( 'post_title' => stripslashes($_POST['banner_title']) ), $where );
		}
	}

	/**
	 * Set banner style
	 */
	public function set_banner_style( $post_id ) {
		if ( isset( $_GET['style'] ) ) {
			update_post_meta( $post_id, 'banner-style', $_GET['style'] );
		}
	}

	/**
	 * css and script on the admin posts forms
	 * Runs on the admin_head-edit-tags.php action hook
	 */

	public function admin_head() {
		if ( !empty( $_GET['post'] ) && self::has_support( get_post_type($_GET['post']) ) ) {
		?>
		<script type="text/javascript">
			<!--
			jQuery(document).ready(function ($) {
			
				var nonce = '<?php echo wp_create_nonce( "delete_post_image" ) ?>';
			
				$('#delete-thumb-button').click(
					function () {
						$.post(ajaxurl, {
							post_id: <?php echo esc_js($_GET['post']) ?>,
							post_type: 'banners',
							action: 'delete_post_image',
							_nonce: nonce
						}, function (data) {
							if (data == '1') $('#post_banner_img').hide('slow');
						});
					}
				);
			});
			//-->
		</script><?php
		}
		
		if ( 
			(isset( $_GET['post_type'] ) && self::has_support( $_GET['post_type'] ) ) 
				|| 
			( !empty( $_GET['post'] ) && self::has_support( get_post_type($_GET['post']) ) ) 
		   )	{
			?>	
		<script type="text/javascript">
			<!--
			jQuery(document).ready(function ($) {
				 $("#image").change(function (){
				 	$('#post_banner_img').html('<div style="text-align:center;width:100%;"><img src="/wp-content/themes/rgb/images/jb-load.gif" width="40" height="40" /></div>');
				 	$('#file').hide();
				 	$("input[name='save']").click();
				 });				
			});
			//-->
		</script>			
			<?php
		}
	}

	/**
	 * Shows errors in admin
	 * Runs on the admin_notices action hook
	 */
	public function admin_notice() {

		if ( empty( $_GET['post_image_error'] ) ) {
			return;
		}

		$error = unserialize( base64_decode( $_GET['post_image_error'] ) );

		if ( ! is_wp_error( $error ) ) {
			return;
		}

		echo '<div class="error">
			  <p><strong>' . __( 'Image upload error: ', 'rgb_posts_ordering' ) . '</strong>' . $error->get_error_message() . '</p>
			  </div>';
	}

	/**
	 * On wp_redirect, we add/remove our errors var as needed
	 *
	 * @param string $location
	 * Runs on the wp_redirect filter hook
	 */
	public function wp_redirect( $location ) {

		$location = remove_query_arg( 'post_image_error', $location );

		if ( ! $this->error ) {
			return $location;
		}

		$location = add_query_arg( 'post_image_error', urlencode( base64_encode( serialize( $this->error ) ) ), $location );

		return $location;
	}

	/* =Misc static functions
	----------------------------------------------- */

	/**
	 * Adds a field to the "Add post" form
	 *
	 * @param string $post_type
	 * Runs on the add_meta_boxes action hook
	 */
	public function add_banners_metabox() {
		add_meta_box(
			'post_banner',
			'Banner Image',
			array( $this, 'render_banner_meta_box'),
			array('banners'),
			'normal',
			'high'
		);
	}
	
	public function render_banner_meta_box( $post ) {	
			
		$post_id = $post->ID;
		$post_type = $post->post_type;

		$current          = self::get_post_image_infos( $post_id, $post_type );
		$upload_size_unit = $max_upload_size = wp_max_upload_size();
		$sizes            = array( 'KB', 'MB', 'GB' );
		for ( $u = - 1; $upload_size_unit > 1024 && $u < count( $sizes ) - 1; $u ++ ) {
			$upload_size_unit /= 1024;
		}
		if ( $u < 0 ) {
			$upload_size_unit = 0;
			$u                = 0;
		} else {
			$upload_size_unit = (int) $upload_size_unit;
		}

		?>
		<h4>Add/Replace your banner image -- choose an image from your computer (minimum 1280px wide)</h4>
		<div>
			<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $max_upload_size ?>"/>
			<input type="file" id="image" name="image" /><br/>
		</div>
		<div id="post_banner_img" style="width:100%;overflow:hidden;">
			<?php 
			if ( has_post_banner( $post_id, $post_type ) ) :
				$banner_style = get_post_meta( $post_id, 'banner-style', true );
				$link = (get_post_meta($post_id, 'banner_link', true)!='') ? get_post_meta($post_id, 'banner_link', true) : '#';
				// {JewishPhoenix} Develop "as is" banner mode
				$as_is_mode = get_post_meta($post_id,'cmb_banners_mode_select',true);
				if($as_is_mode == 'as_is_mode'){
					$link = (get_post_meta($post_id, 'as_is_banner_url', true)!='') ? get_post_meta($post_id, 'as_is_banner_url', true) : '#';  
				}
			?>
				<div class="banner b3x short">
					<div class="media">
						<a href="<?php echo $link; ?>"><?php the_post_banner( $post_id, $post_type, 'b3x_short' ); ?></a>
					</div>
					<div class="content<?php echo (get_post_meta($post_id, 'banner_filter', true)!='') ? ' gradient-' . get_post_meta($post_id, 'banner_filter', true) : ''; echo $banner_style ? ' style-' . $banner_style : ''; ?>" data-color="<?php echo (get_post_meta($post_id, 'banner_filter', true)!='') ? get_post_meta($post_id, 'banner_filter', true) : 'magenta'; ?>">
						<?php if ( ! $banner_style ) : ?>
							<a class="sponsored" href="<?php echo $link; ?>"><?php echo (get_post_meta($post_id, 'banner_label', true)!='') ? get_post_meta($post_id, 'banner_label', true) : '&nbsp;'; ?></a>
							<a class="icon" href="<?php echo $link; ?>"><?php echo (get_post_meta($post_id, 'banner_emoji', true)!='') ? '&#x' . get_post_meta($post_id, 'banner_emoji', true) . ';' : '&nbsp;'; ?></a>
							
							<a<?php if (get_post_meta($post_id, 'banner_overline', true)!='on') echo ' style="display:none;"'; ?> class="overline" href="<?php echo $link; ?>"><?php echo (get_post_meta($post_id, 'banner_underline', true)!='') ? get_post_meta($post_id, 'banner_underline', true) : '&nbsp'; ?></a>
							<a class="headline" href="<?php echo $link; ?>"><?php echo (get_post_meta($post_id, 'banner_title', true)!='') ? get_post_meta($post_id, 'banner_title', true) : '&nbsp;'; ?></a>
							
							<a<?php if (get_post_meta($post_id, 'banner_overline', true)=='on') echo ' style="display:none;"'; ?> class="underline" href="<?php echo $link; ?>"><?php echo (get_post_meta($post_id, 'banner_underline', true)!='') ? get_post_meta($post_id, 'banner_underline', true) : '&nbsp'; ?></a>
							<a<?php if ((get_post_meta($post_id, 'banner_button', true)=='')) echo ' style="display:none;"'; ?> class="banner-button" href="<?php echo $link; ?>"><?php echo (get_post_meta($post_id, 'banner_button', true)!='') ? get_post_meta($post_id, 'banner_button', true) : '&nbsp;'; ?></a>
							<a class="bottom" href="<?php echo $link; ?>"><?php echo (get_post_meta($post_id, 'banner_credit', true)) ? get_post_meta($post_id, 'banner_credit', true) : '&nbsp;'; ?></a>
						<?php endif; ?>
						<?php if ( $banner_style === 'long' ) : ?>
							<a class="headline" href="<?php echo $link; ?>"><?php echo (get_post_meta($post_id, 'banner_title', true)!='') ? get_post_meta($post_id, 'banner_title', true) : '&nbsp;'; ?></a>
							<a<?php if ((get_post_meta($post_id, 'banner_intro', true)=='')) echo ' style="display:none;"'; ?> class="banner-intro" href="<?php echo $link; ?>"><?php echo get_post_meta($post_id, 'banner_intro', true) ?></a>
							<a<?php if ((get_post_meta($post_id, 'banner_button', true)=='')) echo ' style="display:none;"'; ?> class="banner-button" href="<?php echo $link; ?>"><?php echo (get_post_meta($post_id, 'banner_button', true)!='') ? get_post_meta($post_id, 'banner_button', true) : '&nbsp;'; ?></a>
						<?php endif; ?>
					</div>
				</div>
			<?php endif; ?>
		</div>		
		<script type="text/javascript">
			<!--
			jQuery('#post').attr('enctype', 'multipart/form-data').attr('encoding', 'multipart/form-data');
			//-->
		</script>
		<?php
	}

	/**
	 * Process the image upload
	 * Runs on the save_post action hook
	 *
	 * @param int $post_id
	 *
	 * @return stdClass|boolean:
	 */
	public function process_upload( $post_id ) {

		// get the post_type and check that it supports images
		if ( empty( $_POST['post_type'] ) || ! self::has_support( $_POST['post_type'] ) ) {
			return;
		}

		$post_type = $_POST['post_type'];
		if ( ! self::has_support( $post_type ) ) {
			return $post;
		}

		$file = isset( $_FILES['image'] ) ? $_FILES['image'] : null;
		if ( ! $file ) {
			return $post;
		}

		/* create the post_type directory if needed */
		if ( ! $dir = self::images_mkdir( $post_type ) ) {
			return $this->upload_error( $file, __( "Permission error creating the posts-images/{post_type} folder.", 'rgb-posts-banners' ) );
		}

		// Courtesy of php.net, the strings that describe the error indicated in $_FILES[{form field}]['error'].
		$upload_error_strings = array(
			false,
			__( "The uploaded file exceeds the <code>upload_max_filesize</code> directive in <code>php.ini</code>." ),
			__( "The uploaded file exceeds the <em>MAX_FILE_SIZE</em> directive that was specified in the HTML form." ),
			__( "The uploaded file was only partially uploaded." ),
			__( "No file was uploaded.", 'rgb-posts-banners' ),
			'',
			__( "Missing a temporary folder." ),
			__( "Failed to write file to disk." ),
			__( "File upload stopped by extension." )
		);

		if ( $file['error'] > 0 && $file['error'] !== 4 ) {
			return $this->upload_error( $file, $upload_error_strings[ $file['error'] ] );
		}

		if ( isset( $file['error'] ) && ! is_numeric( $file['error'] ) && $file['error'] && $file['error'] !== 4 ) {
			return $this->upload_error( $file, $file['error'] );
		}

		// A non-empty file will pass this test.
		if ( ! ( $file['size'] > 0 ) ) {
			return $post;
		}

		// A properly uploaded file will pass this test.
		if ( ! @ is_uploaded_file( $file['tmp_name'] ) ) {
			return $this->upload_error( $file, __( 'Specified file failed upload test.' ) );
		}

		// mime check
		$wp_filetype = wp_check_filetype_and_ext( $file['tmp_name'], $file['name'], self::$mimes );
		extract( $wp_filetype );

		if ( ( ! $type || ! $ext ) && ! current_user_can( 'unfiltered_upload' ) ) {
			return $this->upload_error( $file, __( 'Sorry, this file type is not permitted for security reasons.' ) );
		}

		/* delete old image if it exists */
		if ( false === self::remove_post_image( $post_id, $post_type, true ) ) {
			@ unlink( $new_file );

			return $this->upload_error( $file, __( 'An error occurred when trying to remove the old image.', 'rgb-posts-banners' ) );
		}

		if ( $proper_filename ) {
			$file['name'] = $proper_filename;
		}

		if ( ! $ext ) {
			$ext = ltrim( strrchr( $file['name'], '.' ), '.' );
		}

		if ( ! $type ) {
			$type = $file['type'];
		}

		/* get a unique filename */


		$filename = wp_unique_filename( $dir, $file['name'] );

		// Move the file to the uploads dir
		$new_file = $dir . "/$filename";

		/* moves uploaded file */
		if ( false === @ move_uploaded_file( $file['tmp_name'], $new_file ) ) {
			return

				$file_infos = array();
		}
		$file_infos ['name']       = $filename;
		$file_infos ['size']       = $file['size'];
		$file_infos ['path']       = $new_file;
		$file_infos ['url']        = self::images_url() . '/' . $post_type . '/' . $filename;
		$file_infos ['type']       = $type;
		$file_infos ['ext']        = $ext;
		$file_infos ['infos']      = getimagesize( $new_file );
		$file_infos ['banners'] = array();

		self::update_post_image_infos( $post_id, $post_type, $file_infos );

	}

	/**
	 * Make a directory
	 *
	 * @param string $post_type optional category name to create a taxnonomy directory
	 */
	public static function images_mkdir( $post_type = '' ) {

		global $wp_filesystem;
		WP_Filesystem();

		$dir = self::images_dir() . ( $post_type ? '/' . $post_type : '' );

		if ( ! wp_mkdir_p( $dir ) && ! is_dir( $dir ) ) // Only check to see if the Dir exists upon creation failure. Less I/O this way.
		{
			wp_die( __( 'Could not create directory.' ) );
		}

		return $dir;
	}

	/**
	 * Returns the absolute path to the banners folder
	 * @return string
	 */
	public static function images_dir() {
		$upload_dir_infos = wp_upload_dir();

		return $upload_dir_infos['basedir'];
	}

	/**
	 * Handles upload errors
	 *
	 * @param array $file
	 * @param $message $message
	 */
	private function upload_error( &$file, $message ) {
		$this->error = new WP_Error( 'invalid_upload', $message, $file );

		return false;
	}

	/**
	 * Remove a post's image (and its banners)
	 *
	 * @param int $post_id
	 * @param string $post_type
	 */
	public static function remove_post_image( $post_id, $post_type, $is_on_save = false ) {

		$infos = self::get_post_image_infos( $post_id, $post_type, $is_on_save );

		if ( ! $infos ) {
			return;
		}

		if ( ! empty( $infos ) && isset( $infos['path'] ) ) {

			if ( false === self::remove_post_banners( $post_id, $post_type ) ) {
				return false;
			}
			if ( ! @ unlink( $infos['path'] ) && file_exists( $infos['path'] ) ) {
				return false;
			}

		}

		self::delete_post_image_infos( $post_id, $post_type );

		return true;

	}

	/**
	 * Removes the generated banners of a post
	 *
	 * @param int $post_id
	 * @param string $post_type
	 *
	 * @return bool
	 */
	public static function remove_post_banners( $post_id, $post_type ) {

		$infos = self::get_post_image_infos( $post_id, $post_type );

		if ( ! $infos ) {
			return;
		}

		if ( empty( $infos['banners'] ) ) {
			return true;
		}

		foreach ( $infos['banners'] as $name => $banner ) {
			if ( false === self::remove_post_banner( $name, $post_id, $post_type ) ) {
				return false;
			}
		}

		return true;

	}

	/**
	 * Removes apost's banner
	 * @pram string $banner_name
	 *
	 * @param int $post_id
	 * @param string $post_type
	 *
	 * @return bool
	 */
	public static function remove_post_banner( $banner_name, $post_id, $post_type, $is_on_save = false ) {

		if ( $banner_name == 'admin_banner' ) {
			return;
		}

		$infos = self::get_post_image_infos( $post_id, $post_type );
		if ( ! $infos ) {
			return;
		}

		if ( empty( $infos['banners'] ) || empty( $infos['banners'][ $banner_name ] ) ) {
			return true;
		}

		$as_is_mode = ($is_on_save) ? get_post_meta($post_id,'cmb_banners_mode_select',true) : '';
		if($as_is_mode == 'as_is_mode'){
			if ( file_exists( $banner['path'] ) && ! @ unlink( $banner['path'] ) ) {
				return false;
			}
		}
		unset( $infos['banners'][ $banner_name ] );

		self::update_post_image_infos( $post_id, $post_type, $infos );

		return true;

	}

	/**
	 * Updates a post banner metadata
	 *
	 * @param int $post_id
	 * @param sttring $post_type
	 * @param array $infos
	 *
	 * @return boolean
	 */
	public static function update_post_image_infos( $post_id, $post_type, $infos ) {

		// compatibility with beta1
		if ( get_post_meta( $post_id, 'image', true ) ) {
			delete_post_meta( $post_id, 'image' );
		}

		return update_post_meta( $post_id, 'image-' . $post_type, $infos );

	}

	/**
	 * Deletes a post's banner metadata
	 *
	 * @param int $post_id
	 * @param string $post_type
	 *
	 * @return boolean
	 */
	public static function delete_post_image_infos( $post_id, $post_type ) {

		// compatibility with beta1
		if ( get_post_meta( $post_id, 'image-' . $post_type, true ) ) {
			return delete_post_meta( $post_id, 'image-' . $post_type );
		}

		// compatibility with beta1
		if ( get_post_meta( $post_id, 'image', true ) ) {
			return delete_post_meta( $post_id, 'image' );
		}

		return delete_post_meta( $post_id, 'image-' . $post_type );

	}

	/**
	 * Returns the URI to the banners folder
	 * @return string
	 */
	public static function images_url() {
		$upload_dir_infos = wp_upload_dir();

		return $upload_dir_infos['baseurl'];
	}

	/**
	 * Denerates the banners of a saved post
	 * Runs on the edited_$post_type action hook
	 *
	 * @param unknown_type $post_id
	 */
	public function generate_banners_action( $post_id ) {

		$post_type = ! empty( $_POST['post_type'] ) ? $_POST['post_type'] : 'banners';

		if ( ! self::has_support( $post_type ) ) {
			return;
		}

		self::generate_banners( $post_id, $post_type );
	}

	/**
	 * Generate a post's banners
	 *
	 * @param int $post_id
	 * @param string $post_type
	 */
	public static function generate_banners( $post_id, $post_type ) {

		$infos = self::get_post_image_infos( $post_id, $post_type );

		if ( ! $infos ) {
			return;
		}

		$banners = ! empty( $infos['banners'] ) ? $infos['banners'] : array();

		// removes obsolete banners
		foreach ( $banners as $name => $size ) {
			if ( ! isset( self::$banners_sizes[ $name ] ) ) {
				self::remove_post_banner( $name, $post_id, $post_type, true );
			}
		}

		// creates all banners images
		foreach ( self::$banners_sizes as $key => $size ) {

			if ( ! empty( $banners[ $key ] ) ) {
				self::remove_post_banner( $key, $post_id, $post_type, true );
			}

			$img = self::image_resize( $infos['path'], $size['width'], $size['height'], $size['crop'], $key );

			if ( ! $img || is_wp_error( $img ) ) {
				continue;
			}

			$file_infos           = array();
			$file_infos ['name']  = basename( $img );
			$file_infos ['path']  = $img;
			$file_infos ['infos'] = getimagesize( $img );
			$file_infos ['url']   = self::images_url() . '/' . $post_type . '/' . basename( $img );

			$infos['banners'][ $key ] = $file_infos;
		}

		self::update_post_image_infos( $post_id, $post_type, $infos );

	}

	public static function image_resize( $file, $max_w, $max_h, $crop = false, $suffix = null, $dest_path = null, $jpeg_quality = 90 ) {

		$editor = wp_get_image_editor( $file );
		if ( is_wp_error( $editor ) ) {
			return $editor;
		}
		$editor->set_quality( $jpeg_quality );

		$resized = $editor->resize( $max_w, $max_h, $crop );
		if ( is_wp_error( $resized ) ) {
			return $resized;
		}

		$dest_file = $editor->generate_filename( $suffix, $dest_path );
		$saved     = $editor->save( $dest_file );

		if ( is_wp_error( $saved ) ) {
			return $saved;
		}

		return $dest_file;
	}

	/**
	 * Remove post image on deletetion of that post
	 * Runs on the delete_post action hook
	 *
	 * @param int $post
	 * @param string $post_type
	 */
	public function delete_banner( $post, $post_type='banners' ) {
		self::remove_post_image( $post, $post_type );
	}

	/**
	 * Adds a new column to post_types supporting banners
	 * Runs on the manage_{$post_type}_posts_columns action hook
	 */
	public function edit_columns( $columns ) {
		unset( $columns["cb"] );

		$custom_array = array(
			'cb'    => '<input type="checkbox" />',
			'banner' => __( 'Banner' )
		);

		$columns = array_merge( $custom_array, $columns );

		return $columns;
	}

	/**
	 * Handles the thumbnial column content
	 * Runs on the manage_{$post_type}_columns action hook
	 */
	public function columns( $null, $column_name, $post_id='' ) {

		$post_type = isset( $_GET['post_type'] ) ? $_GET['post_type'] : '';

		if ( ! $post_type ) {
			return '';
		}

		if ( ! $post_id ) {
			return '';
		}
		
		switch ( $column_name ) {
			case 'banner':
				include ( get_template_directory() . '/functions/banners/includes/' . $post_id . '.inc' );
				return $banner_array['slider'];
				//return get_the_post_banner( $post_id, $post_type, 'b1x_square' );
				break;
		}

		return '';
	}

	/**
	 * Handles httpr post image deletion
	 * Runs on the wp_ajax_delete_post_image action hook
	 */
	public function ajax_delete_post_image() {

		$post_id  = isset( $_POST['post'] ) && (int) $_POST['post'] ? (int) $_POST['post'] : '';
		$post_type = isset( $_POST['post_type'] ) && $_POST['post_type'] ? $_POST['post_type'] : '';

		if ( ! get_post_type( $post_type ) ) {
			die( 0 );
		}

		if ( ! $post_id || ! wp_verify_nonce( $_POST['_nonce'], 'delete_post_image' ) ) {
			die( 0 );
		}

		self::remove_post_image( $post_id, $post_type );

		die( '1' );
	}

}

if ( ! function_exists( 'add_post_banners_support' ) ) {
	/**
	 * Adds banners support for the provided post_type
	 *
	 * @param string $post_type
	 */
	function add_post_banners_support( $post_type ) {
		RGB_Banners::add_post_type_support( $post_type );
	}
}

if ( ! function_exists( 'remove_post_banners_support' ) ) {
	/**
	 * Removes banners support for the provided post_type
	 *
	 * @param string $post_type
	 */
	function remove_post_banners_support( $post_type ) {
		RGB_Banners::remove_post_type_support( $post_type );
	}
}

if ( ! function_exists( 'has_post_banners_support' ) ) {
	/**
	 * Checks if the provided post_type has banners support
	 *
	 * @param string $post_type
	 *
	 * @return bool true if the post_type has thumbnial support, false otherwise
	 */
	function has_post_banners_support( $post_type ) {
		return RGB_Banners::has_support( $post_type );
	}
}

if ( ! function_exists( 'add_post_image_size' ) ) {
	/**
	 * Adds a post image size
	 *
	 * @param string $name the banner size name for reference
	 * @param unknown_type $width
	 * @param unknown_type $height
	 * @param unknown_type $crop
	 */
	function add_post_image_size( $name, $width = 0, $height = 0, $crop = false ) {
		return RGB_Banners::add_image_size( $name, $width, $height, $crop );
	}
}

if ( ! function_exists( 'set_post_banner' ) ) {
	/**
	 * Sets the default banner size
	 *
	 * @param unknown_type $width
	 * @param unknown_type $height
	 * @param unknown_type $crop
	 */
	function set_post_banner( $width = 0, $height = 0, $crop = false ) {
		return RGB_Banners::set_banner( $width, $height, $crop );
	}
}

if ( ! function_exists( 'has_post_banner' ) ) {
	/**
	 * Checks if the secified post has a banner image for the specified post_type and size
	 *
	 * @param int $post_id the post ID
	 * @param string $post_type the post_type name
	 * @param string $size the banner size
	 */
	function has_post_banner( $post_id, $post_type, $size = null ) {
		return RGB_Banners::has_post_banner( $post_id, $post_type, $size );
	}
}

if ( ! function_exists( 'the_post_banner' ) ) {
	/**
	 * Prints the specified post's banner HTML code for the specified post_type and size
	 *
	 * @param int $post_id the post ID
	 * @param string $post_type the post_type name
	 * @param string $size the banner size
	 * @param array $attr additionnal attributes
	 */
	function the_post_banner( $post_id, $post_type, $size = 'banner', $attr = '' ) {
		echo RGB_Banners::get_the_post_banner( $post_id, $post_type, $size, $attr );
	}
}

if ( ! function_exists( 'get_the_post_banner' ) ) {
	/**
	 * Returns the specified post's banner HTML code for the specified post_type and size
	 *
	 * @param int $post_id the post ID
	 * @param string $post_type the post_type name
	 * @param string $size the banner size
	 * @param array $attr additionnal attributes
	 */
	function get_the_post_banner( $post_id, $post_type, $size = 'banner', $attr = '' ) {
		return RGB_Banners::get_the_post_banner( $post_id, $post_type, $size, $attr );
	}
}

if ( ! function_exists( 'get_post_banner' ) ) {
	/**
	 * Returns the specified post's banner for the specified post_type and size
	 *
	 * @param int $post_id the post ID
	 * @param string $post_type the post_type name
	 * @param string $size the banner size
	 * @param array $attr additionnal attributes
	 */
	function get_post_banner( $post_id, $size ) {
		return RGB_Banners::get_post_banner( $post_id, $post_type, $size );
	}
}