## Laravel Artisan Language ##

This package is to add artisan commands for language strings to Laravel>=5.4 project.

Because when you have all your base strings in your project you have no way to easily get them to create a JSON file for a locale. You have to check all files... So this package helps you and do that automaticaly, and do some other tasks like list all strings and synchronise a locale JSON file...

### Features ###

Add these 4 artisan commands :

- **language:strings** to list all project strings (in *app* and *resource/views* folders)
- **language:make** to create a JSON file for the locale filled with project strings
- **language:diff** to show differences between locale JSON file and project strings
- **language:sync** to synchronise locale JSON file with project strings

### Installation ###

Add package to your composer.json file :
```
    composer require bestmomo/laravel5-artisan-language
```

For Laravel 5.4 add service provider to `config/app.php` (with Laravel 5.5 there is the package discovery):
```
    Bestmomo\ArtisanLanguage\ArtisanLanguageProvider::class,
```
And it's done !

