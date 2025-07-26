# ressapp

This repository contains a Laravel application.

## Running Artisan Commands

All Artisan commands must be executed from the project root where the `artisan` file lives.
For example, to view the list of available commands run:

```bash
php artisan list
```

You may run any other Artisan command in the same way:

```bash
php artisan migrate
```

## Running Tests

Tests are written with PHPUnit. After installing dependencies with Composer run the test suite from the project root:


```bash
./vendor/bin/phpunit
```

If you have PHPUnit installed globally you can simply run `phpunit` instead.


### Continuous Integration

A GitHub Actions workflow runs the PHPUnit suite on every push and pull request. The workflow is defined in `.github/workflows/ci.yml`.
