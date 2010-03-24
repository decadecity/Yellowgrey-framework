<?php

/* Default page title */
define('DEFAULT_TITLE', 'Yellowgrey framework project');

/*
If the framework is installed in a sub directory of the domain this should be
set to the folder name.  Omit the trailing /, include the leading /.

For example, if the framework is running under http://example.com/framework/ :
define('WEB_ROOT', '/framework');
*/
define('WEB_ROOT', '');

/*
If DEBUG is True then the framework will output debugging information on
errors.  This should be False in a production environment.
*/
define('DEBUG', True);

/*
You might want to use the following to enable debug info when running on
a local dev server.

if ('127.0.0.1' == $_SERVER['SERVER_ADDR']) {
  define('DEBUG', True);
  error_reporting(6143); //turn on all PHP error reporting
} else {
  define('DEBUG', False);
}
*/

$plugins = array('futurama');
