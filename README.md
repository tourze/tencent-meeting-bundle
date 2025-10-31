# Tencent Meeting Bundle

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/tencent-meeting-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/tencent-meeting-bundle)
[![PHP Version Require](https://img.shields.io/packagist/php-v/tourze/tencent-meeting-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/tencent-meeting-bundle)
[![License](https://img.shields.io/packagist/l/tourze/tencent-meeting-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/tencent-meeting-bundle)
[![Build Status](https://img.shields.io/travis/tourze/tencent-meeting-bundle/master.svg?style=flat-square)](https://travis-ci.org/tourze/tencent-meeting-bundle)
[![Quality Score](https://img.shields.io/scrutinizer/g/tourze/tencent-meeting-bundle.svg?style=flat-square)](https://scrutinizer-ci.com/g/tourze/tencent-meeting-bundle)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/tourze/tencent-meeting-bundle.svg?style=flat-square)](https://scrutinizer-ci.com/g/tourze/tencent-meeting-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/tencent-meeting-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/tencent-meeting-bundle)

A Symfony bundle for integrating Tencent Meeting API functionality, providing a foundation for building Tencent Meeting related applications.

## Features

- Symfony bundle structure for easy integration
- Tencent Meeting API integration support
- Extensible architecture for custom implementations
- PSR-4 autoloading support
- Comprehensive API documentation included

## Installation

Install the bundle via Composer:

```bash
composer require tourze/tencent-meeting-bundle
```

## Quick Start

After installation, register the bundle in your Symfony application:

```php
// config/bundles.php
return [
    // ...
    Tourze\TencentMeetingBundle\TencentMeetingBundle::class => ['all' => true],
];
```

## Configuration

Currently, this bundle provides the basic structure for Tencent Meeting integration. The bundle includes:

- Service container configuration
- Dependency injection setup
- Basic extension structure

Configuration options will be added in future versions as API features are implemented.

## Usage

This bundle is under development. The current version provides the foundation structure for Tencent Meeting integration.

Future versions will include:
- Meeting creation and management
- User authentication
- Recording management
- Webhook support
- Enterprise features

## API Documentation

The bundle includes comprehensive API documentation for Tencent Meeting REST APIs located in the `API 文档/` directory, covering:

- Meeting management
- User management
- Recording management
- Webhook events
- Enterprise features

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details on how to contribute to this project.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
