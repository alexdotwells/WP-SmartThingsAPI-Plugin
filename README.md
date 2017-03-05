# Paddle EZ Booking Control
 - Plugin Name:       Paddle EZ Booking Control
 - Version:           2.5.2
 - Author:            AWells <ajwells99@gmail.com>
 - Description:       Paddle EZ Booking Control. Server & client side updates to customize the booking process.
 - Text Domain:       pez

-------------------------------
<br>

**FILE:  pez.php**
- Plugin Main
- Identifies, Initializes, & Runs the plugin

<br>

**FILE:  includes/class-pez.php**
- PEZ Plugin Controller
- Loads supporting plugin files/classes
- Defines actions and filters
- Initializes & runs Loader

<br>

**FILE:  includes/class-pez-loader.php**
- Removes any necessary actions
- Adds plugin hooks

<br>

**FILE:  includes/class-pez-booking-controller.php**
- Methods called by plugin actions/filters

<br>

**FILE:  includes/class-pez-booking.php**
- Helper methods to get/build data for the main booking controller

<br>

**FILE:  includes/assets/css/pez-bookingform.css**
- Shop/Booking Form styles

<br><br>

**FILE:  includes/assets/js/pez-bookingform.js**
- Shop/Booking Form JavaScript

<br>

**FILE:  includes/assets/js/pez-cart.js**
- Cart/Checkout JavaScript
