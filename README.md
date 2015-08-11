# php-proxy-plugin-cache

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

LRU-Cache

http://redis.io/topics/lru-cache

By default storage in redis can overload your server's memory since by default max memory is zero

