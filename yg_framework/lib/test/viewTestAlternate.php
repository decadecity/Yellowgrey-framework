<?php

require_once realpath(dirname(__FILE__)).'/../view.php';
require_once 'PHPUnit/Framework/TestCase.php' ;
require_once 'PHPUnit/Extensions/OutputTestCase.php';
require_once realpath(dirname(__FILE__)).'/viewTestStub.php';

//set up the constants needed
define('DEBUG', True);
define('DEFAULT_TITLE', 'test');
define('ROOT_DIR',realpath(dirname(__FILE__)));
define('ROUTES', serialize(array('Test-link' => 'test')));
define('WEB_ROOT','/framework');

class ViewTestAlternate extends ViewTestStub {
  public function testErrorMessage()
  {
    $this->expectOutputString(
      '<strong>You are seeing this message  becuase DEBUG is set to True '.
      'in settings.php</strong><br />') ;
    echo $this->view->error_message('test');
  }
  public function testHeadersDefault()
  {
    $this->expectOutputString(
      "    <link rel='stylesheet' href='/framework/static/main.css' type='text/css' />\n".
      "    <script src='/framework/static/main.js' type='text/javascript'></script>\n"
    );
    echo $this->view->headers();
  }
  public function testHeadersAddRemove()
  {
    $this->view->add_css('test');
    $this->view->add_js('test');
    $this->view->remove_css('main');
    $this->view->remove_js('main');
    $this->expectOutputString(
      "    <link rel='stylesheet' href='/framework/static/test.css' type='text/css' />\n".
      "    <script src='/framework/static/test.js' type='text/javascript'></script>\n"
    );
    echo $this->view->headers();
  }  public function testLinkToRoute()
  {
    $this->expectOutputString('<a href="/framework/Test-link" class="test-class" '.
                              'id="test-id">link text</a>');
    echo $this->view->link_to_route('test', 'link text', 'test-class',
                                    'test-id');
  }
  public function testLinkToRouteHome()
  {
    $this->expectOutputString('<a href="/framework/" class="test-class" '.
                              'id="test-id">link text</a>');
    echo $this->view->link_to_route('home', 'link text', 'test-class',
                                    'test-id');
  }
  public function testResolveRoute()
  {
    $this->expectOutputString('/framework/Test-link');
    echo $this->view->resolve_route('test');
  }
  public function testResolveRouteHome()
  {
    $this->expectOutputString('/framework/');
    echo $this->view->resolve_route('home');
  }

}

