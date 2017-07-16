<?php
require_once 'path_generator_seam.php';

class PathGeneratorTest extends \Codeception\Test\Unit {
  /**
   * @var \UnitTester
   */
  protected $tester;

  protected function _before() {
  }

  protected function _after() {
  }

  // tests
  public function testShouldSetBaseDirectories() {
    $base_directories = ['./foo', './bar'];
    $path_generator_seam = new PathGeneratorSeam;
    $path_generator_seam->set_base_directories($base_directories);
    $this->assertSame($base_directories, $path_generator_seam->get_base_directories());
  }

  public function testShouldSetWords() {
    $words = ['foo', 'bar'];
    $path_generator_seam = new PathGeneratorSeam;
    $path_generator_seam->set_words($words);
    $this->assertSame($words, $path_generator_seam->get_words());
  }

  public function testShouldSetFilename() {
    $filename = 'foo.bar';
    $path_generator_seam = new PathGeneratorSeam;
    $path_generator_seam->set_filename($filename);
    $this->assertSame($filename, $path_generator_seam->get_filename());
  }

  public function testShouldGetFirstExistingFilename() {
    $path_generator_seam = new PathGeneratorSeam;

    $path_generator_seam->set_test_directories([
      'root/',  // Ancestor
      'root/foo/',  // Ancestor
      'root/foo/bar/',  // Decoy
      'root/foo/bar/baz/',  // Child of decoy
      'root/foo/bar_baz/',  // Correct directory
    ]);
    $path_generator_seam->set_test_files([
      'root/foo/bar/filename.txt',  // Decoy
      'root/foo/bar/baz/filename.txt',  // Decoy in child directory
      'root/foo/bar_baz/filename.txt',  // Correct file
    ]);

    $path_generator_seam->set_base_directories(['root']);
    $path_generator_seam->set_words(['foo', 'bar', 'baz', 'qux']);
    $path_generator_seam->set_filename('filename.txt');
    $this->assertSame('root/foo/bar_baz/filename.txt', $path_generator_seam->get_first_existing_filename());
  }

  public function testShouldGetFilenameInRootDirectory() {
    $path_generator_seam = new PathGeneratorSeam;

    $path_generator_seam->set_test_directories([
      'root/',  // Ancestor
      'root/foo/',  // Ancestor
      'root/foo/bar/',  // Decoy
      'root/foo/bar/baz/',  // Child of decoy
      'root/foo/bar_baz/',  // Correct directory
    ]);
    $path_generator_seam->set_test_files([
      'root/filename.txt',  // Correct file
      'root/foo/bar/filename.txt',  // Decoy
      'root/foo/bar/baz/filename.txt',  // Decoy in child directory
      'root/foo/bar_baz/filename.txt',  // Decoy
    ]);

    $path_generator_seam->set_base_directories(['root']);
    $path_generator_seam->set_words(['foo', 'bar', 'baz', 'qux']);
    $path_generator_seam->set_filename('filename.txt');
    $this->assertSame('root/filename.txt', $path_generator_seam->get_first_existing_filename());
  }

  public function testShouldReturnFalseIfAMatchingFilenameCouldNotBeFound() {
    $path_generator_seam = new PathGeneratorSeam;

    $path_generator_seam->set_test_directories([
      'root/',  // Ancestor
      'root/foo/',  // Ancestor
      'root/foo/bar/',  // Decoy
      'root/foo/bar/baz/',  // Child of decoy
      'root/foo/bar_baz/',  // Correct directory
    ]);
    $path_generator_seam->set_test_files([
      'root/filename.txt',  // Correct file
      'root/foo/bar/filename.txt',  // Decoy
      'root/foo/bar/baz/filename.txt',  // Decoy in child directory
      'root/foo/bar_baz/filename.txt',  // Decoy
    ]);

    $path_generator_seam->set_base_directories(['root']);
    $path_generator_seam->set_words(['foo', 'bar', 'baz', 'qux']);
    $path_generator_seam->set_filename('bad_filename.txt');
    $this->assertFalse($path_generator_seam->get_first_existing_filename());
  }
}
