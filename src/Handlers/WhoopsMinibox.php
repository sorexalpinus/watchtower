<?php

namespace WatchTower\Handlers;

use WatchTower\Events\EventInterface;
use WatchTower\Exceptions\WatchTowerException;
use WatchTower\Outputs\OutputTargetInterface;
use WatchTower\WatchTower;
use WatchTower\Wrappers\WhoopsWrapper;
use Whoops\Handler\PlainTextHandler;
use Whoops\Handler\PrettyPageHandler;


/**
 * Class InAppInfoBoxHandler display error output directly into browser on the top of the screen - use in development only
 *
 * @package WatchTower
 */
class WhoopsMinibox extends Handler
{
    /**
     * @param EventInterface $event
     * @return $this
     * @throws WatchTowerException
     */
    public function handle(EventInterface $event)
    {
        $ww = new WhoopsWrapper();
        $whoopsHandler = new PrettyPageHandler();
        $whoopsHandler->handleUnconditionally(true);
        $this->output['minibox'] = $this->createMinibox($event);
        $this->output['main'] = $ww->handle($whoopsHandler, $event);
        $plainTextHandler = new PlainTextHandler();
        $this->output['plaintext'] = $ww->handle($plainTextHandler, $event);
        return $this;
    }

    /**
     * @return string $initialOutput
     */
    protected function getOutputStart()
    {
        $style = '<style>' . file_get_contents(WATCHTOWER_RROOT . '/css/WhoopsMinibox.css') . '</style>';
        $jquery = '<script>' . file_get_contents(WATCHTOWER_RROOT . '/js/jquery.min.js') . '</script>';
        $script = '<script>' . file_get_contents(WATCHTOWER_RROOT . '/js/WhoopsMinibox.js') . '</script>';
        return $style . $jquery . $script;
    }

    /**
     * @param EventInterface $event
     * @return string $html
     * @throws WatchTowerException
     */
    protected function createMinibox(EventInterface $event)
    {
        try {
            $handlerClone = clone $this;
            $sHandler = base64_encode(serialize($handlerClone));
            $sEvent = base64_encode(serialize($event));
        }
        catch (\Throwable $e) {
            $sHandler = $sEvent = '';
            WatchTower::log($e);
        }
        finally {
            $readerPath = WatchTower::getInstance()->getConfig('watchtower.reader');
            $template = file_get_contents(WATCHTOWER_RROOT . '/html/WhoopsMinibox.html');
            $html = str_replace(
                [':eventName', ':eventMessage', ':eventFile', ':eventLine', ':eventId', ':readerPath', ':sEvent', ':sHandler'],
                [$event->getName(), $event->getMessage(), $event->getFile(), $event->getLine(), $event->getId(), $readerPath, $sEvent, $sHandler], $template);
        }
        return $html;
    }

    /**
     * @param OutputTargetInterface $outputTarget
     * @param EventInterface $event
     * @param array $globalVars
     */
    protected function sendToTarget($outputTarget,$event,$globalVars) {

        switch(get_class($outputTarget)) {
            case 'WatchTower\Outputs\Browser': {
                $output = $this->getOutput('minibox'); break;
            }
            case 'WatchTower\Outputs\File': {
                $output = $this->getOutput('main'); break;
            }
            case 'WatchTower\Outputs\Email':
            default: {
                $output = $this->getOutput('plaintext'); break;
            }
        }
        $outputTarget->execute($event, $output, $globalVars);
    }


}