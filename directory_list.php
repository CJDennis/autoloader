<?php
// A class for lists of directories, not for directory listings!

class DirectoryList {
  protected $include_current_directory = false;
  protected $before_current_directory = [];
  protected $after_current_directory = [];

  public function include_current_directory() {
    $this->include_current_directory = true;
  }

  public function exclude_current_directory() {
    $this->include_current_directory = false;
  }

  public function add_search_directory_before_current($directory) {
    $real_path = $this->get_real_path($directory);
    $this->remove_real_path($real_path);
    $this->before_current_directory[] = $real_path;
  }

  public function add_search_directory_after_current($directory) {
    $real_path = $this->get_real_path($directory);
    $this->remove_real_path($real_path);
    $this->after_current_directory[] = $real_path;
  }

  public function remove_search_directory($directory) {
    $real_path = $this->get_real_path($directory);
    $this->remove_real_path($real_path);
  }

  protected function remove_real_path($real_path) {
    $path_pattern = preg_quote($real_path, '/');
    $this->before_current_directory = preg_grep("/^{$path_pattern}$/", $this->before_current_directory, PREG_GREP_INVERT);
    $this->after_current_directory = preg_grep("/^{$path_pattern}$/", $this->after_current_directory, PREG_GREP_INVERT);
  }

  protected function get_real_path($path) {
    $real_path = realpath($path);
    if ($real_path === false) {
      $path = str_replace(DIRECTORY_SEPARATOR, '/', $path);
      if (DIRECTORY_SEPARATOR !== '\\' || !preg_match('~^[A-Z]:/~i', $path)) {
        if (substr($path, 0, 1) !== '/') {
          $path = str_replace(DIRECTORY_SEPARATOR, '/', getcwd()) . '/' . $path;
        }
        else {
          $root = preg_replace('~^(.*?)/.*$~', '$1', str_replace(DIRECTORY_SEPARATOR, '/', realpath('/')));
          $path = $root . $path;
        }
      }
      // Replace /./ and // with /
      $path = preg_replace('~/\Q.\E(?![^/])|/(?=/)~', '', $path);
      // Replace each /directory/../ with /
      $path = preg_replace('~/(?!\Q..\E(?![^/]))[^/]+(?R)*/\Q..\E(?![^/])~', '', $path);
      $path = str_replace('/', DIRECTORY_SEPARATOR, $path);
      $real_path = $path;
    }
    return $real_path;
  }

  public function get_directory_list() {
    $directory_list = $this->before_current_directory;
    if ($this->include_current_directory || !count($this->before_current_directory) && !count($this->after_current_directory)) {
      $directory_list[] = $this->get_current_directory();
    }
    $directory_list = array_merge($directory_list, $this->after_current_directory);
    return $directory_list;
  }

  // Wrapping the file system functions allows mocking this class in Unit Tests
  protected function get_current_directory() {
    return getcwd();
  }
}
