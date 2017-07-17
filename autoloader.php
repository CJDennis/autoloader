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

  final public static function unregister() {
    spl_autoload_unregister([get_called_class(), static::AUTOLOAD_METHOD]);
  }

  final public static function register() {
    static::set_directory_list();
    static::register_magic_autoload_function_if_no_others_registered();
    spl_autoload_register([get_called_class(), static::AUTOLOAD_METHOD]);
  }

  protected static function register_magic_autoload_function_if_no_others_registered() {
    // __autoload() has been deprecated as of PHP 7.2 but some older libraries might still use it
    if (!spl_autoload_functions() && function_exists('__autoload')) {
      spl_autoload_register('__autoload');
    }
  }

  public static function get_directory_list() {
    static::set_directory_list();
    return static::$directory_list;
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
    $namespaces = preg_split('/\\\\/', $class_name, -1, PREG_SPLIT_NO_EMPTY);
    $class_name = array_pop($namespaces);
    static::$words = static::split_class_name_into_words($class_name);

    if (static::validate()) {
      static::process_words();
      static::adjust();

      $class_filename = static::get_class_filename($namespaces);
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

  protected static function get_class_filename($namespaces) {
    $directory_list = static::get_namespaced_directory_list($namespaces);
    $path_generator = new PathGenerator;
    $path_generator->set_base_directories($directory_list);
    $path_generator->set_words(static::$words);
    $path_generator->set_filename(static::$class_filename);
    return $path_generator->get_first_existing_filename();
  }

  protected static function get_namespaced_directory_list($namespaces) {
    $directory_list = static::$directory_list->get_directory_list();
    $namespace_path = strtolower(implode('/', $namespaces));
    if ($namespace_path) {
      $namespace_path = '/' . $namespace_path;
    }
    $directory_list = array_map(function($directory) use ($namespace_path) { return $directory . $namespace_path; }, $directory_list);
    return $directory_list;
  }
}
