Yellowgrey Framework
====================

A light weight VC framework inspired by Django and written in PHP.

Quickstart Guide
================

* Copy all files and directories to the root web folder of the sie that will 
    host the framework.
* That's it!

Basic Use
=========

Change the default site title
-----------------------------
Edit yg_framework/settings.php and change the value of DEFAULT_TITLE
If you are developing, change DEBUG to True - this will cause the framework
to output debugging messages.

Editing the default homepage
----------------------------
The content of the homepage comes from yg_framework/templates/home.php
Edit this page to change the content.

Adding a new page
-----------------
Create a new php page in yg_framework/templates/ and fill with the <body>
content of the page.

Edit yg_framework/views.php and create a new View class named after the page 
name you have just created. There is a stub class in this file you can copy
to get the format.

Edit yg_framework/urls.php and add a new route to the array.  The key is the
URI of the page on your domain and the value is the name of your view.

That's it!

Advanced Use
============

The full API of the View class is outlined in the example class in views.php
