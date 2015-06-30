<?php

/**
 * The main class that powers the plugin.
 *
 * @package   expanding-archives
 * @copyright Copyright (c) 2015, Ashley Evans
 * @license   GPL2+
 */
class NG_Expanding_Archives {

	/**
	 * The single instance of the plugin.
	 * @var Naked_Social_Share
	 * @since 1.0.0
	 */
	private static $_instance = null;

	/**
	 * The version number.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_version;

	/**
	 * The token.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_token;

	/**
	 * The main plugin file.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $file;

	/**
	 * The main plugin directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $dir;

	/**
	 * The plugin assets directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_dir;

	/**
	 * The plugin assets URL.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_url;

	/**
	 * Suffix for Javascripts.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $script_suffix;

	/**
	 * Constructor function
	 *
	 * Sets up all the variables, handles localisation, and includes
	 * the widget class.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function __construct( $file = '', $version = '1.0.0' ) {
		// Load plugin environment variables.
		$this->_version = $version;
		$this->_token   = 'expanding-archives';

		$this->file       = $file;
		$this->dir        = dirname( $this->file );
		$this->assets_dir = trailingslashit( $this->dir ) . 'assets';
		$this->assets_url = esc_url( trailingslashit( plugins_url( 'assets/', $this->file ) ) );

		$this->script_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		// Include necessary files.
		$this->includes();

		// Load front end JS & CSS
		add_action( 'wp_enqueue_scripts', array( $this, 'add_scripts_styles' ) );

		// Handle localisation
		$this->load_plugin_textdomain();
		add_action( 'init', array( $this, 'load_localisation' ), 0 );

		// Ajax actions.
		add_action( 'wp_ajax_nopriv_expanding_archives_load_monthly', array( $this, 'load_monthly_archives' ) );
		add_action( 'wp_ajax_expanding_archives_load_monthly', array( $this, 'load_monthly_archives' ) );

		// Delete transient when new post is published.
		add_action( 'transition_post_status', array( $this, 'delete_transient' ), 10, 3 );
	}

	/**
	 * Sets up the main NG_Expanding_Archives instance
	 *
	 * @access public
	 * @since  1.0.0
	 * @return NG_Expanding_Archives
	 */
	public static function instance( $file = '', $version = '1.0.0' ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $file, $version );
		}

		return self::$_instance;
	}

	/**
	 * Cloning is not allowed
	 *
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function __clone() {
		// Cloning instances of the class is forbidden
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', $this->_token ), $this->_version );
	}

	/**
	 * Disable unserializing of the class
	 *
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function __wakeup() {
		// Unserializing instances of the class is forbidden
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', $this->_token ), $this->_version );
	}

	/**
	 * Includes the sidebar widget.
	 *
	 * @access private
	 * @since  1.0.0
	 * @return void
	 */
	private function includes() {
		require_once $this->dir . '/inc/widget-expanding-archives.php';
	}

	/**
	 * Load the plugin language files.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function load_plugin_textdomain() {
		$locale = apply_filters( 'plugin_locale', get_locale(), $this->_token );

		load_textdomain( $this->_token, WP_LANG_DIR . '/' . $this->_token . '/' . $this->_token . '-' . $locale . '.mo' );
		load_plugin_textdomain( $this->_token, false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	}

	/**
	 * Load plugin localisation
	 *
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_localisation() {
		load_plugin_textdomain( $this->_token, false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	}

	/**
	 * Adds the CSS and JavaScript for the plugin.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function add_scripts_styles() {
		// Load our CSS.
		wp_register_style( $this->_token, esc_url( $this->assets_url ) . 'css/expanding-archives.css', array(), $this->_version );
		wp_enqueue_style( $this->_token );

		// Load our JavaScript.
		wp_register_script( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'js/expanding-archives' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version, true );
		wp_enqueue_script( $this->_token . '-frontend' );
		$data = array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'expand_archives' )
		);
		wp_localize_script( $this->_token . '-frontend', 'expanding_archives', $data );
	}

	/**
	 * Gets a list of all the posts in the current month.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return string
	 */
	public function get_current_month_posts() {
		$output = '';

		$saved_posts = get_transient( 'expanding_archives_current_month_posts' );

		if ( $saved_posts === false || empty( $saved_posts ) ) {
			$year  = date( 'Y' );
			$month = date( 'm' );

			// Query the posts.
			$archives = get_posts( array(
				'posts_per_page' => - 1,
				'nopaging'       => true,
				'year'           => intval( $year ),
				'monthnum'       => intval( $month ),
			) );

			// If we have results, add each one to a list.
			if ( $archives ) {
				$output .= '<ul>';
				foreach ( $archives as $archive ) {
					$output .= '<li><a href="' . get_permalink( $archive ) . '">' . get_the_title( $archive ) . '</a></li>';
				}
				$output .= '</ul>';
			} else {
				$output = '<ul><li>' . __( 'None yet.', $this->_token ) . '</li></ul>';
			}

			// Set the transient so that this value is cached.
			set_transient( 'expanding_archives_current_month_posts', $output, DAY_IN_SECONDS );
		} else {
			$output = $saved_posts;
		}

		return $output;
	}

	/**
	 * Gets a list of all the posts in a given month/date via ajax.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return string
	 */
	public function load_monthly_archives() {
		// Security check.
		check_ajax_referer( 'expand_archives', 'nonce' );

		$month = strip_tags( $_POST['month'] );
		$year  = strip_tags( $_POST['year'] );

		// Query for posts in the given month/year.
		$archives = get_posts( array(
			'posts_per_page' => - 1,
			'nopaging'       => true,
			'year'           => intval( $year ),
			'monthnum'       => intval( $month ),
		) );

		// If we have results, add each one to our list.
		if ( $archives ) {
			$result = '<ul>';
			foreach ( $archives as $archive ) {
				$result .= '<li><a href="' . get_permalink( $archive ) . '">' . get_the_title( $archive ) . '</a></li>';
			}
			$result .= '</ul>';
			wp_send_json_success( $result );
		} else {
			$result = '<ul><li>' . __( 'No posts found.', $this->_token ) . '</li></ul>';
			wp_send_json_success( $result );
		}

		exit;
	}

	/**
	 * Deletes our transient of posts in the current month when
	 * a new post is published.
	 *
	 * @param string  $new_status
	 * @param string  $old_status
	 * @param WP_Post $post
	 *
	 * @access public
	 * @since  2.0.0
	 * @return void
	 */
	public function delete_transient( $new_status, $old_status, $post ) {
		// Delete our transient.
		if ( $new_status == 'publish' ) {
			delete_transient( 'expanding_archives_current_month_posts' );
		}
	}

}