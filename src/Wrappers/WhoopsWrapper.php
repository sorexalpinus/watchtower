<?php
namespace WatchTower\Wrappers;

use WatchTower\Events\EventInterface;
use WatchTower\Events\ExceptionEvent;
use WatchTower\Exceptions\WatchTowerAwareException;
use Whoops\Handler\Handler;
use Whoops\Run;

/**
 * Class WhoopsWrapper
 * @package WatchTower
 */
class WhoopsWrapper
{

    /**
     * @param Handler $handler
     * @param EventInterface $event
     * @return array|mixed
     */
    public function handle(Handler $handler,EventInterface $event) {

        $xDbgWasEnabled = false;
        if (extension_loaded('xdebug') and xdebug_is_enabled()) {
            $xDbgWasEnabled = true;
            xdebug_disable();
        }
        $whoops = new Run();
        $whoops->allowQuit(false);
        $whoops->writeToOutput(false);
        $whoops->appendHandler($handler);
        $html = $whoops->handleException($event->getException());
        $html = $this->addExtraInfo($html,$event);
        if ($xDbgWasEnabled) {
            xdebug_disable();
        }
        return $html;
    }



    /**
     * @param string $html
     * @param EventInterface $event
     * @return string $html
     */
    protected function addExtraInfo($html, EventInterface $event) {
        if ($event instanceof ExceptionEvent and $event->getException() instanceof WatchTowerAwareException) {
            $extraInfo = $event->getException()->getExtraInfo();
            $fragment = '<div class="details"';
            $pos = strpos($html,$fragment);
            $extraDetails = '<h2 class="details-heading">'.$event->getException()->getTitle() . ': </h2>';
            foreach($extraInfo as $label => $text) {
                $extraDetails .= '<div class="data-table-container" id="data-tables"><label>'.ucfirst($label).'</label><div>'.ucfirst($text).'</div></div>';
            }
            $extraDetails = ' <div class="details">'.$extraDetails.'</div>';
            $html = substr_replace($html, $extraDetails, $pos, 0);
        }
        return $html;
    }
}