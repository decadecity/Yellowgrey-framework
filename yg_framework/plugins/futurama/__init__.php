<?php

require_once(dirname(__FILE__).'/futurama.php');

class futuramaPlugin extends ygPlugin {
  public static function preBuild(View $view)
  {
    $header = Futurama::get_header();
    $view->set_HTTP_header($header[0], $header[1]);
  }
}