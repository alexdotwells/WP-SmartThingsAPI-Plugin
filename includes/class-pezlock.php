<?php

/**
 * Paddle EZ Lock Main Plugin Controller
 *
 * @package    pezlock
 * @subpackage pezlock/includes
 * @author     AJWells <ajwells99@gmail.com>
 */

class Pezlock {

	protected $loader;
	protected $plugin_name;
	protected $version;

	public function __construct() {
		$this->plugin_name = 'pezlock';
		$this->version = '1.0.0';
		$this->load_dependencies();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	private function load_dependencies() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-pezlock-loader.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-pezlock-admin.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-pezlock-options.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-pezlock-event-controller.php';

		$this->loader = new Pezlock_Loader();
	}

  private function define_admin_hooks() {
		$admin_dash = new Pezlock_Admin();
		$this->loader->add_action( 'add_meta_boxes', $admin_dash, 'pezlock_admin_add_meta_box', 20 );
    $this->loader->add_action( 'save_post', $admin_dash, 'pezlock_adminsave_post', 20 , 3 );
		$plugin_options = new Pezlock_Options();
		$this->loader->add_action( 'admin_menu', $plugin_options, 'pezlock_add_admin_menu' );
	  $this->loader->add_action( 'admin_init', $plugin_options, 'pezlock_settings_init' );
	}

	private function define_public_hooks() {
		$booking_events  = new Pezlock_Event_Controller();
		$this->loader->add_action( 'woocommerce_add_order_item_meta', $booking_events, 'pezlock_order_item_meta' , 51, 2 );
    $this->loader->add_action( 'woocommerce_thankyou', $booking_events, 'pezlock_schedule_locker_book_events' );
		$this->loader->add_action( 'pezlock_send_pin_event', $booking_events, 'pezlock_do_scheduled_resource_event', 10, 5 );
		$this->loader->add_action( 'woocommerce_booking_cancelled', $booking_events, 'pezlock_cancel_scheduled_events', 25 );
	}

	public function run() {
		$this->loader->run();
  }

  public function get_loader() {
		return $this->loader;
	}

	public function get_plugin_name() {
		return $this->plugin_name;
	}

	public function get_version() {
		return $this->version;
	}

}
