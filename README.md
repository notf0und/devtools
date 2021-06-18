# About Devtools

Devtools is group of useful console functions for development made with Laravel Framework

## Install

```bash
git clone https://gonzartur@bitbucket.org/gonzartur/devtools.git
cd devtools
composer install
```

Edit .env file and add your configuration. An example below:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=devtools
DB_USERNAME=devtools
DB_PASSWORD=secret

#To use the s3:user command, the following variables are needed
AWS_ACCESS_KEY_ID=aws-user-key
AWS_SECRET_ACCESS_KEY=aws-user-secret
AWS_DEFAULT_REGION=ap-southeast-2
```

If you don't need database functions, you can avoid this the following step and no database credentials are required on .env file:

```bash
php artisan migrate
```

## Available commands

###Import SSH configurations

Import SSH connections configured on ~/.ssh/config file

```bash
php artisan ssh:import
```

###Add a lagoon project

This command would connect to lagoon to get database configurations of a selected project.

```bash
php artisan lagoon:add
```

###Import database configurations

This command read mysql config files that contains login credentials and import them in application database.

```bash
php artisan database:import
```

###Create a database backup

It creates a a database babckup in .sql.bz format and stores it. Path of file created is displayed in console output:

```bash
php artisan database:backup
```

###Restore a database backup

It asks for the backup file to use and restore it to another selected database

```bash
php artisan database:restore
```

###Sync databases

This command work as backup and restore at the same time but piping between databases without dump file generation:

```bash
php artisan database:sync
```

###Create an AWS user with access to selected S3 buckets only

This command creates a policy, a group, a user, access keys and associate them in order to have a user that can access only to the selected S3 buckets

```bash
php artisan s3:user
```

###SSH into a docker container

Connects to a docker container running on local.

```bash
php artisan docker:ssh
```
