<?php
require_once 'autoloader_seam.php';

class AutoloaderTest extends \Codeception\Test\Unit {
  /**
   * @var \UnitTester
   */
  protected $tester;

  protected function _before() {
    Autoloader::register();
    AutoloaderSeam::set_directory_list(new DirectoryListSeam);
  }

  protected function _after() {
  }

  // tests
  public function testShouldRegisterTheAutoloaderFunction() {
    Autoloader::unregister();
    $num_autoload_functions = count(spl_autoload_functions());
    Autoloader::register();
    $this->assertSame($num_autoload_functions + 1, count(spl_autoload_functions()));
  }

  public function testShouldUnregisterTheAutoloaderFunction() {
    Autoloader::register();
    $num_autoload_functions = count(spl_autoload_functions());
    Autoloader::unregister();
    $this->assertSame($num_autoload_functions - 1, count(spl_autoload_functions()));
  }

  public function testShouldAutoloadAClass() {
    AutoloaderSeam::register();
    // requires a valid 'autoloader_test_first_class.php' file to exist in the test/unit directory
    $class_name = 'AutoloaderTestFirstClass';
    if (class_exists($class_name, false)) {
      throw new \PHPUnit_Framework_SkippedTestError("class {$class_name} is already defined");
    }
    $directory_list_seam = AutoloaderSeam::get_directory_list();
    $directory_list_seam->include_current_directory();
    $directory_list_seam->exclude_current_directory();
    $directory_list_seam->clear_search_directory_lists();
    $directory_list_seam->remove_search_directory('tests/unit');
    $directory_list_seam->add_search_directory_before_current('/fake/path');
    $directory_list_seam->add_search_directory_before_current('another/fake/path');
    $directory_list_seam->add_search_directory_before_current('tests/unit');
    $this->assertTrue(class_exists($class_name));
    AutoloaderSeam::unregister();
  }

  public function testShouldAutoloadAClassFromASubdirectory() {
    AutoloaderSeam::register();
    // requires a valid 'autoloader_test_second_class.php' file to exist in the test/unit directory tree, e.g. in test/unit/autoloader/test/second
    $class_name = 'AutoloaderTestSecondClass';
    if (class_exists($class_name, false)) {
      throw new \PHPUnit_Framework_SkippedTestError("class {$class_name} is already defined");
    }
    $directory_list_seam = AutoloaderSeam::get_directory_list();
    $directory_list_seam->exclude_current_directory();
    $directory_list_seam->clear_search_directory_lists();
    $directory_list_seam->add_search_directory_after_current('tests/unit');
    $this->assertTrue(class_exists($class_name));
    AutoloaderSeam::unregister();
  }

  public function testShouldFailToLoadANonexistantClass() {
    AutoloaderSeam::register();
    // requires a valid 'autoloader_test_third_class.php' file NOT to exist anywhere in the test/unit directory tree
    $class_name = 'AutoloaderTestThirdClass';
    if (class_exists($class_name, false)) {
      throw new \PHPUnit_Framework_SkippedTestError("class {$class_name} is already defined");
    }
    $directory_list_seam = AutoloaderSeam::get_directory_list();
    $directory_list_seam->exclude_current_directory();
    $directory_list_seam->clear_search_directory_lists();
    $directory_list_seam->add_search_directory_before_current('tests/unit');
    $this->assertFalse(class_exists($class_name));
    AutoloaderSeam::unregister();
  }
}
