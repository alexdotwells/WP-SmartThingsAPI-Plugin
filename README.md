# Paddle EZ SmartLock Control
 - Plugin Name:       Paddle EZ Lock Control
 - Version:           1.3.0
 - Author:            AWells
 - Description:       Paddle EZ Smart Lock & Locker Management
 - Text Domain:       pezlock


-------------------------------




**FILE:  pez-lock.php**
- Plugin Main
- Identifies, Initializes, & Runs the lock plugin




**FILE:  includes/class-pezlock.php**

- PEZ Lock Plugin Controller
- Loads supporting plugin files/classes
- Defines actions and filters
- Initializes & runs Loader




**FILE:  includes/class-pezlock-loader.php**

- Adds plugin hooks
- (incl: cron-job event hooks)




**FILE:  includes/class-pezlock-admin.php**

- Admin Dashboard methods
- Resource device(lock) setup




**FILE:  includes/class-pezlock-options.php**

- Adds global settings page to sidebar in admin dashboard
- Create/Update/Delete plugin settings




**FILE:  includes/class-pezlock-event-controller.php**

- Methods called by plugin actions/filters
