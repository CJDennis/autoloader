<?php
require_once 'directory_list_seam.php';
require_once 'autoloader.php';

class AutoloaderSeam extends Autoloader {
  protected static $directory_list; // Overrides inherited value to separate tests
}
