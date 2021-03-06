Parcelforce expressTransfer API for Laravel 4
======================
[**Parcelforce exrpessTransfer**](http://www.parcelforce.com/)API for [**Laravel**](http://laravel.com/) to generate pre-advice electronic file that required by solution.

[![Build Status](https://travis-ci.org/alexpechkarev/parcelforce.svg?branch=master)](https://travis-ci.org/alexpechkarev/parcelforce)



Features
------------

 - Generating electronic file on the server
 - Submitting electronic file to [**Parcelforce**](http://www.parcelforce.com/)
 - Single or multiply consignment's per file
 - UK Domestic collection request (Label and receipt provided by PFW driver) 
 - UK Domestic services dispatches only (Label printed by customer)
 - Can be used as Laravel package or PHP standalone class


        
Requirements
------------

Must be [**Parcelforce**](http://www.parcelforce.com/account-customer/benefits-of-parcelforce-account) customer        
PHP >= 5.3        
MySQL        
[**Laravel**](http://laravel.com/) >= 4.1 if used as Laravel package



Installation
------------

```sh
composer require alexpechkarev/parcelforce  dev-master
```


Configuration
-------------

Once installed, register Laravel service provider, in `app/config/app.php`:

```php
'providers' => array(
	...
    'Parcelforce\ExpressTransfer\ParcelforceServiceProvider',
)
```

Publish configuration file:

```sh
php artisan config:publish parcelforce/expresstransfer --path vendor/alexpechkarev/parcelforce/src/config/
```

All generated file will be stored in `app/config/packages/parcelforce/expresstransfer/files` folder by default. 
Ensure this folder writable by the web server:
```sh
chmod o+w app/config/packages/parcelforce/expresstransfer/files
```

Name and location of this folder can be specified in the configuration file by editing `filePath` value, 
if changed ensure it's writable by we server.

```php
    /**
     * Location consignment files
     */        
    'filePath'                        => __DIR__.'/files/',
```


In the configuration file `app/config/packages/parcelforce/expresstransfer/config.php` please ensure that following parameters are set. 
For more details on configuration options and required values please contact [**Parcelforce**](http://www.parcelforce.com/contact-us).
By default these parameters preset with dummy values.
```
 'header_customer_account' 
 'header_generic_contract'
 'senderName'
 'senderAddress1'
 'senderPostTown'
 'senderPostcode'
 'dr_consignment_number' 
 'fileName' 
 'ftpUser' 
 'ftpPass'
 'ftpUploadPath'
 'ftpLocationPath'
```


Laravel Usage
-------------

Simply pass you data as array to `Parcelforce::process()` method. Electronic file will be generated for given data, 
stored at `filePath` location and submitted to Parcelforce. 

```php
        $senderData = array(
            array(
                "deliveryDetails"=>array(
                    'receiverName'      =>"MR CUSTOMER",
                    'receiverAddress1'  =>'100 CUSTOMER SOLUTIONS STREET',
                    'receiverPostTown'  =>'MILTON KEYNES',
                    'receiverPostcode'  =>'MK9 9AB'
                    )
            )           
        );

        Parcelforce::process($senderData);

        // generate file locally without submitting to Parcelforce
        Parcelforce::process($senderData, FALSE);
```



Multiply consignment data can be submitted in single request.

```php
        $senderData = array(
            array(
                "deliveryDetails"=>array(
                    'receiverName'      =>"MR CUSTOMER",
                    'receiverAddress1'  =>'100 CUSTOMER SOLUTIONS STREET',
                    'receiverPostTown'  =>'MILTON KEYNES',
                    'receiverPostcode'  =>'MK9 9AB'
                    )
            ),
            array(
                "deliveryDetails"=>array(
                    'receiverName'      =>"MR CUSTOMER",
                    'receiverAddress1'  =>'202 CUSTOMER SOLUTIONS STREET',
                    'receiverPostTown'  =>'MILTON KEYNES',
                    'receiverPostcode'  =>'MK9 9AB'
                    )
            )
        );

        Parcelforce::process($senderData);
```

By default collection date is set for tomorrow's date and can be amended in the configuration file, see `collectionDate`.
This value can also be specified at runtime using `Parcelforce::setDate()` method.
```php
    Parcelforce::setDate("next Monday");
    Parcelforce::process($senderData);
```
Dates handled by:
 
 - [**Carbon**](https://github.com/briannesbitt/Carbon) in package for Laravel
 - [**DateTime**](http://php.net/manual/en/book.datetime.php) in PHP standalone class  

Following formats accepted by `setDate()` method:

[**Relative Formats**](http://php.net/manual/en/datetime.formats.relative.php)

 - tomorrow
 - next wednesday
 - this thursday
 - ....


[**Date Formats**](http://php.net/manual/en/datetime.formats.date.php)

 - 2014-08-11
 - 08/11/2014
 - 20140811
 - ....



PHP standalone class Usage
-------------
Location: 'Parcelforce\ExpressTransfer\PHP\'

Standalone class have same methods as Laravel package and accepts consignment data in the same way.
Before use please ensure that required parameters are set in configuration file 'Parcelforce/ExpressTransfer/PHP/config.php' and
`Parcelforce/ExpressTransfer/PHP/files` folder is writable by web server.

```php
       $pf = new \Parcelforce\ExpressTransfer\PHP\Parcelforce();
       $pf->process($senderData));
       
       // generate file locally without submitting to Parcelforce
       $pf->process($senderData));
```

`setDate()` method is also available in standalone version.
```php
    ...
    $pf->setDate("next Monday");
    $pf->process($senderData);
```


Configuration testing
-------------

For testing and configuration purposes file can be generated without being submitted to Parcelforce.
To generate file locally simply pass `FALSE` as second parameter to `process()` method.
```php
    // In Laravel package
    Parcelforce::process($senderData, FALSE);

    // In PHP standalone class
    $pf->process($senderData, FALSE);
```

Once testing and configuration completed file and consignment numbers have to be reset to initial values.
To initiate process call `reset()` method. 
Please note: `reset()` will reload database tables and all the data will be lost.
See `config.php` for table details.
```php
    // In Laravel package
    Parcelforce::reset();

    // In PHP standalone class
    $pf->reset();
```



PHPUnit Testing
-------------

PHPUnit testing require [**Mockery**](https://github.com/padraic/mockery) flexible PHP mock object framework.
Run following command to install Mockery:
```
composer require mockery/mockery:dev-master@dev
```
In Laravel package test file needs copying from package folder into `app/tests/` folder, use following to do so:
```
cp vendor/alexpechkarev/parcelforce/tests/ParcelforceLaravelTest.txt app/tests/ParcelforceTest.php
```
And then run test:
```sh
phpunit app/tests/ParcelforceTest.php
```

To test PHP standalone file ensure database credentials are set in the config file.
```sh
    phpunit vendor/alexpechkarev/parcelforce/tests/ParcelforcePHPTest.php
```


Main Methods
-------------
```php
     /** 
     * @param array $data - array of data
     * @return string File content
     */
     public function process($data, $upload = TRUE)


    /**
     * Drop database tables
     */
    public function reset()



    /**
     * Get file content
     * @return string
     */
     public function getFileContent()


    /**
     * Get current instance config file
     * @return array
     */
    public function getConfig()
```



Support
-------------

[Please open an issue on GitHub](https://github.com/alexpechkarev/parcelforce/issues)


License
-------------

Parcelforce expressTransfer API for Laravel 4 is released under the MIT License. See the bundled
[LICENSE](https://github.com/alexpechkarev/parcelforce/blob/master/LICENSE)
file for details.