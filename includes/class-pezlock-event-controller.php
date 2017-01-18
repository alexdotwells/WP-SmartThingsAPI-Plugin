<?php

/**
 * Paddle EZ Lock Event Controller
 *
 * @package    pezlock
 * @subpackage pezlock/includes
 * @author     AJWells <ajwells99@gmail.com>
 */

class Pezlock_Event_Controller {

  private $debug;
  private $options;
  private $pezlock_booking;
  private $product;

	public function __construct() {
    $options = get_option( 'pez_settings' );
    $this->debug = isset($options['pez_debug_enabled']) ? $options['pez_debug_enabled'] : '';
	}

  /**
   * Add child booking ids to parent booking data
   * (faster than querying the db twice to find order_items and their booking_ids)
   */
  function pezlock_order_item_meta( $item_id, $values ) {
    global $wpdb;
		if ( ! empty( $values['booking'] ) ) {
      wc_add_order_item_meta( $item_id, __( 'child_bookings', 'woocommerce-bookings' ), $values['booking']['_child_bookings'] );
    }
  }

  /**
   * Schedule resource booking events
   */
  function pezlock_schedule_locker_book_events( $order_id ) {
    global $wpdb;

    $product;
    $order    = wc_get_order( $order_id );
    $bookings = array();
    $send_pin_now   = false;
    $child_bookings = array();
    $resource_name_str = '';

    $options = get_option( 'pez_settings' );
    $put_url = isset($options['pez_lock_put_url']) ? $options['pez_lock_put_url'] : '';
    $auth    = isset($options['pez_lock_authoriation']) ? $options['pez_lock_authoriation'] : '';
    $user    = isset($options['pez_lock_user']) ? $options['pez_lock_user'] : '';


    if ( $this->debug ) { //debug
      $pin = 1234;
    } else {
      $pin = $wpdb->get_var("SELECT meta_value FROM wp_postmeta where meta_key = 'pin' ORDER BY post_id DESC limit 1");
    }

    foreach ( $order->get_items() as $order_item_id => $item ) {
      $_product = $order->get_product_from_item( $item );
      $product  = $_product;
      $bookings = array_merge( $bookings, $wpdb->get_col( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_booking_order_item_id' AND meta_value = %d", $order_item_id ) ) );
      $bookings = array_merge( $bookings, maybe_unserialize( $item['child_bookings'] ) );
    }

    foreach ( $bookings as $booking_id ) {
			$booking     = get_wc_booking( $booking_id );
      $product     = wc_get_product( $booking->product_id );
      $resource    = $product->get_resource( $booking->resource_id );
      $resource_id = $resource->get_id();
      $device_id   = get_post_meta( $resource_id, '_resource_lock_device_id', true );

      $send_pin_now = $this->maybe_send_now( $booking );
      if ( $send_pin_now ) {
        $this->do_locker_curl( $pin, $device_id, $put_url, $auth, $user );

        $unlock_time_2 = strtotime( "+10 seconds", current_time('timestamp') );  //+10 seconds
        $unlock_time_2_gmt = strtotime( "+" . (string)(get_option( 'gmt_offset' ) * -1) . "Hours", $unlock_time_2 );

        if ( $this->debug ) { //debug
          print_r( 'Cron Event 2 Time : ' . date("Y-m-d H:i:s", $unlock_time_2_gmt) . ' <br /> Offset is: ' . (get_option( 'gmt_offset' ) * -1) . ' hours <br /> ------------------------------ <br />');
        }

        wp_schedule_single_event( $unlock_time_2_gmt , 'pezlock_send_pin_event', array( (int)$booking_id, (int)$booking->product_id, (int)$resource_id, $pin, (int)$user+1 ) );
        usleep(1.25 * 1000000);

      } else {
        $unlock_time = strtotime( "-5 Minutes 5 seconds", $booking->start );
        $unlock_time_gmt = strtotime( "+" . (string)(get_option( 'gmt_offset' ) * -1) . "Hours", $unlock_time );

        $unlock_time_2 = strtotime( "+10 seconds", $unlock_time );
        $unlock_time_2_gmt = strtotime( "+" . (string)(get_option( 'gmt_offset' ) * -1) . "Hours", $unlock_time_2 );

        if ( $this->debug ) { //debug
          print_r( 'Cron Event 1 Time: ' . date("Y-m-d H:i:s", $unlock_time_gmt) . ' <br />');
          print_r( 'Cron Event 2 Time: ' . date("Y-m-d H:i:s", $unlock_time_2_gmt) . ' <br /> Offset is: ' . (get_option( 'gmt_offset' ) * -1) . ' hours. <br /> Unlock Request URL: ' . trim($put_url,'/') . '/' . $device_id . '/setcode?user=' . $user . '&code=' . $pin . '<br /> ------------------------------ <br />');
        }

        wp_schedule_single_event( $unlock_time_gmt , 'pezlock_send_pin_event', array( (int)$booking_id, (int)$booking->product_id, (int)$resource_id, $pin, (int)$user ) );
        wp_schedule_single_event( $unlock_time_2_gmt , 'pezlock_send_pin_event', array( (int)$booking_id, (int)$booking->product_id, (int)$resource_id, $pin, (int)$user+1 ) );
      }

      $resource_name_str .= ( $resource->get_title() . ', ');
		}
    $resource_name_str = trim( $resource_name_str, ', ' );

    if ( $send_pin_now ) {
      echo '<script language="javascript"> alert("To access your rental equipment, go to '. $resource_name_str .' and enter your pin '. $pin .'.\nYour locker information is also in your confirmation email.") </script>';
    } else {
      echo '<script language="javascript"> alert("When your rental period starts, go to '. $resource_name_str .' and enter your pin '. $pin .'.\nYour locker information is also in your confirmation email.") </script>';
    }

  }

  /**
   * Returns send user PIN now(true) or later(false)
   */
  private function maybe_send_now( $booking ) {
    $send_now = true;
    $_start   = $booking->start;
    $_now     = strtotime( "+5 Minutes", current_time('timestamp') );

    if ($_start > $_now ) {
     $send_now = false;
    }

    if ( $this->debug ) { //debug
     print_r('Send Now logic: <br /> [Booking Start]=>'. date('Y-M-d H:i', $booking->start) .'  &  [Now +5 Minutes]=>' . date('Y-M-d H:i', strtotime( "+5 Minutes", current_time('timestamp') )) .'<br /> [Send_Later]=>'. (($_start > $_now) ? 'True' : 'False') .'  &   [Send_Now]=>'.  (($_start < $_now) ? 'True' : 'False') .'<br />');
    }

    return $send_now;
  }

  /**
   * Cron job - run scheduled resource event
   */
  public function pezlock_do_scheduled_resource_event( $booking_id, $product_id, $resource_id, $pin, $user ) {
    $product     = wc_get_product( $product_id );
    $resource    = $product->get_resource( $resource_id );
    $device_id   = get_post_meta( $resource_id, '_resource_lock_device_id', true );

    $options = get_option( 'pez_settings' );
    $put_url = isset($options['pez_lock_put_url']) ? $options['pez_lock_put_url'] : '';
    $auth    = isset($options['pez_lock_authoriation']) ? $options['pez_lock_authoriation'] : '';

    $this->do_locker_curl( $pin, $device_id, $put_url, $auth, $user );
  }

  /**
   * Send user PIN to lock
   */
  private function do_locker_curl( $user_pin, $device_id, $put_url, $auth, $user ) {
    global $wpdb;

    $req_url = trim($put_url,'/') . '/' . trim($device_id,'/') . '/setcode?user=' . $user . '&code=' . $user_pin;

    if ( $this->debug ) { //debug
      wp_schedule_single_event( strtotime("+10 Hours", current_time('timestamp')) , 'pez_SUCCESS_event', array($req_url) );
      print_r('Sending PIN cURL details:' . '<br />' . $req_url . '<br /> ------------------------- <br />');
    } else {

      $curl    = curl_init();
      curl_setopt_array($curl, array(
          CURLOPT_PORT           => "443",
          CURLOPT_URL            => $req_url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING       => "",
          CURLOPT_MAXREDIRS      => 10,
          CURLOPT_TIMEOUT        => 30,
          CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST  => "PUT",
          CURLOPT_HTTPHEADER => array(
            "authorization: " . $auth,
            "cache-control: no-cache",
            "postman-token: 1098c90c-779e-be63-86fe-851b348d6c62"
          ),
      ));

      $response = curl_exec($curl);
      $err = curl_error($curl);
      curl_close($curl);

      if ($err) { echo "Booking Error #:" . $err; }
    }

  }

  /**
   * Cancel booking cron events
   */
  public function pezlock_cancel_scheduled_events( $booking_id ) {
    $delete_id = $booking_id;
    $crons = get_option( 'cron' );
    $cronHook = 'pezlock_send_pin_event';
    foreach ($crons as $cron => $cronAction) {
      if ( is_array ($cronAction) ) {
        if( array_key_exists($cronHook, $cronAction) ){
          foreach( $cronAction[$cronHook] as $event_arr ) {
            $cronArgs = $event_arr['args'];
            if ( $delete_id == $cronArgs[0] ) {
              $timestamp = wp_next_scheduled( $cronHook, $cronArgs );
              wp_unschedule_event( $timestamp, $cronHook, $cronArgs );
            }
          }
      	}
      }
    }
  }

}
