<?php

/**
 * Paddle EZ Lock Global Option Settings
 *
 * @package    pezlock
 * @subpackage pezlock/includes
 * @author     AJWells <ajwells99@gmail.com>
 */

class Pezlock_Options {

  private $options;

  public function __construct() {}


  /**
   * Add options page under "Settings"
   */
  public function pezlock_add_admin_menu(  ) {
  	add_menu_page( 'pezlock', 'Paddle-EZ Setup', 'manage_options', 'pezlock', array( $this, 'pezlock_options_page' ) );
    add_options_page( 'Settings Admin', 'Paddle-EZ Setup', 'manage_options', 'pezlock', array( $this, 'pezlock_options_page' ) );
  }

  /**
   * Initialize plugin option settings
   */
  public function pezlock_settings_init(  ) {

  	register_setting( 'pluginPage', 'pez_settings' );

  	add_settings_section(
  		'pezlock_pluginPage_section',
  		__( '', 'pezlock' ),
  		array( $this, 'pez_settings_section_callback'),
  		'pluginPage'
  	);

  	add_settings_field(
  		'pez_lock_put_url',
  		__( 'Lock Base PUT URL', 'pezlock' ),
  		array( $this, 'pez_lock_put_url_render'),
  		'pluginPage',
  		'pezlock_pluginPage_section'
  	);

  	add_settings_field(
  		'pez_lock_authoriation',
  		__( 'Lock Authorization', 'pezlock' ),
  		array( $this, 'pez_lock_authoriation_render'),
  		'pluginPage',
  		'pezlock_pluginPage_section'
  	);

  	add_settings_field(
  		'pez_lock_user',
  		__( 'Lock User', 'pezlock' ),
  		array( $this, 'pez_lock_user_render'),
  		'pluginPage',
  		'pezlock_pluginPage_section'
  	);

  	add_settings_field(
  		'pez_filter_interval',
  		__( 'Booking Interval', 'pezlock' ),
  		array( $this, 'pez_filter_interval_render'),
  		'pluginPage',
  		'pezlock_pluginPage_section'
  	);

    add_settings_field(
  		'pez_debug_enabled',
  		__( 'Debug Mode Enabled?', 'pezlock' ),
  		array( $this, 'pez_debug_enabled_render'),
  		'pluginPage',
  		'pezlock_pluginPage_section'
  	);
  }

  /**
   * pez_debug_enabled rendering callback
   */
  function pez_debug_enabled_render(  ) {
  	$options = get_option( 'pez_settings' );
    $is_debug = isset($options['pez_debug_enabled']) ? $options['pez_debug_enabled'] : '';

  	?>
  	<input type="checkbox" name="pez_settings[pez_debug_enabled]" value="1" <?php checked( isset( $options['pez_debug_enabled'] ) ); ?> />
  	<?php
  }

  /**
   * pez_lock_put_url rendering method callback
   */
  function pez_lock_put_url_render() {
  	$options = get_option( 'pez_settings' );
    $url = isset($options['pez_lock_put_url']) ? $options['pez_lock_put_url'] : '';

  	?>
  	<input type='text' name='pez_settings[pez_lock_put_url]' value='<?php echo $options['pez_lock_put_url']; ?>'>
  	<?php
  }

  /**
   * pez_lock_authoriation rendering method callback
   */
  function pez_lock_authoriation_render() {
  	$options = get_option( 'pez_settings' );
    $auth = isset($options['pez_lock_authoriation']) ? $options['pez_lock_authoriation'] : '';

  	?>
  	<input type='text' name='pez_settings[pez_lock_authoriation]' value='<?php echo $options['pez_lock_authoriation']; ?>'>
  	<?php
  }

  /**
   * pez_lock_user rendering method callback
   */
  function pez_lock_user_render() {
  	$options = get_option( 'pez_settings' );
    $user = isset($options['pez_lock_user']) ? $options['pez_lock_user'] : '';

  	?>
  	<input type='text' name='pez_settings[pez_lock_user]' value='<?php echo $options['pez_lock_user']; ?>'>
  	<?php
  }

  /**
   * pez_filter_interval rendering method callback
   */
  function pez_filter_interval_render() {
  	$options = get_option( 'pez_settings' );
    $interval = ( isset($options['pez_filter_interval']) && is_numeric($options['pez_filter_interval'])) ? $options['pez_filter_interval'] : 30;

  	?>
    <select name="pez_settings[pez_filter_interval]">
      <option value="15" <?php selected( $interval, 15 ); ?> >15 minutes</option>
      <option value="30" <?php selected( $interval, 30 ); ?> >30 minutes</option>
    </select>
  	<?php
  }

  function pez_settings_section_callback() {
  	echo '';
  }

  /**
   * Load the options page markup
   */
  function pezlock_options_page() {

  	?>
    <style>
    form.pez_options input[type=text] { width: 62%; }
    form.pez_options select { width: 10%; }
    </style>

  	<form class="pez_options" action='options.php' method='post'>

  		<h2>Paddle-EZ Global Plugin Settings</h2>

  		<?php
  		settings_fields( 'pluginPage' );
  		do_settings_sections( 'pluginPage' );
  		submit_button();
  		?>

  	</form>
  	<?php
  }

}

// $desc_field2 = "From Postman - PUT minus device id & parameters";
// $desc_field3 = "From Postman - e.g. Bearer 4526774c-494d-437a-a3d2-b03a9cdc5a2c";
// $desc_field4 = "This is which slot in the lock the code is stored";
// $default_field2 = "https://graph-na02-useast1.api.smartthings.com:443/api/smartapps/installations/46ff763e-ad1f-40e5-871e-4f70b83f1e09/";
// $default_field3 = "Bearer 4526774c-494d-437a-a3d2-b03a9cdc5a2c";
// $default_field4 = "1";
// $val_field2 = get_post_meta( $post->ID, '_resource_lock_put_url', true );
// $val_field3 = get_post_meta( $post->ID, '_resource_lock_authoriation', true );
// $val_field4 = get_post_meta( $post->ID, '_resource_lock_user', true );
// $val_field2 = (strlen($val_field2) < 1) ? $default_field2 : $val_field2;
// $val_field3 = (strlen($val_field3) < 1) ? $default_field3 : $val_field3;
// $val_field4 = (strlen($val_field4) < 1) ? $default_field4 : $val_field4;
