<?php

require_once realpath(dirname(__FILE__)).'/../view.php';
require_once 'PHPUnit/Framework/TestCase.php' ;
require_once 'PHPUnit/Extensions/OutputTestCase.php';
require_once realpath(dirname(__FILE__)).'/viewTestStub.php';

//set up the constants needed
define('DEBUG', False);
define('DEFAULT_TITLE', 'test');
define('ROOT_DIR',realpath(dirname(__FILE__)));
define('ROUTES', serialize(array('Test-link' => 'test')));
define('WEB_ROOT','');

class ViewTest extends ViewTestStub {
}