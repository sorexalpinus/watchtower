<?php

namespace WatchTower\Exceptions;

/**
 * Class ExceptionHandlerException
 * @package WatchTower\Exceptions
 */
class WatchTowerException extends \ErrorException implements WatchTowerAwareException
{
    /** @var string $title */
    protected $title = 'WatchTower exception';

    /**
     * @return WatchTowerAwareException|void
     */
    public function handle()
    {

    }



    /**
     * @return string $title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return array $extraInfo
     */
    public function getExtraInfo()
    {
        return [];
    }

    public function getFullMessage() {
        return '<pre>'. (new \ReflectionClass($this))->getShortName() . ' ' . $this->getCode() . ': ' . $this->getMessage() . ' in ' . $this->getFile() . ':' . $this->getLine() . PHP_EOL . 'Trace: ' . PHP_EOL . $this->getTraceAsString() . '</pre>';
    }

}