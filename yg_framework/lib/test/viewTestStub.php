<?php
/**
 * Base tests and classes for testing the main View class.
 * This allows us to run tests with different values of constants.
 */

class ViewOpen extends View { //allows us to get into protected methods and vars
  public function initialise_headers()
  {
    //used to check that headers overwrite all these variables
    $this->http_header_string = 'test';
    $this->http_header_code = 0;
    $this->http_header_location = 'http://localhost/';
  }
  public function get_http_header_string()
  {
    return $this->http_header_string;
  }
  public function get_http_header_code()
  {
    return $this->http_header_code;
  }
  public function get_http_header_content_type()
  {
    return $this->http_header_content_type;
  }
  public function get_http_header_location()
  {
    return $this->http_header_location;
  }
  public function test_build_HTTP_headers()
  {
    return $this->build_HTTP_headers();
  }
}


class testPlugin extends ygPlugin {
  public static function preBuild(View $view)
  {
    return parent::preBuild($view);
  }
  public static function postBuild(View $view)
  {
    return parent::postBuild($view);
  }
}


class ViewTestStub extends PHPUnit_Extensions_OutputTestCase {
  protected $view ;
  protected function setUp()
  {
    $this->view = new ViewOpen;
  }
  public function test__construct()
  {
    $this->assertTrue($this->view->view == 'ViewOpen');
  }
  public function testBuild()
  {
    //checks existance
    $this->assertTrue($this->view->build() == null);
  }
  public function testBuildHTTPHeadersDefault()
  {
    $result = array( 0 => 'Content-type: text/html; charset=utf-8',
                     1 => 'HTTP/1.0 200 OK');
    $this->assertTrue(
                      $this->view->test_build_HTTP_headers() == $result
                     );
  }
  public function testBuildHTTPHeadersNonStandard()
  {
    $this->view->set_HTTP_header('X-Test', 'test');
    $result = array( 0 => 'X-Test: test',
                     1 => 'Content-type: text/html; charset=utf-8',
                     2 => 'HTTP/1.0 200 OK');
    $this->assertTrue(
                      $this->view->test_build_HTTP_headers() == $result
                     );
  }
  public function testBuildHTTPHeadersRedirect()
  {
    $this->view->set_HTTP_status_code(307, 'http://example.com/');
    $result = array( 0 => 'Content-type: text/html; charset=utf-8',
                     1 => 'Location: http://example.com/',
                     2 => 'HTTP/1.0 307 Temporary Redirect');
    $this->assertTrue(
                      $this->view->test_build_HTTP_headers() == $result
                     );
  }
  public function testBuildHTTPHeadersRedirectInvalid()
  {
    $this->view->set_HTTP_status_code(307, 'invalid');
    $result = array( 0 => 'Content-type: text/html; charset=utf-8',
                     1 => 'HTTP/1.0 200 OK');
    $this->assertTrue(
                      $this->view->test_build_HTTP_headers() == $result
                     );
  }
  public function testBody()
  {
    $this->view->view = 'body_stub_test';
    $this->expectOutputString('body_stub_test');
    echo ($this->view->body());
  }
  public function testBodyInvalid()
  {
    $this->view->view = 'noexist';
    $this->expectOutputString('');
    echo ($this->view->body());
  }
  public function testErrorMessage()
  {
    $this->expectOutputString('') ;
    echo $this->view->error_message('test');
  }
  public function testHeadersDefault()
  {
    $this->expectOutputString(
      "    <link rel='stylesheet' href='/static/main.css' type='text/css' />\n".
      "    <script src='/static/main.js' type='text/javascript'></script>\n"
    );
    echo $this->view->headers();
  }
  public function testHeadersRemove()
  {
    $this->view->remove_css('main');
    $this->view->remove_js('main');
    $this->expectOutputString('');
    echo $this->view->headers();
  }
  public function testHeadersAddRemove()
  {
    $this->view->add_css('test');
    $this->view->add_js('test');
    $this->view->remove_css('main');
    $this->view->remove_js('main');
    $this->expectOutputString(
      "    <link rel='stylesheet' href='/static/test.css' type='text/css' />\n".
      "    <script src='/static/test.js' type='text/javascript'></script>\n"
    );
    echo $this->view->headers();
  }
  public function testLinkToRoute()
  {
    $this->expectOutputString('<a href="/Test-link" class="test-class" '.
                              'id="test-id" rel="test-rel">link text</a>');
    echo $this->view->link_to_route('test', 'link text', 'test-class',
                                    'test-id', 'test-rel');
  }
  public function testLinkToRouteHome()
  {
    $this->expectOutputString('<a href="/" class="test-class" '.
                              'id="test-id">link text</a>');
    echo $this->view->link_to_route('home', 'link text', 'test-class',
                                    'test-id');
  }
  public function testLinkToRouteNoRoute()
  {
    $this->expectOutputString('link text');
    echo $this->view->link_to_route('test-link', 'link text', 'test-class',
                                    'test-id');
  }
  public function testResolveRoute()
  {
    $this->expectOutputString('/Test-link');
    echo $this->view->resolve_route('test');
  }
  public function testResolveRouteHome()
  {
    $this->expectOutputString('/');
    echo $this->view->resolve_route('home');
  }
  public function testResolveRouteNoRoute()
  {
    $this->assertTrue($this->view->resolve_route('test-link') === False);
  }
  public function testSetContentTypeCharset() {
    $this->view->set_content_type('text/xml','utf-8');
    $this->assertTrue(
                      $this->view->get_http_header_content_type() == 'text/xml; charset=utf-8'
                     );
  }
  public function testSetContentTypeNoCharset() {
    $this->view->set_content_type('application/pdf');
    $this->assertTrue(
                      $this->view->get_http_header_content_type() == 'application/pdf'
                     );
  }
  /* This section is possibly excessive testing of a switch() */
  public function testSetHTTPStatusCodeDefault() {
    $this->assertTrue(
                      $this->view->get_http_header_code() == 200 and
                      $this->view->get_http_header_string() == 'OK' and
                      $this->view->get_http_header_location() == null
                     );
  }
  public function testSetHTTPStatusCode200() {
    $this->view->initialise_headers();
    $this->view->set_HTTP_status_code(200);
    $this->assertTrue(
                      $this->view->get_http_header_code() == 200 and
                      $this->view->get_http_header_string() == 'OK' and
                      $this->view->get_http_header_location() == null
                     );
  }
  public function testSetHTTPStatusCode404() {
    $this->view->initialise_headers();
    $this->view->set_HTTP_status_code(404);
    $this->assertTrue(
                      $this->view->get_http_header_code() == 404 and
                      $this->view->get_http_header_string() == 'Not Found' and
                      $this->view->get_http_header_location() == null
                     );
  }
  public function testSetHTTPStatusCode301() {
    $this->view->initialise_headers();
    $this->view->set_HTTP_status_code(301, 'invalid');
    $this->assertTrue(
                      $this->view->get_http_header_code() == 301 and
                      $this->view->get_http_header_string() == 'Moved Permanently' and
                      $this->view->get_http_header_location() == 'invalid'
                     );
  }
  public function testSetHTTPStatusCode302() {
    $this->view->initialise_headers();
    $this->view->set_HTTP_status_code(302, 'invalid');
    $this->assertTrue(
                      $this->view->get_http_header_code() == 302 and
                      $this->view->get_http_header_string() == 'Found' and
                      $this->view->get_http_header_location() == 'invalid'
                     );
  }
  public function testSetHTTPStatusCode303() {
    $this->view->initialise_headers();
    $this->view->set_HTTP_status_code(303, 'invalid');
    $this->assertTrue(
                      $this->view->get_http_header_code() == 303 and
                      $this->view->get_http_header_string() == 'See Other' and
                      $this->view->get_http_header_location() == 'invalid'
                     );
  }
  public function testSetHTTPStatusCode307() {
    $this->view->initialise_headers();
    $this->view->set_HTTP_status_code(307, 'invalid');
    $this->assertTrue(
                      $this->view->get_http_header_code() == 307 and
                      $this->view->get_http_header_string() == 'Temporary Redirect' and
                      $this->view->get_http_header_location() == 'invalid'
                     );
  }
  public function testSetHTTPStatusCode410() {
    $this->view->initialise_headers();
    $this->view->set_HTTP_status_code(410);
    $this->assertTrue(
                      $this->view->get_http_header_code() == 410 and
                      $this->view->get_http_header_string() == 'Gone' and
                      $this->view->get_http_header_location() == null
                     );
  }
  public function testSetHTTPStatusCode500() {
    $this->view->initialise_headers();
    $this->view->set_HTTP_status_code(500);
    $this->assertTrue(
                      $this->view->get_http_header_code() == 500 and
                      $this->view->get_http_header_string() == 'Internal Server Error' and
                      $this->view->get_http_header_location() == null
                     );
  }
  public function testTitleDefault()
  {
    $this->expectOutputString("    <title>test</title>\n");
    echo $this->view->title();
  }
  public function testTitleSet()
  {
    $this->view->title = 'new title';
    $this->expectOutputString("    <title>new title</title>\n");
    echo $this->view->title();
  }
  public function testPlugin()
  {
    $plugins = array('test');
    //Checks the plugin code is run
    $this->assertTrue($this->view->preBuild($plugins) == null);
  }
}
