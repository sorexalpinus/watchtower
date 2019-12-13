<?php

namespace WatchTower\Handlers;

use WatchTower\Events\EventInterface;
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
     * @var string
     */
    private $contentType = 'minibox';

    /**
     * @param EventInterface $event
     * @return $this
     */
    public function handle(EventInterface $event)
    {
        $ww = new WhoopsWrapper();
        $whoopsHandler = new PrettyPageHandler();
        $whoopsHandler->handleUnconditionally(true);
        if ($this->contentType == 'minibox') {
            $box = $this->createMinibox($event);
        } else {
            $box = $ww->handle($whoopsHandler, $event);
        }
        $this->output = $box;
        $plainTextHandler = new PlainTextHandler();
        $this->outputVars['plaintext'] = $ww->handle($plainTextHandler, $event);
        return $this;
    }

    /**
     * @param string $contentType : "minibox" or "mainbox"
     * @return $this
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
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
     * @param string $html
     * @param EventInterface $event
     * @return string $html
     */
    protected function createMinibox(EventInterface $event)
    {
        $handlerClone = clone $this;
        $handlerClone->setContentType('mainbox');
        $sHandler = base64_encode(serialize($handlerClone));
        $sEvent = base64_encode(serialize($event));
        $readerPath = 'http://watchtower.local/getbox.php';
        $template = file_get_contents(WATCHTOWER_RROOT . '/html/WhoopsMinibox.html');
        $html = str_replace(
            [':eventName', ':eventMessage', ':eventFile', ':eventLine', ':eventId', ':readerPath', ':sEvent', ':sHandler'],
            [$event->getName(), $event->getMessage(), $event->getFile(), $event->getLine(), $event->getId(), $readerPath, $sEvent, $sHandler], $template);
        return $html;
    }


}