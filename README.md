# Installation
Project is based on [Symfony](http://symfony.com/)

First, [get Composer](https://getcomposer.org/download/), if you don't already use it.

Make sure you have installed php 8.1 or higher version.https://www.php.net/downloads.php

Make sure you have installed mysql https://www.mysql.com/downloads/, pdo mysql extension.

Then put your database credentials in `.env` file. (`.env.local`) for local development.

Next, run the following commands:

Install dependencies
```bash
composer install
```

Run command to create database
```bash
php bin/console doctrine:database:create
```

Run migrations command to create tables
```bash
php bin/console doctrine:migrations:migrate
```

Run command to generate jwt keys
```bash
php bin/console lexik:jwt:generate-keypair
```

Run server without symfony cli
```bash
php -S localhost:8000 -t public
```

Tests
Run command to create database for tests
```bash
php bin/console --env=test doctrine:database:create
```

Run command to create tables in test database
```bash
php bin/console --env=test doctrine:schema:create
```

Run tests
```bash
bin/phpunit
```
