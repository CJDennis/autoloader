# autoloader
A PHP class to allow easy reorganisation of source directories by intelligent autoloading.

Software projects can be resistant to change because of the way they have been designed.
One of the ways design can be rigid is by having the source files in hard-coded locations.
Most projects have some level of organisation: third party library files are kept separate
from the rest of the code, a particular directory hierachy is in use, etc.

However, not all projects are as well organised as this.

The Autoloader class is designed to make it easy to reorganise your directory structures
as often as you need to without having to also change a lot of hard-coded references.
It does this by eliminating the hard-coded references and replacing them with autoloaded files.
This is a manual step, the code won't do this for you!

Let's say you have a class called `FooBar`. You can specify your library directory, e.g. `lib/`.
By default the autoloader will try to load a file called `foo_bar.php` by searching in these
locations in the following order:

- `lib/foo_bar.php`
- `lib/foo_bar/foo_bar.php`
- `lib/foo/foo_bar.php`
- `lib/foo/bar/foo_bar.php`

The root directory of the library is searched first, then the longest possible directory formed by the class name.
If the file is still not found, the directory names are progressively shortened until a match is found.
The search is based on the most specific names first, i.e. the longest names with the fewest directories.
This gives you the freedom of placing your `FooBar` class in any one of the above four locations
without changing a single line anywhere in your source code! `FooBarBaz` would search in up to eight locations
and `FooBarBazQux` would search in up to 16 locations, etc.

If your class is in a namespace, the namespace will be inserted between the library directory and the search directories.
e.g. `Baz\Qux\FooBar` would search in `lib/baz/qux/` onwards.

If no matching file is found, autoloading continues with the next registered autoloader.
This means that the Autoloader class can be extended with different options, running each one in turn.

The derived classes can be loaded in any order. They will not take effect until their `register()` method
has been called, and they can be disabled by calling `unregister()` on them.

```php
<?php
require_once 'second_autoloader.php';
require_once 'last_autoloader.php';
require_once 'first_autoloader.php';

FirstAutoloader::register();
SecondAutoloader::register();
LastAutoloader::register();

FirstAutoloader::unregister();

// 'my_fabulous_class.php' doesn't need to be included or required before the next line
$my_fabulous_class = new MyFabulousClass;
```

If the magic `__autoload()` function has been defined but no other autoload functions are registered,
`__autoload()` will be added to the start of the list. This means that older libraries that haven't been updated
should not break, as long as `__autoload()` is only defined once in the entire codebase. Note that it is no longer recommended
to use [`__autoload()`](http://php.net/manual/en/function.autoload.php) and it has been deprecated since PHP 7.2.

There are two constants within the class: `PREPEND` and `APPEND` which can be overriden
to add a string constant to the start and/or end of the filename, e.g. if `PREPEND = 'my_'`
and `APPEND = '.class'`, autoloading the class `FooBar` will search for a file called `my_foo_bar.class.php`.

The autoloader class is designed to be extended. It is recommended to extend it
if you want to change any options, overriding a minimal amount of code.
This makes it highly likely that any updates will be compatible with your code.

The `Autoloader` class relies on two other classes: `DirectoryList` and `PathGenerator`.

You should have at least the following files:
- `autoloader.php`
- `directory_list.php`
- `path_generator.php`
- `LICENSE`
- `README.md`
- `codeception.yml`
- `tests/unit/AutoloaderTest.php`
- `tests/unit/DirectoryListTest.php`
- `tests/unit/PathGeneratorTest.php`
- `tests/unit/autoloader_bad_exception.php`
- `tests/unit/autoloader_seam.php`
- `tests/unit/directory_list_seam.php`
- `tests/unit/path_generator_seam.php`
- `tests/unit/test_autoloader_exception.php`
- `tests/unit/autoloader_test_first_class.php`
- `tests/unit/autoloader/test/second/autoloader_test_second_class.php`
- `tests/unit/autoloader/testnamespace/test_class.php`

plus other files required for Codeception. The code is tested with [Codeception 2.2.6](http://codeception.com/builds).
You will need the 2.2.6 version of Codeception's `.phar` file to run the tests.

- `php path/to/codecept.phar run unit` - to run tests for all classes
- `php path/to/codecept.phar run unit AutoloaderTest` - to run the tests in the named file only
