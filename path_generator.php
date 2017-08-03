<?php
class PathGenerator {
  protected $base_directories;
  protected $words;
  protected $filename;

  protected $paths_skipped;

  public function set_base_directories($base_directories) {
    $this->base_directories = $base_directories;
  }

  public function set_words($words) {
    $this->words = $words;
  }

  public function set_filename($filename) {
    $this->filename = $filename;
  }

  public function get_first_existing_filename() {
    foreach ($this->base_directories as $base_directory) {
      $max = pow(2, count($this->words));
      for ($i = 0; $i < $max;) {
        $dir = $base_directory . '/' . $this->get_path($i);
        $path = $dir . $this->filename;
        if (!$this->is_a_directory($dir)) {
          $i += pow(2, $this->paths_skipped);
        }
        elseif (!$this->is_a_file($path)) {
          ++$i;
        }
        else {
          return $path;
        }
      }
    }
    return false;
  }

  protected function get_path($ordinal) {
    $words = $this->words;
    $n = count($words);
    $pattern = sprintf("%0{$n}b", $ordinal);
    preg_match('/^(.*?)(0*)$/', $pattern, $match);
    $pattern = $match[1];
    $this->paths_skipped = strlen($match[2]);
    $pattern = str_replace(['0', '1'], ['_', '/'], $pattern);
    array_splice($words, strlen($pattern));
    $path = implode('', array_map([get_called_class(), 'join_words_and_separators'], $words, str_split($pattern)));
    return $path;
  }

  protected function join_words_and_separators($word, $separator) {
    return $word . $separator;
  }

  // Wrapping the file system functions allows mocking this class in Unit Tests
  protected function is_a_directory($path) {
    return is_dir($path);
  }

  protected function is_a_file($path) {
    return is_file($path);
  }
}
