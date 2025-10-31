# WeChat Bot Bundle

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/wechat-bot-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/wechat-bot-bundle)
[![PHP Version Require](https://img.shields.io/badge/php-%5E8.1-blue.svg?style=flat-square)](https://php.net/)
[![Symfony Version](https://img.shields.io/badge/symfony-%5E7.3-blue.svg?style=flat-square)](https://symfony.com/)
[![License](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)](LICENSE)
[![Build Status](https://img.shields.io/github/actions/workflow/status/tourze/php-monorepo/ci.yml?branch=master&style=flat-square)](https://github.com/tourze/php-monorepo/actions)
[![Code Coverage](https://img.shields.io/codecov/c/github/tourze/php-monorepo?style=flat-square)](https://codecov.io/gh/tourze/php-monorepo)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/wechat-bot-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/wechat-bot-bundle)

A comprehensive WeChat Bot API client and management module for Symfony applications, providing complete WeChat bot functionality integration.

## Table of Contents

- [Description](#description)
- [Features](#features)
- [Installation](#installation)
- [Configuration](#configuration)
- [Quick Start](#quick-start)
- [Step-by-Step Guide](#step-by-step-guide)
  - [Step 1: Configure Services](#step-1-configure-services)
  - [Step 2: Set Up Entities](#step-2-set-up-entities)
  - [Step 3: Use API Services](#step-3-use-api-services)
- [Commands](#commands)
  - [Check Online Status](#check-online-status)
  - [Sync Contacts](#sync-contacts)
  - [Sync Groups](#sync-groups)
- [Advanced Usage](#advanced-usage)
  - [Custom Request Classes](#custom-request-classes)
  - [Event Handling](#event-handling)
  - [Admin Controllers](#admin-controllers)
- [Dependencies](#dependencies)
- [Testing](#testing)
- [Contributing](#contributing)
  - [Code Style](#code-style)
  - [Submitting Changes](#submitting-changes)
- [License](#license)
- [Changelog](#changelog)

## Description

This bundle provides a complete solution for integrating WeChat Bot functionality 
into Symfony applications. It includes API clients, entity management, admin 
controllers, and comprehensive request/response handling for WeChat Bot operations.

## Features

- **Complete WeChat Bot API Integration**: Full support for WeChat Bot REST API
- **Friend Management**: Add, accept, delete friends and manage friend requests
- **Group Operations**: Create, manage groups, handle invitations and member operations
- **Message Handling**: Send/receive text, image, file, voice messages with @mentions
- **Moments Integration**: Publish, like, comment on WeChat Moments
- **File Operations**: Upload/download images, videos, files with CDN support
- **Account Management**: Device management, login/logout, QR code operations
- **Admin Interface**: EasyAdmin controllers for backend management
- **Entity System**: Doctrine entities for data persistence
- **Command Line Tools**: Symfony console commands for automation

## Installation

```bash
composer require tourze/wechat-bot-bundle
```

## Configuration

Before using this bundle, configure the API settings in your Symfony application:

```yaml
# config/packages/wechat_bot.yaml
wechat_bot:
    api_base_url: 'https://your-api-endpoint.com'
    default_timeout: 30
```

## Quick Start

Follow these steps to integrate WeChat Bot functionality into your Symfony application:

## Step-by-Step Guide

### Step 1: Configure Services

```yaml
# config/packages/wechat_bot.yaml
wechat_bot:
    api_base_url: 'https://your-api-endpoint.com'
    default_timeout: 30
```

### Step 2: Set Up Entities

```php
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Entity\WeChatContact;

// Create a WeChat account
$account = new WeChatAccount();
$account->setDeviceId('your-device-id');
$account->setNickname('Bot Account');
```

### Step 3: Use API Services

```php
use Tourze\WechatBotBundle\Service\WeChatContactService;
use Tourze\WechatBotBundle\Service\WeChatMessageService;

// Add a friend
$contactService->addFriend($account, 'wxid_friend', 'Hello!', 'search');

// Send a message
$messageService->sendTextMessage($account, 'wxid_receiver', 'Hello World!');
```

## Commands

The bundle provides several console commands for automation:

### Check Online Status
```bash
php bin/console wechat-bot:check-online-status
```
Check the online status of WeChat bot devices.

### Sync Contacts
```bash
php bin/console wechat:sync-contacts
```
Synchronize WeChat contacts from the API to local database.

### Sync Groups
```bash
php bin/console wechat:sync-groups
```
Synchronize WeChat groups from the API to local database.

## Advanced Usage

### Custom Request Classes

```php
use Tourze\WechatBotBundle\Request\Friend\AddFriendRequest;

$request = new AddFriendRequest($apiAccount, $deviceId, $wxId, $message, $addType);
$response = $apiClient->request($request);
```

### Event Handling

```php
use Tourze\WechatBotBundle\Handler\WeChatCallbackHandler;

// Handle incoming WeChat messages
$handler->handleCallback($callbackData);
```

### Admin Controllers

Access admin interface at `/admin` to manage:
- WeChat Accounts
- Contacts and Friends
- Groups and Members
- Messages and Conversations
- Moments and Comments

## Dependencies

This bundle requires the following packages:
- PHP ^8.1
- Symfony ^7.3
- Doctrine ORM ^3.0
- Doctrine DBAL ^4.0
- EasyAdmin Bundle ^4
- Tourze HTTP Client Bundle ^0.1

## Testing

Run the test suite with:

```bash
# Run all tests
./vendor/bin/phpunit packages/wechat-bot-bundle/tests

# Run with coverage
./vendor/bin/phpunit packages/wechat-bot-bundle/tests --coverage-html=coverage

# Run PHPStan analysis
./vendor/bin/phpstan analyse packages/wechat-bot-bundle
```

## Contributing

Please see [CONTRIBUTING.md](../../CONTRIBUTING.md) for details on how to contribute to this project.

### Code Style

This project follows PSR-12 coding standards and uses PHP 8.1+ features. Please ensure your code:

- Follows PSR-12 coding standards
- Includes proper type declarations
- Has comprehensive tests
- Passes PHPStan analysis at level 8

### Submitting Changes

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Make your changes
4. Add tests for your changes
5. Ensure all tests pass
6. Commit your changes (`git commit -am 'Add some amazing feature'`)
7. Push to the branch (`git push origin feature/amazing-feature`)
8. Create a Pull Request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Changelog

### Version 0.1.0
- Initial release
- Complete WeChat Bot API integration
- Admin interface with EasyAdmin
- Console commands for automation
- Comprehensive test suite