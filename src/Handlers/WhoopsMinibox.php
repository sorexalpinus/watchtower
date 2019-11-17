<?php

namespace WatchTower\Handlers;

use WatchTower\Events\EventInterface;
use WatchTower\Wrappers\WhoopsWrapper;
use Whoops\Handler\PlainTextHandler;
use Whoops\Handler\PrettyPageHandler;


/**
 * Class InAppInfoBoxHandler display error output directly into browser on the top of the screen - use in development only
 * @package WatchTower
 */
class WhoopsMinibox extends Handler
{
    /**
     * @param EventInterface $event
     * @return $this
     */
    public function handle(EventInterface $event)
    {
        $ww = new WhoopsWrapper();
        $whoopsHandler = new PrettyPageHandler();
        $whoopsHandler->handleUnconditionally(true);
        $this->output = $ww->handle($whoopsHandler,$event,'minibox');
        $plainTextHandler = new PlainTextHandler();
        $this->outputVars['plaintext'] = $ww->handle($plainTextHandler,$event,'minibox');
        return $this;
    }
}