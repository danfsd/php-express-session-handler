# Express Session Handler

This library is meant to work as a Session Handler for PHP that is compatible with [express-session](https://github.com/expressjs/session).

The main motivation for this was the need to share Session between a PHP application and a NodeJS application.

This was inspired on this [gist](https://gist.github.com/mscdex/9507b0d8df42e0aec825) by [mscdex](https://gist.github.com/mscdex), implementing some of its strategies with a `SessionHandler` implementation.

## Requirements

This library is meant to use the Save Handlers defined on `session.save_handler` directive, the library itself doesn't have any code regarding save handlers.

- **PHP**: `5.6` or greater
- **phpredis** or other Save Handler Extension. (you can install it using `pecl install redis`)
- **php_serialize** Serialize Handler.

**NOTE: this was tested using [express-session](https://github.com/expressjs/session) with the [connect-redis](https://github.com/tj/connect-redis) Session Store. This is supposed to work with any Session Store that stores it's data as JSON**.

## Installation

### Composer

Just run:
```
composer require danfsd/php-express-session-handler
```

## Setup

### Redis

You can set the following directive on `php.ini` like the following:

```
session.session_name = PHPSESSID
session.save_handler = redis
session.save_path = "tcp://127.0.0.1/?prefix=session:php:"
session.serialize_handler = php_serialize
```

Or you can set it using PHP's `ini_set` function like the following:
```php
ini_set("session.session_name", "PHPSESSID");
ini_set("session.save_handler", "redis");
ini_set("session.save_path", "tcp://127.0.0.1/?prefix=session:php:");
ini_set("session.serialize_handler", "php_serialize");
```

## Usage

```php
use danfsd\ExpressSessionHandler;

// This is the express-session's secret you defined in your NodeJS application
const SESSION_SECRET = "node.js";

$handler = new ExpressSessionHandler(SESSION_SECRET);

// Setting the Handler
session_set_save_handler($handler, true);

// Starting/Recoverying session
session_start();

// Populates $_SESSION['cookie'] with data that express-session requires
$handler->populateSession();

echo "<pre>";
var_dump($_SESSION);
var_dump($_COOKIE);
echo "</pre>"
```

More examples will be disclosed soon.