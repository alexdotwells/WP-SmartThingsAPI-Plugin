<?php

/**
 * Paddle EZ Lock Admin Controller
 *
 * @package    pezlock
 * @subpackage pezlock/includes
 * @author     AJWells <ajwells99@gmail.com>
 */


class Pezlock_Admin {

	private $plugin_name;
	private $version;

	public function __construct() {}

  function pezlock_admin_add_meta_box( $post ) {
    add_meta_box( 'pezlock_resource_locker_metabox', __( 'Locker Data', 'woocommerce-bookings' ), array( $this, 'pezlock_get_meta_box_inner' ), 'bookable_resource', 'normal', 'high' );
  }


  function pezlock_get_meta_box_inner( $post ) {
		$desc_field1 = "From SmartThings - https://graph-na02-useast1.api.smartthings.com/device/show/DEVICE ID";
		$default_field1 = "";
    $val_field1 = get_post_meta( $post->ID, '_resource_lock_device_id', true );
    $val_field1 = (strlen($val_field1) < 1) ? $default_field1 : $val_field1;

    ?>
    <div class="woocommerce_options_panel woocommerce">
			<div class="panel-wrap" id="pezlock_resource_locker">
				<div class="options_group">
					<?php woocommerce_wp_text_input( array( 'id' => '_resource_lock_device_id', 'label' => __( 'Device ID', 'woocommerce-bookings' ), 'data_type' => 'text',
					'value' => $val_field1, 'desc_tip' => 'true', 'description' => __( $desc_field1, 'woocommerce-bookings' ), 'style' => 'width: 55%;' ) ); ?>
				</div>
      </div>
    </div>
    <?php
  }

  function pezlock_adminsave_post( $post_id, $post_after, $post_before ) {
    if ( empty( $post_id ) or empty( $post_after ) ) {
        return;
    }

    if( empty( $post_after->post_type ) or $post_after->post_type != 'bookable_resource' ) {
        return $post_id ;
    }

    if ( defined( 'DOING_AUTOSAVE' ) or is_int( wp_is_post_revision( $post_after ) ) or is_int( wp_is_post_autosave( $post_after ) ) ) {
        return $post_id ;
    }

    if ( isset( $_POST['_resource_lock_device_id'] ) ) {
        $_resource_lock_device_id = '';

        if( $_POST['_resource_lock_device_id'] !== '' ) {
            $_resource_lock_device_id = wc_format_decimal( $_POST['_resource_lock_device_id'] );
        }
        update_post_meta( $post_id, '_resource_lock_device_id', $_resource_lock_device_id );
    }
  }

}
