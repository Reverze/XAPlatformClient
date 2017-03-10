# XAPlatformClient
Client for XAPlatform written in PHP

##What is it?
The XAPlatformClient is a some kind of bridge between XAPlatform and your app.
It helps you to integrate your app with XAPlatform. This library does not require
any frameworks (its not a framework-bundle). 

##Installation
You can install this library using composer:
```
composer require rev/xa-platform-client
```
This will install package at newest version. 

##Usage
Before you will use it, you must have registered application at XAPlatform.
Application key is a special key which identifies your app and app's other.

At first we must determine application key and XAPlatform provider.

```php
$platformCredentials = new PlatformClient\Auth\PlatformCredentials();
$platformCredentials->setAppKey($yourApplicationId);
$platformCredentials->setProvider($yourProviderHostname);
$platformCredentials->setPort($yourProviderServicePort);
```

The next is to set up cache driver. Caching is use to improve performance.
The recommended cache driver is **apcu**. Then you should determine cache lifetime
multiplier. What is lifetime multiplier? Each action has own cache lifetime. If you set
lifetime multiplier as *2*, each action will be stored 2 times longer.

```php
$cacheDriverParameters = new PlatformClient\Cache\CacheDriverParameters();
$cacheDriverParameters->setDriver('apcu');
$cacheDriverParameters->setMultiplier(2);
```

At the end we initialize client core using above parameters:
```php
$core = new PlatformClient\Core();
$core->setProvider($platformCredentials);
$core->setCacheParameters($cacheDriverParameters);

$core->connect();
```

That's all. Your app is ready to integrate with XAPlatform.
