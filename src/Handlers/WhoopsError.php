<?php

namespace WatchTower\Handlers;

use WatchTower\Events\EventInterface;
use WatchTower\Wrappers\WhoopsWrapper;
use Whoops\Handler\PlainTextHandler;
use Whoops\Handler\PrettyPageHandler;


/**
 * Class WhoopsError
 * @package WatchTower\Handlers
 */
class WhoopsError extends Handler
{
    protected $defaultConfig = [];

    /**
     * @param EventInterface $event
     * @return WhoopsError $this
     */
    public function handle(EventInterface $event)
    {
        $ww = new WhoopsWrapper();
        $whoopsHandler =new PrettyPageHandler();
        $whoopsHandler->handleUnconditionally(true);
        $this->output = $ww->handle($whoopsHandler,$event,'error-page');
        $plainTextHandler = new PlainTextHandler();
        $this->outputVars['plaintext'] = $ww->handle($plainTextHandler,$event,'error-page');
        return $this;
    }
}