<?php
require_once 'directory_list_seam.php';

class DirectoryListTest extends \Codeception\Test\Unit {
  /**
   * @var \UnitTester
   */
  protected $tester;

  protected function _before() {
  }

  protected function _after() {
  }

  // tests
  public function testShouldSetIncludeCurrentDirectory() {
    $directory_list_seam = new DirectoryListSeam;
    $directory_list_seam->include_current_directory();
    $this->assertTrue($directory_list_seam->get_include_current_directory());
  }

  public function testShouldUnsetIncludeCurrentDirectory() {
    $directory_list_seam = new DirectoryListSeam;
    $directory_list_seam->exclude_current_directory();
    $this->assertFalse($directory_list_seam->get_include_current_directory());
  }

  public function testShouldReturnTheRealPath() {
    // On Windows: C:\path\to\current\working\directory\foo\bar\baz
    // On *nix: /path/to/current/working/directory/foo/bar/baz
    $directory_list_seam = new DirectoryListSeam;
    $this->assertSame(rtrim(getcwd(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR
                      . implode(DIRECTORY_SEPARATOR, ['foo', 'bar', 'baz']), $directory_list_seam->get_real_path('1/2/../3//.///.././../foo//.//bar/../no/quz/../../bar/./baz'));
  }

  public function testShouldReturnTheRealPathFromAnAbsolutePath() {
    // On Windows: C:\foo\bar\baz
    // On *nix: /foo/bar/baz
    $directory_list_seam = new DirectoryListSeam;
    $this->assertSame(rtrim(realpath('/'), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR
                      . implode(DIRECTORY_SEPARATOR, ['foo', 'bar', 'baz']), $directory_list_seam->get_real_path('/1/2/../3//.///.././../foo//.//bar/../no/quz/../../bar/./baz'));
  }

  public function testShouldReturnTheRealPathOnWindowsFromAnAbsolutePathWithADriveLetter() {
    // On Windows: L:\foo\bar\baz
    // On *nix: this test would fail, so it is skipped, but with an assertion to keep the numbers the same
    $directory_list_seam = new DirectoryListSeam;
    if (DIRECTORY_SEPARATOR !== '\\') {
      $this->assertFalse(false);
    }
    else {
      $this->assertSame('L:' . DIRECTORY_SEPARATOR
                        . implode(DIRECTORY_SEPARATOR, ['foo', 'bar', 'baz']), $directory_list_seam->get_real_path('L:/1/2/../3//.///.././../foo//.//bar/../no/quz/../../bar/./baz'));
    }
  }

  public function testShouldReturnBeforeThenCurrentDirectoryThenAfter() {
    $directory_list_seam = new DirectoryListSeam;
    $directory_list_seam->include_current_directory();
    $directory_list_seam->add_search_directory_before_current('./foo');
    $directory_list_seam->add_search_directory_after_current('./bar');
    $directory_list_seam->add_search_directory_after_current('./baz');
    $directory_list_seam->add_search_directory_before_current('./qux');
    $this->assertSame([
      $directory_list_seam->get_real_path('./foo'),
      $directory_list_seam->get_real_path('./qux'),
      realpath(getcwd()),
      $directory_list_seam->get_real_path('./bar'),
      $directory_list_seam->get_real_path('./baz'),
    ], $directory_list_seam->get_directory_list());
  }

  public function testShouldReturnCurrentDirectoryOnly() {
    $directory_list_seam = new DirectoryList;
    $directory_list_seam->include_current_directory();
    $this->assertSame([realpath(getcwd())], $directory_list_seam->get_directory_list());
  }

  public function testShouldReturnCurrentDirectoryWhenExcludingAndBeforeAndAfterAreEmpty() {
    $directory_list_seam = new DirectoryList;
    $directory_list_seam->exclude_current_directory();
    $this->assertSame([realpath(getcwd())], $directory_list_seam->get_directory_list());
  }

  public function testShouldReturnDirectoriesExceptRemovedDirectories() {
    $directory_list_seam = new DirectoryListSeam;
    $directory_list_seam->exclude_current_directory();
    $directory_list_seam->add_search_directory_before_current('./foo');
    $directory_list_seam->add_search_directory_after_current('./bar');
    $directory_list_seam->add_search_directory_after_current('./baz');
    $directory_list_seam->add_search_directory_before_current('./qux');
    $directory_list_seam->remove_search_directory('./bar');
    $this->assertSame([
      $directory_list_seam->get_real_path('./foo'),
      $directory_list_seam->get_real_path('./qux'),
      $directory_list_seam->get_real_path('./baz'),
    ], $directory_list_seam->get_directory_list());
  }
}
