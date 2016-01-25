## Installation
To install through composer, set the repository entry as below:
```
{
    "repositories": [
        {
            "type": "git",
            "url": "git@github.com:rudenyl-eeweb/acpclient.git"
        }
    ]
}
```

Then in your ```composer.json```, add:
```
{
    "require": {
        "rudenyl-eeweb/acpclient": "*"
    },
    "minimum-stability": "dev"
}
```


After the package has been installed, add the following service provider in your ```config/app.php```
```
'providers' => [
    ACPClient\ACPClientServiceProvider::class,
]
```


## Publishing
To publish the package's config, views and assets, run the following command in you terminal:
```
php artisan vendor:publish --provider="ACPClient\ACPClientServiceProvider"
```


.
