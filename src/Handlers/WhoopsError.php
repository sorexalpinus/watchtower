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
    /**
     * @param EventInterface $event
     * @return WhoopsError $this
     */
    public function handle(EventInterface $event)
    {
        $ww = new WhoopsWrapper();
        $whoopsHandler =new PrettyPageHandler();
        $whoopsHandler->handleUnconditionally(true);
        $this->output['main'] = $ww->handle($whoopsHandler,$event);
        $plainTextHandler = new PlainTextHandler();
        $this->output['plaintext'] = $ww->handle($plainTextHandler,$event);
        return $this;
    }

    /**
     *
     */
    public function afterSendToOutput() {
        if (PHP_SAPI !== 'cli')  {
            die;
        }
    }
}