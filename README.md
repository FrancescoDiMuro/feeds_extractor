# Feeds Extractor

## Index
[1. Introduction](#introduction)<br>
[2. Database structure](#database-structure)<br>
[3. Setup](#setup)<br>
[4. Usage](#usage)

## Introduction
This tool has been created to copy entries from a database to another, managing different type of information, described in details further in this document.

## Database structure
As mentioned before, this tool is able to copy data from a database to another, both with the following common tables:
- feeds
- instagram_sources
- tik_tok_sources
- posts

## Setup
In the following sections are described the various steps that are needed in order to setup the application and make it runnable.
Note that the host where you're going to run the application might already satisfy some of these requirements, so, if that's the case, feel free to skip them.

1. You need to have [PHP](https://www.php.net/) (>= 8.3.0) installed
2. You need to have [composer](https://getcomposer.org/) (>= 2.6.6) installed
3. You need to clone the [repository](https://github.com/FrancescoDiMuro/feeds_extractor) locally
4. You need to create two files in the `database` project folder:
    - sqlite_source.sqlite
    - sqlite_target.sqlite

## Usage
Once you've correctly setup up the application, you're ready to run the application with:
```cmd
php artisan copy <feedId> [options]
```

where `feedId` is a positive integer greater than zero, and options are `--only=instagram|tiktok` and '--include-posts=n`, with n as a positive integer number grater than zero.

In case of bad or missing inputs, the user will be shown which are the errors.

## Procedure
As first task, I ran the command

```cmd
composer create-project laravel/laravel feeds_extractor
```

thanks to which I created a new Laravel "empty" project.

After analysing what was the project structure, I individuated some entities, such as:
- Feed
- InstagramSource
- TikTokSource
- Post

Considering what reported above, I proceeded to create the models to connect the database to the application thanks to the Laravel Eloquent ORM, using the command for each model:

```cmd
php artisan make:model [ModelName] --migration
```
Thanks to the `--migration` flag, I was able to create both the model and the migration file as well.<br>
Once commands completed their execution, I started to update the code in the migrations, in order to create the various tables in the SQLite databases.<br>
Since for every table, we don't want the columns for timestamps, I removed the following line of code from all the new migrations:

```php
$table->timestamps();
```

and added the following line of code in every model to achieve the same result:

```php
public $timestamps = false;
```

Since the project requires the copy of data from a database to another, I had to update the project `database.php` to be able to "communicate" with two databases, adding the following lines of code:

```php
'sqlite_source' => [
    'driver' => 'sqlite',
    'url' => env('DB_URL_SOURCE'),
    'database' => env('DB_DATABASE_SOURCE', database_path('source.sqlite')),
    'prefix' => '',
    'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
],

'sqlite_target' => [
    'driver' => 'sqlite',
    'url' => env('DB_URL_TARGET'),
    'database' => env('DB_DATABASE_TARGET', database_path('target.sqlite')),
    'prefix' => '',
    'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
],
```

Next, I proceeded to update the structure of every model, adding the various attributes to each entity, and defining the relationships between them.<br>

After adding all the attributes to the various models, I proceeded to use the command

```cmd
php artisan migrate --pretend
```

to check the tables' creation queries, and, once verified that those were corresponding to what I expected, I ran the command

```cmd
php artisan migrate --database=<connection_name>
```

launching all the new migrations for each connection (two).

Moving forward, I launched the command below to create the command `copy`

```cmd
php artisan make:command --command=copy CopyFeed
```

that, once typed in the console, invokes the logic in the class `CopyFeed`.

Once the command has been created, I proceeded to define its arguments and options through the `$signature` attribute of the command class:

```php
protected $signature = 'copy 
{feedId : Id of the feed to extract} 
{--only= : Specifies from which source extract the feeds} 
{--include-posts= : Specifies the number of posts to extract}';
```

Defining the `$description` attribute, I was able to set a description for the command:

```php
protected $description = 'Copy a feed and related data from a source db to a target db (see options).';
```

Since the command has a mandatory parameter (`feedId`), I proceeded to implement the interface `PromptsForMissingInput`:

```php
class CopyFeed extends Command implements PromptsForMissingInput
```

and, to have a custom message when the argument is missing, I added the method `promptForMissingArgumentsUsing`:

```php
protected function promptForMissingArgumentsUsing(): array {
    return [
        'feedId' => ['Which feed do you want to extract?', 'E.g. 123']
    ];
}
```

Keeping up with the development of the command logic, I created a Service class where to move all the heavy logic,
leaving just the argument and options obtaining to the CopyFeed class, and the respective return value.

While developing the service core logic, I wanted to do some tests, ending up creating a factory for each model,
in order to seed the database with some data and see the results of the service logic.

Proceeding step by step:<br>
1. I created the factory for each model with the command

    ```cmd
    php artisan make:factory [FactoryName]
    ```

2. I added the `factory()` for the `Feed` model to the `DatabaseSeeder.php` file:
    ```php
    Feed::factory()
        ->count(10)
        ->has(InstagramSource::factory()->count(1))
        ->has(TikTokSource::factory()->count(1))
        ->hasPosts(10)
        ->create();
    ```
3. I ran the command
    ```cmd
    php artisan db:seed --database=sqlite_source
    ```
    (so just the source database would be filled with this data).

Once these steps have been completed, I could run some tests while creating the business logic of the application.

During the development, I noticed that I needed to create the records in the target database, and so I had to be able to fill the data of a record through their properties' names, ending up to add the `$fillable` property to each model definition (different for almost every model):

```php
protected $fillable = [
    'id',
    'name'
];
```

In this way, I could be able to mass create the records in the target database.

Since I wanted to have a visual feedback about the data copy from a database to another, I developed an utility function that showed me the various records after the copy:

```php
public function retrieveFeed(string $connection, int $feedId, ?string $only = null, ?int $includePosts = null)
```

After hours of developing, I finally made the application work, being able to proceed to the next step: unit testing.

To start with this step, I ran the command
```cmd
php artisan make:test CopyFeedTest --unit
```

With the flag `--unit`, the nature of the test would be unit, and not feature.

After creating the test, I proceeded to update the code in the `phpunit.xml`, adding the possibility to connect to two different databases.

Proceeding with the test creation, for some reasons, if I tried to run them with this line of code

```php
use PHPUnit\Framework\TestCase;
```

I always ended up receiving an error, and so, after hours of research on internet why this was happening, I found a solution replacing the above line of code with

```php
use Illuminate\Foundation\Testing\TestCase;
```

This had let me continue with the developing the tests, ending up writing a total of five tests, to test the functionality of the application with different scenarios.

To run the tests, I used the command
```cmd
php artisan test --filter CopyFeedTest
```
