<?php
require_once 'directory_list.php';
require_once 'path_generator.php';

class Autoloader {
  const AUTOLOAD_METHOD = 'autoload';
  const PREPEND = '';
  const APPEND = '';

  protected static $directory_list;
  protected static $words;
  protected static $class_filename;

  public static function get_directory_list() {
    static::set_directory_list();
    return static::$directory_list;
  }

  final public static function register() {
    static::set_directory_list();
    spl_autoload_register([get_called_class(), static::AUTOLOAD_METHOD]);
  }

  final public static function unregister() {
    spl_autoload_unregister([get_called_class(), static::AUTOLOAD_METHOD]);
  }

  public static function set_directory_list($directory_list = null) {
    if ($directory_list instanceof DirectoryList) {
      static::$directory_list = $directory_list;
    }
    elseif (static::$directory_list === null) {
      static::$directory_list = new DirectoryList;
    }
  }

  protected static function autoload($class_name) {
    static::$words = static::split_class_name_into_words($class_name);

    if (static::validate()) {
      static::process_words();
      static::adjust();

      $path_generator = new PathGenerator;
      $path_generator->set_base_directories(static::$directory_list->get_directory_list());
      $path_generator->set_words(static::$words);
      $path_generator->set_filename(static::$class_filename);
      $class_filename = $path_generator->get_first_existing_filename();
      if ($class_filename !== false) {
        require_once $class_filename;
      }
    }
  }

  protected static function validate() {
    return true;
  }

  protected static function process_words() {
    static::$words = array_map('strtolower', static::$words);
    static::$class_filename = static::PREPEND . implode('_', static::$words) . static::APPEND . '.php';
  }

  protected static function adjust() {
  }

  protected static function split_class_name_into_words($class_name) {
    $pattern = <<<REGEX
/
  (?=[A-Z] (?=[^A-Z\d]))
| (?<=[^A-Z\d]) (?=[A-Z\d])
| (?<=\d) (?=[A-Z])
| (?<=[^\d]) (?=\d)
| _
/x
REGEX;
    return preg_split($pattern, $class_name, null, PREG_SPLIT_NO_EMPTY);
  }
}
