<?php
require_once 'directory_list.php';

class DirectoryListSeam extends DirectoryList {
  public function get_real_path($path) {
    return parent::get_real_path($path);
  }

  public function get_include_current_directory() {
    return $this->include_current_directory;
  }

  public function clear_search_directory_lists() {
    $this->before_current_directory = [];
    $this->after_current_directory = [];
  }
}
