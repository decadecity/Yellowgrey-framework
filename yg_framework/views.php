<?php

/*
Stub class, copy/paste this and change values to get started.

class VIEW_NAME extends View {
  public function build()
  {
    $this->title = 'PAGE_TITLE';
  }
}

*/

/*
This example shows all available methods when defining a view.
All are optional.
*/
class example extends View {
  /*
  build() gets called just before the view is sent to the template.
  */
  public function build()
  {
    /*
    Change the title, defaults to the title defined in settings.php
    */
    $this->title = 'Yellowgrey framework - example page';
    /*
    Set the base template
    */
    $this->template = 'baseHTML';
    /*
    Set the page body, defaults to the name of the class or the home page body.  
    This is the php file in the templates directory that will be used 
    to generate body()
    */
    $this->view = 'example';
    /*
    Set the status code, defaults to 200.
    Allowed values are: 200, 404, 500
    */
    $this->set_HTTP_status_code(200);
    /*
    Set the content type and optional charset, defaults to HTML in UTF8 charset.
    */
    $this->set_content_type('application/pdf');
    $this->set_content_type('text/html','utf-8');
    /*
    Change stylesheets.  Defaults to including main.css
    */
    $this->remove_css('main');
    $this->add_css('main');
    /*
    Change JavaScript includes.  Defaults toincluding  main.js
    */
    $this->remove_js('main');
    $this->add_js('main');
  }
}

/* Removing this route will cause the homepage to error! */
class home extends View {
}
