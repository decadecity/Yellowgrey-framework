<?php
/**
 * This is the core of the framework
 */

/* traps for a file not exisiting */
function yg_require_file($file_name, $message = 'Core file not found') {
  if (!file_exists(ROOT_DIR.$file_name)) {
    printf('<h1>Internal framework error</h1><p>%s</p>',$message);
    if (defined(DEBUG)) {
      if (DEBUG) {
        printf('<p>File was: %s</p>', $file_name);
      }
    }
    //exit();
    return False;
  }
  return True;
}

function yg_error_handler($output)
{
    $error = error_get_last();
    $output = "";
    if (is_array($error)) {
      foreach ($error as $info => $string) {
          $output .= sprintf("%s: %s\n", $info, $string);
      }
    }
    return $output;
}

ob_start('yg_error_handler');

$core_files = array(
                    '/settings.php',
                    '/lib/view.php',
                    '/views.php',
                    '/urls.php',
                   );

/* Include the settings - needs to be in global scope */
foreach ($core_files as $file_name) {
  if(yg_require_file($file_name)) {
    require_once(ROOT_DIR.$file_name);
    if ('/settings.php' == $file_name) {
      if (!defined('DEBUG')) {
        //DEBUG hasn't been set correctly so define it as False now.
        define('DEBUG', False);
      }
      if (!defined('WEB_ROOT')) {
        //WEB_ROOT hasn't been set correctly so define it as a blank string.
        define('WEB_ROOT', '');
      }
      if (!defined('DEFAULT_TITLE')) {
        //DEFAULT_TITLE hasn't been set correctly so define it now.
        define('DEFAULT_TITLE', 'Yellowgrey framework project');
      }
    }
  }
}

if (count($plugins)) {
  foreach ($plugins as $plugin) {
    $file_name = '/plugins/'.$plugin.'/__init__.php';
    if (file_exists($file_name)) {
      if(yg_require_file($file_name, 'Plugin '.$plugin.' not found')) {
        require_once(ROOT_DIR.$file_name);
      }
    }
  }
}

if (ob_get_length()) {
  $error = ob_get_contents();
  ob_end_clean();
  header('HTTP/1.0 500 Internal Server Error');
  printf('<h1>Internal framework error</h1>');
  //if (defined(DEBUG)) {
    //if (DEBUG) {
      printf('<p>Error when including core files:</p><p>%s</p>',$error);
    //}
  //}
  exit();
} else {
  ob_end_clean();
}

if (!isset($routes)) {
  //URL map array wasn't found, define a blank array
  $routes = array();
}
/* Turn the routes array into a constant, used to generate links later */
define('ROUTES', serialize($routes));

/* This is where we resolve the URL into a view */
if (isset($_GET['ygRoute'])) {
  $route = $_GET['ygRoute'];
  unset($_GET['ygRoute']);
}

$error = ''; //Error message to pass to view

/* Main routing code */
if (!isset($route)) {
  //If route is not set then it's a fatal problem
  $page = new error500;
  $page->error_message = sprintf('Route was not set, '.
                                 'check ygRoute parameter in .htaccess');
  $view = 'error500';
} else {
  //ygRoute was set, probably by .htaccess
  if (0 == strlen($route)) {
    //No route => home page
    $view = 'home';
  } else {
    //We have a route
    if (array_key_exists($route, $routes)) {
      $view = $routes[$route];
    } else {
      //No matching route was found - the page doesn't exist
      $view = 'error404';
      $error = sprintf('No matching route was found in urls.php.<br />'.
                      'Search string was: %s', htmlspecialchars($route));
    }
  }
  if (class_exists($view)) {
    $page = new $view;
    $page->error_message = $error ;
  } else {
    //The view class is missing
    $page = new error500;
    $page->error_message = sprintf('View Class }%s{ was not found.', $view);
    $view = 'error500';
  }
}

/*
$route is unsecure user input.  as it has now been validated it's time to unset
it to prevent it being used later.  The only way it can get out is if DEBUG
is set to True and it is a 404 page - even then it's been escaped.
*/
unset($route);

$page->view = $view; //set the view
unset($routes);
//clear the rest of the global scope, no longer needed
unset($view);
unset($error);

/* sets the base template - can be overwridden in $page->build() */
if (@$_SERVER['X-Requested-With'] == 'XMLHttpRequest') { //Is this an XML page? -- Used for AJAX delivery
  $page->set_content_type('text/xml; charset=utf-8');
  $page->template = 'baseXML';
} else { //This is HTML
  $page->template = 'baseHTML';
}

/*
Call build to set up the view ready for output.
$page->build() should be defined in views.php
*/
if (isset($plugins)) {
  $page->preBuild($plugins);
}

/* Start the page going */
$page->send_HTTP_headers(); //Send the HTTP headers
if (yg_require_file('/templates/'.$page->template.'.php',
    'Template not found')) {
  @require_once(ROOT_DIR.'/templates/'.$page->template.'.php');  //output
}
