# WeChat Bot Bundle

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/wechat-bot-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/wechat-bot-bundle)
[![PHP Version Require](https://img.shields.io/badge/php-%5E8.1-blue.svg?style=flat-square)](https://php.net/)
[![Symfony Version](https://img.shields.io/badge/symfony-%5E7.3-blue.svg?style=flat-square)](https://symfony.com/)
[![License](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)](LICENSE)
[![Build Status](https://img.shields.io/github/actions/workflow/status/tourze/php-monorepo/ci.yml?branch=master&style=flat-square)](https://github.com/tourze/php-monorepo/actions)
[![Code Coverage](https://img.shields.io/codecov/c/github/tourze/php-monorepo?style=flat-square)](https://codecov.io/gh/tourze/php-monorepo)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/wechat-bot-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/wechat-bot-bundle)

一个为 Symfony 应用提供的全面微信机器人 API 客户端和管理模块，提供完整的微信机器人功能集成。

接口文档： https://www.yuque.com/wechatpro/wxapi

## 目录

- [描述](#描述)
- [功能特性](#功能特性)
- [安装](#安装)
- [配置](#配置)
- [快速开始](#快速开始)
- [分步指南](#分步指南)
  - [第一步：配置服务](#第一步配置服务)
  - [第二步：设置实体](#第二步设置实体)
  - [第三步：使用 API 服务](#第三步使用-api-服务)
- [命令](#命令)
  - [检查在线状态](#检查在线状态)
  - [同步联系人](#同步联系人)
  - [同步群组](#同步群组)
- [高级用法](#高级用法)
  - [自定义请求类](#自定义请求类)
  - [事件处理](#事件处理)
  - [后台控制器](#后台控制器)
- [依赖](#依赖)
- [测试](#测试)
- [贡献](#贡献)
  - [代码风格](#代码风格)
  - [提交更改](#提交更改)
- [许可证](#许可证)
- [更新日志](#更新日志)

## 描述

此包为 Symfony 应用程序集成微信机器人功能提供了完整的解决方案。包括 API 客户端、
实体管理、后台控制器以及微信机器人操作的完整请求/响应处理。

## 功能特性

- **完整的微信机器人 API 集成**：全面支持微信机器人 REST API
- **好友管理**：添加、同意、删除好友及管理好友请求
- **群组操作**：创建、管理群组，处理邀请和成员操作
- **消息处理**：发送/接收文本、图片、文件、语音消息，支持@提醒
- **朋友圈集成**：发布、点赞、评论微信朋友圈
- **文件操作**：上传/下载图片、视频、文件，支持 CDN
- **账号管理**：设备管理、登录/登出、二维码操作
- **后台界面**：EasyAdmin 控制器用于后台管理
- **实体系统**：Doctrine 实体进行数据持久化
- **命令行工具**：Symfony 控制台命令进行自动化操作

## 安装

```bash
composer require tourze/wechat-bot-bundle
```

## 配置

使用此包之前，请在 Symfony 应用程序中配置 API 设置：

```yaml
# config/packages/wechat_bot.yaml
wechat_bot:
    api_base_url: 'https://your-api-endpoint.com'
    default_timeout: 30
```

## 快速开始

按照以下步骤将微信机器人功能集成到您的 Symfony 应用程序中：

## 分步指南

### 第一步：配置服务

```yaml
# config/packages/wechat_bot.yaml
wechat_bot:
    api_base_url: 'https://your-api-endpoint.com'
    default_timeout: 30
```

### 第二步：设置实体

```php
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Entity\WeChatContact;

// 创建微信账号
$account = new WeChatAccount();
$account->setDeviceId('your-device-id');
$account->setNickname('Bot Account');
```

### 第三步：使用 API 服务

```php
use Tourze\WechatBotBundle\Service\WeChatContactService;
use Tourze\WechatBotBundle\Service\WeChatMessageService;

// 添加好友
$contactService->addFriend($account, 'wxid_friend', 'Hello!', 'search');

// 发送消息
$messageService->sendTextMessage($account, 'wxid_receiver', 'Hello World!');
```

## 命令

此包提供了若干控制台命令用于自动化操作：

### 检查在线状态
```bash
php bin/console wechat-bot:check-online-status
```
检查微信机器人设备的在线状态。

### 同步联系人
```bash
php bin/console wechat:sync-contacts
```
从 API 同步微信联系人到本地数据库。

### 同步群组
```bash
php bin/console wechat:sync-groups
```
从 API 同步微信群组到本地数据库。

## 高级用法

### 自定义请求类

```php
use Tourze\WechatBotBundle\Request\Friend\AddFriendRequest;

$request = new AddFriendRequest($apiAccount, $deviceId, $wxId, $message, $addType);
$response = $apiClient->request($request);
```

### 事件处理

```php
use Tourze\WechatBotBundle\Handler\WeChatCallbackHandler;

// 处理传入的微信消息
$handler->handleCallback($callbackData);
```

### 后台控制器

在 `/admin` 访问后台界面管理：
- 微信账号
- 联系人和好友
- 群组和成员
- 消息和对话
- 朋友圈和评论

## 依赖

此包需要以下依赖：
- PHP ^8.1
- Symfony ^7.3
- Doctrine ORM ^3.0
- Doctrine DBAL ^4.0
- EasyAdmin Bundle ^4
- Tourze HTTP Client Bundle ^0.1

## 测试

运行测试套件：

```bash
# 运行所有测试
./vendor/bin/phpunit packages/wechat-bot-bundle/tests

# 运行带覆盖率的测试
./vendor/bin/phpunit packages/wechat-bot-bundle/tests --coverage-html=coverage

# 运行 PHPStan 分析
./vendor/bin/phpstan analyse packages/wechat-bot-bundle
```

## 贡献

请查看 [CONTRIBUTING.md](../../CONTRIBUTING.md) 了解如何为此项目做出贡献的详细信息。

### 代码风格

本项目遵循 PSR-12 编码标准并使用 PHP 8.1+ 特性。请确保您的代码：

- 遵循 PSR-12 编码标准
- 包含适当的类型声明
- 具有全面的测试
- 通过 PHPStan 8 级分析

### 提交更改

1. Fork 仓库
2. 创建功能分支 (`git checkout -b feature/amazing-feature`)
3. 进行更改
4. 为您的更改添加测试
5. 确保所有测试通过
6. 提交更改 (`git commit -am 'Add some amazing feature'`)
7. 推送到分支 (`git push origin feature/amazing-feature`)
8. 创建 Pull Request

## 许可证

此项目采用 MIT 许可证 - 详情请查看 [LICENSE](LICENSE) 文件。

## 更新日志

### 版本 0.1.0
- 初始版本发布
- 完整的微信机器人 API 集成
- 带有 EasyAdmin 的管理界面
- 用于自动化的控制台命令
- 全面的测试套件