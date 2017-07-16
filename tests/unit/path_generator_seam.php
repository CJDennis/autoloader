<?php
require_once 'path_generator.php';

class PathGeneratorSeam extends PathGenerator {
  protected $test_directories;
  protected $test_files;

  public function get_base_directories() {
    return $this->base_directories;
  }

  public function get_words() {
    return $this->words;
  }

  public function get_filename() {
    return $this->filename;
  }

  public function set_test_directories($test_directories) {
    return $this->test_directories = $test_directories;
  }

  public function set_test_files($test_files) {
    return $this->test_files = $test_files;
  }

  protected function is_a_directory($path) {
    parent::is_a_directory($path);
    //return true;
    return in_array($path, $this->test_directories);
  }

  protected function is_a_file($path) {
    parent::is_a_file($path);
    //return true;
    return in_array($path, $this->test_files);
  }
}
