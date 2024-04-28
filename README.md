# svn-migrate

<p align="center">
    <strong>SVN to Git migration tool that can be used to convert SVN repos to Git repos.</strong>
</p>

<p align="center">
    <a href="https://github.com/nephifey/svn-migrate"><img src="http://img.shields.io/badge/source-nephifey/svn--migrate-blue.svg?style=flat-square" alt="Source Code"></a>
    <a href="https://packagist.org/packages/nephifey/svn-migrate"><img src="https://img.shields.io/packagist/v/nephifey/svn-migrate.svg?style=flat-square&label=release" alt="Download Package"></a>
    <a href="https://php.net"><img src="https://img.shields.io/packagist/php-v/nephifey/svn-migrate.svg?style=flat-square&colorB=%238892BF" alt="PHP Programming Language"></a>
    <a href="https://github.com/nephifey/svn-migrate/blob/main/LICENSE"><img src="https://img.shields.io/packagist/l/nephifey/svn-migrate.svg?style=flat-square&colorB=darkcyan" alt="Read License"></a>
    <!-- <a href="https://github.com/nephifey/svn-migrate/actions/workflows/continuous-integration.yml"><img src="https://img.shields.io/github/actions/workflow/status/nephifey/svn-migrate/continuous-integration.yml?branch=main&style=flat-square&logo=github" alt="Build Status"></a> -->
    <!-- <a href="https://codecov.io/gh/nephifey/svn-migrate"><img src="https://img.shields.io/codecov/c/gh/nephifey/svn-migrate?label=codecov&logo=codecov&style=flat-square" alt="Codecov Code Coverage"></a> -->
    <!-- <a href="https://shepherd.dev/github/nephifey/svn-migrate"><img src="https://img.shields.io/endpoint?style=flat-square&url=https%3A%2F%2Fshepherd.dev%2Fgithub%2Fnephifey%2Fsvn-migrate%2Fcoverage" alt="Psalm Type Coverage"></a> -->
</p>

## Prerequisites

You must have [Git](https://git-scm.com/), [Git Svn](https://git-scm.com/) 
(Bundled with Git), and [Svn](https://subversion.apache.org/) installed on your machine. These are checked at the start of the migration setup and are **REQUIRED**.

```
$ git --version
$ svn --version --quiet
$ git svn --version
```

## Getting Started

Install the package into your project using composer via the command below.

```
$ composer require nephifey/svn-migrate
```

## Usage

Run the below php script to kick off the migration.

```
$ vendor/bin/svn-migrate
```

## Copyright and License

nephifey/svn-migrate is copyright © Nathan Phifer <nephifer5@gmail.com> and licensed for use under the terms of the MIT License (MIT). Please see LICENSE for more information.
