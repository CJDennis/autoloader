<?php
// A class for lists of directories, not for directory listings!

class DirectoryList {
  protected $include_global_include_paths = false;
  protected $before_global_include_paths = [];
  protected $after_global_include_paths = [];

  public function include_global_include_paths() {
    $this->include_global_include_paths = true;
  }

  public function exclude_global_include_paths() {
    $this->include_global_include_paths = false;
  }

  public function add_search_directory_before_current($directory) {
    $real_path = $this->get_real_path($directory);
    $this->remove_real_path($real_path);
    $this->before_global_include_paths[] = $real_path;
  }

  public function add_search_directory_after_current($directory) {
    $real_path = $this->get_real_path($directory);
    $this->remove_real_path($real_path);
    $this->after_global_include_paths[] = $real_path;
  }

  public function remove_search_directory($directory) {
    $real_path = $this->get_real_path($directory);
    $this->remove_real_path($real_path);
  }

  protected function remove_real_path($real_path) {
    $path_pattern = preg_quote($real_path, '/');
    $this->before_global_include_paths = preg_grep("/^{$path_pattern}$/", $this->before_global_include_paths, PREG_GREP_INVERT);
    $this->after_global_include_paths = preg_grep("/^{$path_pattern}$/", $this->after_global_include_paths, PREG_GREP_INVERT);
  }

  public function get_directory_list() {
    $directory_list = $this->before_global_include_paths;
    if ($this->include_global_include_paths || !count($this->before_global_include_paths) && !count($this->after_global_include_paths)) {
      $directory_list = array_merge($directory_list, $this->get_current_include_paths());
    }
    $directory_list = array_merge($directory_list, $this->after_global_include_paths);
    return $directory_list;
  }

  protected function get_current_include_paths() {
    $include_paths = explode(PATH_SEPARATOR, get_include_path());
    $include_paths = array_map([$this, 'get_real_path'], $include_paths);
    return $include_paths;
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
}
