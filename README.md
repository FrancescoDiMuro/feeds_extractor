# Feeds Extractor

## Index
[1. Introduction](#introduction)<br>
[2. Database structure](#database-structure)<br>
[3. Setup](#setup)<br>
[4. Usage](#usage)

## Introduction
This tool has been created to extract data from a database, managing different type of information, described in details further in this document.

## Database structure
As mentioned before, this tool is able to extract data from a database, host for the following tables:
- feeds
- instagram_sources
- tiktok_sources
- posts

## Setup
In the following sections are described the various steps that are needed in order to setup the application and make it runnable.
Note that the host where you're going to run the application might already satisfy some of these requirements, so, if that's the case, feel free to skip them.

1. You need to have [PHP](https://www.php.net/) (>= 8.3.0) installed
2. You need to have [composer](https://getcomposer.org/) installed
3. You need to clone to [repository]() locally 
4. 
## Usage

## Procedure
As first task, I ran the command

```cmd
composer create-project laravel/laravel feeds_extractor
```

thanks to which a I created a new Laravel "empty" project.

After analysing what was the project structure, I individuated some entities, such as:
- Feed
- InstagramSource
- TikTokSource
- Post

Considering what thought above, I proceeded to create the models to connect the database to the application thanks to the Laravel Eloquent ORM, using the command for each model:

```cmd
php artisan make:model [ModelName] --migration
```
Thanks to the `--migration` flag, I was able to create both the model and the migration file as well.<br>
Once commands completed their execution, I started to update the code in the migrations, in order to create the various tables in the SQLite database.<br>
Since for every table, we don't want the columns for timestamps, I removed the below line of code from all the new migrations:

```php
$table->timestamps();
```

and added the below line of code in every model to achieve the same result:

```php
public $timestamps = false;
```

Next, I proceeded to update the structure of every model, adding the various attributes to each entity, and defining the relationships between then.<br>
<b>TODO:</b> maybe show the tables' structure?<br>

After adding all the attributes to the various models, I proceeded to use the command

```cmd
php artisan migrate --pretend
```

to check the DDL of the tables, and, once verified that those were corresponding to what I thought, I ran the command 

```cmd
php artisan migrate
```

launching all the new migrations.

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
protected $description = 'Extracts a feed and the associated sources, with an optional number of posts, using the feed id.';
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

Keeping up with the development of the command logic, I created a Service class to move all the heavy logic to this class,
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