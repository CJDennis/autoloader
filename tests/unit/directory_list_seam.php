<?php
require_once 'directory_list.php';

class DirectoryListSeam extends DirectoryList {
  public function get_real_path($path) {
    return parent::get_real_path($path);
  }

  public function get_include_global_include_paths() {
    return $this->include_global_include_paths;
  }

  public function clear_search_directory_lists() {
    $this->before_global_include_paths = [];
    $this->after_global_include_paths = [];
  }
}
