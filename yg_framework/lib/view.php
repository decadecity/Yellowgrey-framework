<?php
/**
 * Main class for handling the data to build a page
 *
 * This should be extened for each view required.
 */
class View {
  /**
   * @var Error message string
   */
  public $error_message = '';
  /**
   * @var Template to use when displaying page
   */
  public $template = '';
  /**
   * @var Page title (HTML)
   */
  public $title = DEFAULT_TITLE;
  /**
   * @var View to use when generating page
   */
  public $view = 'home';

  /**
   * @var Array of style sheets to include
   */
  protected $css = array('main' => True);
  /**
   * @var Array of JavaScripts to incluse
   */
  protected $js = array('main' => True);
  /**
   * @var HTTP header code
   */
  protected $http_header_code = 200;
  /**
   * @var HTTP content type
   */
  protected $http_header_content_type = 'text/html; charset=utf-8';
  /**
   * @var Location header - used for 30x redirects
   */
  protected $http_header_location = null;
  /**
   * @var HTTP code text string hint
   */
  protected $http_header_string = 'OK';
  /**
   * @var HTTP headers
   */
  protected $http_headers = array();

  public function __construct()
  {
    $this->view = get_class($this);
  }

  public function preBuild($plugins)
  {
    if (is_array($plugins)) {
      foreach ($plugins as $plugin) {
        $plugin = $plugin.'Plugin';
	if (class_exists($plugin)) {
          $plugin = new $plugin;
          $plugin->preBuild($this);
        }
      }
      $this->build();
      foreach ($plugins as $plugin) {
        $plugin = $plugin.'Plugin';
	if (class_exists($plugin)) {
          $plugin = new $plugin;
          $plugin->postBuild($this);
        }
      }
    }
  }

  /**
   * Main function called before page output
   *
   * Should be overwriden in views.php
   */
  public function build() {
  }

  /*/********************** CSS and JS functions *****************************/
  /**
   * Adds a stylesheet to include
   *
   * @param $css \p string Name of CSS file without extension
   */
  public function add_css($css)
  {
    $this->css[$css] = True;
  }
  /**
   * Removes a stylesheet to include
   *
   * @param $css \p string Name of CSS file without extension
   */
  public function remove_css($css)
  {
    $this->css[$css] = False;
  }
  /**
   * Adds an external JavaScript to include
   *
   * @param $js \p string Name of JS file without extension
   */
  public function add_js($js)
  {
    $this->js[$js] = True;
  }
  /**
   * Removes an external JavaScript to include
   *
   * @param $js \p string Name of JS file without extension
   */
  public function remove_js($js)
  {
    $this->js[$js] = False;
  }

  /*/******************* HTTP Header functions ******************************/
  /**
   * Sets the HTTP status code
   *
   * @param $code \p int HTTP status code
   * @param $location \p string Location for redirection
   */
  public function set_HTTP_status_code($code, $location = null)
  {
    switch ($code) {
      case 200:
        $this->http_header_string = 'OK';
        $this->http_header_code = $code;
        $this->http_header_location = null;
        break;
      case 301:
        $this->http_header_string = 'Moved Permanently';
        $this->http_header_code = $code;
        $this->http_header_location = $location;
        break;
      case 302:
        $this->http_header_string = 'Found';
        $this->http_header_code = $code;
        $this->http_header_location = $location;
        break;
      case 303:
        $this->http_header_string = 'See Other';
        $this->http_header_code = $code;
        $this->http_header_location = $location;
        break;
      case 307:
        $this->http_header_string = 'Temporary Redirect';
        $this->http_header_code = $code;
        $this->http_header_location = $location;
        break;
      case 404:
        $this->http_header_string = 'Not Found';
        $this->http_header_code = $code;
        $this->http_header_location = null;
        break;
       case 410:
        $this->http_header_string = 'Gone';
        $this->http_header_code = $code;
        $this->http_header_location = null;
        break;
      case 500:
        $this->http_header_string = 'Internal Server Error';
        $this->http_header_code = $code;
        $this->http_header_location = null;
        break;
    }
  }
  /**
   * Sets the response's mime type
   *
   * @param $type \p string Mime type of content
   * @param $charset \p string Character set of content
   */
  public function set_content_type($type, $charset = null)
  {
    $content_type = $type;
    if ($charset) {
      $content_type .= "; charset=$charset";
    }
    $this->http_header_content_type = $content_type;
  }

  /**
   * Sets a HTTP header, used for non standard headers
   */
  public function set_HTTP_header($header, $value)
  {
    $this->http_headers[$header] = $value;
  }

