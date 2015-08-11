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

```json
"require": {
	"athlon1600/php-proxy": "@dev",
	"athlon1600/php-proxy-plugin-cache": "@dev"
},
```

LRU-Cache

http://redis.io/topics/lru-cache

By default storage in redis can overload your server's memory since by default max memory is zero


[php-proxy-app](https://github.com/Athlon1600/php-proxy-app)

**composer.json**

edit the **require** block of configuration parameters

![require](http://i.imgur.com/uYepBbW.png)

##  Configuration fo r"
