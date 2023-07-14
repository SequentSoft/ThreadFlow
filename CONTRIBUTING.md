# CONTRIBUTING

Contributions are welcome, and are accepted via pull requests.
Please review these guidelines before submitting any pull requests.

## Process

1. Fork the project.
2. Create a new branch for your changes.
3. Code, test, commit, and push your changes.
4. Open a pull request detailing your changes. Make sure to follow the [template](.github/PULL_REQUEST_TEMPLATE.md).

## Guidelines

* Ensure that you follow the [PSR-1](https://www.php-fig.org/psr/psr-1/), [PSR-4](https://www.php-fig.org/psr/psr-4/), and [PSR-12](https://www.php-fig.org/psr/psr-12/) coding standards. You can check this by running `composer lint`.
* Please strive for a coherent commit history, making sure each individual commit in your pull request is meaningful.
* If necessary, please [rebase](https://git-scm.com/book/en/v2/Git-Branching-Rebasing) to avoid merge conflicts.
* Remember that we follow [SemVer](http://semver.org/). Therefore, if you are adding any changes to the API, it may only be done in a new major version.
* Any new functionality or changes should be accompanied by appropriate tests. Please ensure that all tests are passing before submitting a pull request.

## Setup

Clone your fork, then install the dev dependencies:
```bash
composer install
```
## Lint

Lint your code:
```bash
composer lint
```
## Tests

Run all tests:
```bash
composer test
```

Check types:
```bash
composer test:types
```

Unit tests:
```bash
composer test:unit
```
