# Xloit Bridge Zend ServiceManager Library

**Note: This project is a work in progress. Don't use it in production!**

This is the Bridge Zend ServiceManager Library for Xloit.

- File issues at [https://github.com/xloit/xloit-bridge-zend-servicemanager/issues](https://github.com/xloit/xloit-bridge-zend-servicemanager/issues)
- Create pull requests against [https://github.com/xloit/xloit-bridge-zend-servicemanager](https://github.com/xloit/xloit-bridge-zend-servicemanager)
- See [The Xloit Bridge Zend ServiceManager Library Documentation](#documentation)

## Installation

Install this library using composer:

```
$ composer require xloit/xloit-bridge-zend-servicemanager
```

## Resources

You can run the unit tests with the following command:

```
$ cd path/to/xloit-bridge-zend-servicemanager/
$ composer install
$ ./bin/phpunit
```

Please see the tests for full information on capabilities.

## Documentation

Documentation is [in the doc tree](doc/), and can be compiled using [bookdown](http://bookdown.io):

```
$ bookdown doc/bookdown.json
$ php -S 0.0.0.0:8080 -t doc/html/ # then browse to http://localhost:8080
```

### Bookdown

You can install bookdown globally using `composer global require bookdown/bookdown`.
If you do this, make sure that `$HOME/.composer/vendor/bin` is on your `$PATH` environment.

Alternately, public-facing, browseable documentation is available at [The Xloit Bridge Zend ServiceManager Library Documentation](http://projects.xloit.com/xloit/docs/current/bridge-zend-servicemanager)

## Architecture

Architectural notes are in [NOTES.md](NOTES.md).

## LICENSE

The files in this archive are released under the
[Xloit Open Project license](http://projects.xloit.com/license/MIT), which is a MIT License.
You can find a copy of this license in [LICENSE](LICENSE).