  /**
   * Builds the headers as an array
   *
   * This will be looped over by send_HTTP_headers(), this function is here
   * to allow the testing of the headers without calling the header() function
   * as that function cannot be unit tested.
   *
   * @return \p array List of headers in order, one value per header
   */
  protected function build_HTTP_headers()
  {
    $return = array();
    if (300 < $this->http_header_code and $this->http_header_code < 400) {
      //this is some form of redirect, check we have the correct information
      if (strlen($this->http_header_location)) {
        if (preg_match('/(https?):\/\/([0-9a-zA-Z][-\w]*[0-9a-zA-Z]\.)'.
                       '+[a-zA-Z]{2,9}(:\d{1,4})?([-\w\/#~:.?+=&%@#]*)/',
                       $this->http_header_location)) {
          //the location is a valid URL
          $return[1] = sprintf('Location: %s',$this->http_header_location);
        } else {
          //there was no valid location so use a 200 code
          $this->set_HTTP_status_code(200);
        }
      }
    }
    $return[0] = sprintf('HTTP/1.0 %d %s',
                         $this->http_header_code,
                         $this->http_header_string);
    if (strlen($this->http_header_content_type)) {
      $return[2] = sprintf('Content-type: %s', $this->http_header_content_type);
    }
    foreach ($this->http_headers as $code => $value) {
      $return[] = sprintf('%s: %s', $code, $value);
    }
    ksort($return);
    return array_reverse($return); //ensures that the HTTP header gets set last so overrides
  }

  // @codeCoverageIgnoreStart - can't unit test headers
  /**
   * Sends the HTTP headers, prevents duplication
   */
  public function send_HTTP_headers()
  {
    if (False == headers_sent($file, $line)) {
      foreach ($this->build_HTTP_headers() as $header) {
        header($header);
      }
    } else {
      echo $this->debug("Warning:  Headers have already been sent in file ".
                        "$file on line $line\n");
    }
  }
  // @codeCoverageIgnoreEnd

  /**
   * Returns a debug message if DEBUG is set to True
   *
   * @param $string \p string Error message text to display
   *
   * @return \p string Error message for output
   */
  public function debug($string)
  {
    $return = '';
    if (DEBUG) {
      // @codeCoverageIgnoreStart - can't use two values of a constant
      $return = sprintf('<strong>You are seeing this message  becuase DEBUG '.
                        'is set to True in settings.php</strong><br />%s',
                        $string);
    }
    // @codeCoverageIgnoreEnd
    return $return;
  }

  /*/*************** Template functions used to build page *******************/

  /**
   * Retruns the body content via the chosen view
   *
   * @return \p string Body content
   */
  public function body()
  {
    $page = $this; //make $page available to the template
    ob_start();
    if (!@include ROOT_DIR."/templates/{$this->view}.php") {
      $this->debug(sprintf("<p>Sorry, the view was not found.<br />".
                           "View requested was: }%s{</p>", $this->view));
    }
    $return = ob_get_contents();
    ob_end_clean();
    return $return;
  }

  /**
   * Returns the page error message via debug
   *
   * @return \p string Error message text
   */
  public function error_message()
  {
    return $this->debug($this->error_message);
  }

  /**
   * Returns the stylesheets and JavaScript includes
   *
   * @return \p string CSS and JS includes HTML
   */
  public function headers()
  {
    $return = '';
    foreach($this->css as $css => $value) {
      if ($value) {
        $return .= sprintf("    <link rel='stylesheet' ".
                           "href='".WEB_ROOT."/static/%s.css' ".
                           "type='text/css' />\n", $css);
      }
    }
    foreach($this->js as $js => $value) {
      if ($value) {
        $return .= sprintf("    <script src='".WEB_ROOT."/static/%s.js' ".
                           "type='text/javascript'></script>\n", $js);
      }
    }
    return $return;
  }

  /**
   * Returns the page title
   *
   * @return \p string Page Title
   */
  public function title()
  {
    return sprintf("    <title>%s</title>\n", $this->title) ;
  }

  /**
   * Returns the URI of a route relative to the server root
   *
   * @param $route \p string View to link to, key in the $routes array
   *
   * @return \p string URI of route, False if route not found
   */
  public function resolve_route($route)
  {
    if ($route == 'home') {
      $link = '';
    } else {
      $routes = unserialize(ROUTES); //we need access to the routing table
      $link = array_search($route, $routes);
    }
    if ($link !== False) {
      $link = sprintf(WEB_ROOT.'/%s', $link);
    }
    return $link;
  }

  /**
   * Returns a link to a route
   *
   * @param $route \p string View to link to, key in the $routes array
   * @param $text \p string Link text to display
   * @param $class \p string Class of link
   * @param $id \p string ID of link
   * @param $rel \p string Rel attribute of link
   *
   * @return \p string HTML of link to URL of route
   */
  public function link_to_route($route, $text='', $class=null, $id=null, $rel=null)
  {
    $link = $this->resolve_route($route);
    if ($link !== False) {
      $html = sprintf('<a href="%s"', $link);
      if ($class) {
        $html .= sprintf(' class="%s"', $class);
      }
      if ($id) {
        $html .= sprintf(' id="%s"', $id);
      }
      if ($rel) {
        $html .= sprintf(' rel="%s"', $rel);
      }
      $html .= sprintf('>%s</a>', $text);
      return $html;
    } else {
      return $text;
    }
  }
}

// @codeCoverageIgnoreStart - No point testing these

/**
 * Preset view class for handling 404 errors
 */
class error404 extends View {
  public function build()
  {
    $this->title = 'Page not found';
    $this->set_HTTP_status_code(404);
  }
}
/**
 * Preset view class for handling 500 errors
 */
class error500 extends View {
  public function build()
  {
    $this->title = 'Internal server error';
    $this->set_HTTP_status_code(500);
  }
}

/**
 * Stub for plugins to extend
 */
class ygPlugin {
  public static function preBuild(View $view)
  {
  }
  public static function postBuild(View $view)
  {
  }
}
