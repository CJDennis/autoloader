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
    Autoloader::unregister();
    AutoloaderSeam::unregister();
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
    $directory_list_seam->include_global_include_paths();
    $directory_list_seam->exclude_global_include_paths();
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
    $directory_list_seam->exclude_global_include_paths();
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
    $directory_list_seam->exclude_global_include_paths();
    $directory_list_seam->clear_search_directory_lists();
    $directory_list_seam->add_search_directory_before_current('tests/unit');
    $this->assertFalse(class_exists($class_name));
    AutoloaderSeam::unregister();
  }

  public function testShouldUseSPL__AutoloadFunctionIfNoneOthersRegistered() {
    $this->create_autoload_function();

    Autoloader::unregister();
    $autoloader_functions = spl_autoload_functions();
    foreach ($autoloader_functions as $autoloader_function) {
      spl_autoload_unregister($autoloader_function);
    }

    require_once 'test_autoloader_exception.php';
    $test_autoloader = function ($class_name) {
      throw new TestAutoloaderException('using $test_autoloader');
    };
    spl_autoload_register($test_autoloader);

    $e = null;
    try {
      class_exists('BadClass');
    }
    catch (Exception $e) {}

    spl_autoload_unregister($test_autoloader);
    foreach ($autoloader_functions as $autoloader_function) {
      spl_autoload_register($autoloader_function);
    }
    Autoloader::register();

    $this->tester->expectException('TestAutoloaderException', function () use ($e) {
      if ($e instanceof Exception) {
        throw $e;
      }
    });
  }

  public function testShouldUseSPL__autoloadFunctionIfRegisteredBeforeOthers() {
    $this->create_autoload_function();

    Autoloader::unregister();
    $autoloader_functions = spl_autoload_functions();
    foreach ($autoloader_functions as $autoloader_function) {
      spl_autoload_unregister($autoloader_function);
    }

    Autoloader::register();

    $e = null;
    try {
      class_exists('BadClass2');
    }
    catch (Exception $e) {}

    Autoloader::unregister();
    spl_autoload_unregister('__autoload');
    foreach ($autoloader_functions as $autoloader_function) {
      spl_autoload_register($autoloader_function);
    }
    Autoloader::register();

    $this->tester->expectException('AutoloaderBadException', function () use ($e) {
      if ($e instanceof Exception) {
        throw $e;
      }
    });
  }

  protected function create_autoload_function() {
    class_exists('Codeception\Lib\Notification');
    class_exists('Codeception\Event\PrintResultEvent');
    class_exists('PHP_CodeCoverage_Report_Text');
    class_exists('PHP_CodeCoverage_Report_Factory');
    class_exists('PHP_CodeCoverage_Report_Node');
    class_exists('PHP_CodeCoverage_Report_Node_Directory');
    class_exists('PHP_CodeCoverage_Report_Node_File');
    class_exists('PHP_CodeCoverage_Util');
    class_exists('PHP_CodeCoverage_Report_Node_Iterator');
    class_exists('PHP_CodeCoverage_Report_PHP');
    class_exists('PHP_CodeCoverage_Report_HTML');
    class_exists('PHP_CodeCoverage_Report_HTML_Renderer');
    class_exists('PHP_CodeCoverage_Report_HTML_Renderer_Dashboard');
    class_exists('PHP_CodeCoverage_Report_HTML_Renderer_Directory');
    class_exists('PHP_CodeCoverage_Report_HTML_Renderer_File');

    require_once 'autoloader_bad_exception.php';
    if (!function_exists('__autoload')) {
      function __autoload($class_name) {
        throw new AutoloaderBadException("{$class_name} from __autoload()");
      }
    }
  }

  public function testShouldAutoloadNamespacedClass() {
    AutoloaderSeam::register();
    // requires a valid 'autoloader_test_second_class.php' file to exist in the test/unit directory tree, e.g. in test/unit/autoloader/test/second
    $class_name = 'Autoloader\TestNamespace\TestClass';
    if (class_exists($class_name, false)) {
      throw new \PHPUnit_Framework_SkippedTestError("class {$class_name} is already defined");
    }
    $directory_list_seam = AutoloaderSeam::get_directory_list();
    $directory_list_seam->exclude_global_include_paths();
    $directory_list_seam->clear_search_directory_lists();
    $directory_list_seam->add_search_directory_after_current('tests/unit');
    $this->assertTrue(class_exists($class_name));
    AutoloaderSeam::unregister();
  }
}
