# watchtower

Error & Exception handler for PHP 7+

##Installation

Install the latest version with

```bash
$ composer require sorexalpinus/watchtower
```

## Basic Usage

```php
<?php
    use WatchTower\WatchTower;
    use WatchTower\Handlers\WhoopsMinibox;
    use WatchTower\Outputs\Browser;
    
    $wt = WatchTower::getInstance();
    $wt->watchFor(E_WARNING | E_NOTICE)
        ->thenCreate(WhoopsMinibox::create())
        ->andSendTo(Browser::create());
    $wt->watch();
```


##Author
Juraj Hlatky (sorexalpinus)

##License
WatchTower is licensed under the MIT License - see the LICENSE file for details

##Special thanks
This library uses modified Whoops (filp/whoops) package
