# 腾讯会议 Bundle

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/tencent-meeting-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/tencent-meeting-bundle)
[![PHP Version Require](https://img.shields.io/packagist/php-v/tourze/tencent-meeting-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/tencent-meeting-bundle)
[![License](https://img.shields.io/packagist/l/tourze/tencent-meeting-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/tencent-meeting-bundle)
[![Build Status](https://img.shields.io/travis/tourze/tencent-meeting-bundle/master.svg?style=flat-square)](https://travis-ci.org/tourze/tencent-meeting-bundle)
[![Quality Score](https://img.shields.io/scrutinizer/g/tourze/tencent-meeting-bundle.svg?style=flat-square)](https://scrutinizer-ci.com/g/tourze/tencent-meeting-bundle)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/tourze/tencent-meeting-bundle.svg?style=flat-square)](https://scrutinizer-ci.com/g/tourze/tencent-meeting-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/tencent-meeting-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/tencent-meeting-bundle)

用于集成腾讯会议 API 功能的 Symfony Bundle，为构建腾讯会议相关应用程序提供基础。

## 功能特性

- Symfony Bundle 结构，便于集成
- 腾讯会议 API 集成支持
- 可扩展的架构，支持自定义实现
- PSR-4 自动加载支持
- 包含全面的 API 文档

## 安装

使用 Composer 安装：

```bash
composer require tourze/tencent-meeting-bundle
```

## 快速开始

安装后，在您的 Symfony 应用程序中注册该 Bundle：

```php
// config/bundles.php
return [
    // ...
    Tourze\TencentMeetingBundle\TencentMeetingBundle::class => ['all' => true],
];
```

## 配置

目前该 Bundle 提供了腾讯会议集成的基础结构。Bundle 包含：

- 服务容器配置
- 依赖注入设置
- 基本扩展结构

随着 API 功能的实现，将在未来版本中添加配置选项。

## 使用方法

该 Bundle 正在开发中。当前版本提供了腾讯会议集成的基础结构。

未来版本将包括：
- 会议创建和管理
- 用户身份验证
- 录制管理
- Webhook 支持
- 企业功能

## API 文档

该 Bundle 包含位于 `API 文档/` 目录中的腾讯会议 REST API 的全面文档，涵盖：

- 会议管理
- 用户管理
- 录制管理
- Webhook 事件
- 企业功能

## 参考文档

- [腾讯会议开发者文档](https://cloud.tencent.com/document/product/1095)
- [腾讯会议 API 文档](https://cloud.tencent.com/document/product/1095/42413)

## 贡献

请参阅 [CONTRIBUTING.md](CONTRIBUTING.md) 了解如何为此项目做出贡献。

## 许可证

该项目采用 MIT 许可证 - 有关详细信息，请参阅 [LICENSE](LICENSE) 文件。
