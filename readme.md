## About Devtools

Devtools is group of useful console functions for development made with Laravel Framework

## Install

```bash
git clone ...
cd devtools
composer install
```

Edit .env file and add your database and AWS configuration, example below:

```dotenv
DB_CONNECTION=mysql
DB_HOST=homestead
DB_PORT=3306
DB_DATABASE=devtools
DB_USERNAME=homestead
DB_PASSWORD=secret

AWS_ACCESS_KEY_ID=aws-user-key
AWS_SECRET_ACCESS_KEY=aws-user-secret
AWS_DEFAULT_REGION=ap-southeast-2
```


If you dont need database functions, you can avoid this the following step and no database credentials are required on .env file:

```bash
php artisan migrate
```

If you dont need AWS functions, AWS credentials are not required.

## Available commands

####Import database configurations
```bash
php artisan database:cim
```

This command read mysql config files that contains login credentials and import them in application database.

####Create a database backup
It creates a a database babckup in .sql.bz format and stores it. Path of file created is displayed in console output:
```bash
php artisan database:backup
```

####Restore a database backup
It asks for the backup file to use and restore it to another selected database
```bash
php artisan database:restore
```

####Baskup and restore a database
Same as backup and restore commands combined:
```bash
php artisan database:br
```

####Create an AWS user with access to selected S3 buckets only
This commands creates a policy, a group, a user and access keys and associate them in order to have a user that can access only to the selected S3 buckets
```bash
php artisan s3:user
```
