Parcelforce API for Lavarel 4
======================



Installation
------------


To install edit `composer.json` and add following line:

```javascript
"alexpechkarev/parcelforce": "dev-master"
```

Run `composer update`



Configuration
-------------

Once installed, register Laravel service provider, in your `app/config/app.php`:

```php
'providers' => array(
	...
    'Alexpechkarev\Parcelforce\ParcelforceServiceProvider',
)
```


Publish configuration file:

```
$ php artisan config:publish alexpechkarev/parcelforce --path vendor/alexpechkarev/parcelforce/src/config/
```

Make files folder writable by web server

```php
chmod o+w app/config/packages/alexpechkarev/parcelforce/files
```

Publish package migrations.

```php
$ php artisan migrate:publish alexpechkarev/parcelforce
```

Publish package migrations, ensure that database credentials are set.

```php
$ php artisan migrate 
```