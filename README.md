# Cache Plugin for PHP-Proxy Application

Provides caching support for PHP-Proxy application. Cache Plugin as of this moment only supports memory storage.

## Installation

 Install Redis Server:

```shell
apt-get install redis-server
```

Start the server if not already started

```shell
/etc/init.d/redis-server start
```

Find the **composer.json** file in your proxy directory, and add this package as one of its requirements:  

```json
"require": {
	"athlon1600/php-proxy": "@dev",
	"athlon1600/php-proxy-plugin-cache": "@dev"
},
```

Install the new composer package:  

```shell
composer update
```

Final step, find the **config.php** file in that same directory, and add **Cache** to the list of plugins
to be loaded.

```php
$config['plugins'] = array(
	'Cache', // <--- new plugin
	'HeaderRewrite',
	'Stream',
	.....
);
```
Cache plugin has to be loaded first, so must appear **first in the list** otherwise it won't work.

#### Redis Configuration

By default, Redis is configured to store everything it's being sent, however on a busy proxy that tends
to overload the memory with all those caches files. For best performance, adjust redis memory settings and set appropriate key eviction policy. This should be good enough on a server with 1 GB ram:


	root@uk1:/# redis-cli
	127.0.0.1:6379> config set maxmemory 300000000
	OK
	127.0.0.1:6379> config set maxmemory-policy volatile-lru
	OK
	127.0.0.1:6379>




