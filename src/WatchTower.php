<?php

namespace WatchTower;

use Exception;
use WatchTower\Events\ErrorEvent;
use WatchTower\Events\EventInterface;
use WatchTower\Events\EventTrait;
use WatchTower\Events\ExceptionEvent;
use WatchTower\Exceptions\WatchTowerException;
use WatchTower\Handlers\HandlerInterface;
use WatchTower\Outputs\OutputTargetInterface;

/**
 * Class WatchTower
 * @package WatchTower
 */
class WatchTower
{
    use EventTrait;

    /** @var WatchTower $instance */
    static private $instance;

    /** @var bool $enabled */
    private $enabled = false;

    /** @var bool $initialized */
    private $initialized = false;

    /** @var HandlerInterface[] $handlers */
    private $handlers;

    /** @var callable[] $filters */
    private $filters;

    /** @var array $setup */
    private $setup;

    /** @var EventBuffer $eventBuffer */
    private $eventBuffer;

    /** @var int $maxBufferSize */
    private $maxBufferSize = 10;

    /**
     * @return WatchTower $instance
     */
    static public function getInstance()
    {
        if (!is_object(self::$instance)) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    /**
     * @return WatchTower $WatchTower
     */
    public function enable()
    {
        if (!$this->initialized) {
            $this->init();
        }
        $this->enabled = true;
        return $this;
    }

    /**
     * @return WatchTower
     */
    public function disable()
    {
        $this->enabled = false;
        $this->initialized = false;
        return $this;
    }

    /** bool $initialized */
    public function isInitialized()
    {
        return $this->initialized;
    }

    static public function destroyInstance()
    {
        self::$instance = null;
    }

    /**
     * @return bool $enabled
     */
    public function isEnabled()
    {
        return $this->enabled;
    }


    /**
     * @param string|int|array $eventType
     * @param callable|null $filter
     * @return $this
     */
    public function watchFor($eventType,$filter = null)
    {
        $this->setup = [];
        $eventTypes = is_array($eventType) ? $eventType : [$eventType];
        if(!is_array($this->handlers)) {
            $this->handlers = [];
        }
        foreach($eventTypes as $eventType) {
            $this->setEventType($eventType,$filter);
        }
        return $this;
    }

    /**
     * @param $handler
     * @return $this
     * @throws WatchTowerException
     */
    public function thenCreate(HandlerInterface $handler)
    {
        if (is_array($this->setup)) {
            foreach($this->setup as $eventType => $emptyArray) {
                $hClone = clone $handler;
                $h = &$this->handlers[$eventType];
                if (!is_array($h)) {
                    $h = [];
                }
                $hClass = get_class($hClone);
                $h[$hClass] = $hClone;
                $this->setup[$eventType] = $hClass;
            }
        } else {
            throw new WatchTowerException(sprintf('Wrong configuration. Please make sure you use "watchFor" method first'), 12);
        }
        return $this;
    }

    /**
     * @param OutputTargetInterface[]|OutputTargetInterface $outputTargets
     * @return WatchTower
     * @throws WatchTowerException
     */
    public function andSendTo($outputTargets)
    {
        $outputTargets = is_array($outputTargets) ? $outputTargets : [$outputTargets];
        if (is_array($this->setup)) {
            foreach($this->setup as $eventType => $handlerClass) {
                /** @var HandlerInterface $handler */
                $handler = $this->handlers[$eventType][$handlerClass];
                if (is_object($handler)) {
                    foreach ($outputTargets as $outputTarget) {
                        $handler->sendTo($outputTarget);
                    }
                } else {
                    throw new WatchTowerException(sprintf('Wrong configuration. Please make sure you use "watchFor" method first'), 12);
                }
            }
        } else {
            throw new WatchTowerException(sprintf('Wrong configuration. Please make sure you use "watchFor" method first'), 12);
        }
        return $this;
    }


    /**
     * @return WatchTower
     * @throws WatchTowerException
     */
    public function watch()
    {
        if (!empty($this->handlers)) {
            $this->enable();
        } else {
            throw new WatchTowerException('Empty configuration: nothing to watch for', 14);
        }

        return $this;
    }


    /**
     * @param Exception $exception
     * @return bool $result
     */
    public function handleException(Exception $exception)
    {
        $result = false;
        if ($this->isEnabled()) {
            $event = new ExceptionEvent($exception);
            $result = $this->handleEvent($event);
        }
        return $result;
    }


    /**
     * @param EventInterface $event
     * @return bool $result
     */
    public function handleEvent(EventInterface $event)
    {
        /** @var HandlerInterface[] $handlers */
        $handlers = $this->getGetHandlersFor($event);
        if (is_array($handlers) and sizeof($handlers) > 0) {
            foreach ($handlers as $handler) {
                $handler
                    ->handle($event)
                    ->sendToOutputTargets($event);
            }
            $event->setHandled(true);
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return $this
     */
    public function reset() {
        $this->handlers = null;
        return $this;
    }

    /**
     * @param string|int $eventType
     * @param callable|null $filter
     * @return $this
     */
    protected function setEventType($eventType,$filter = null) {
        if (!array_key_exists($eventType,$this->handlers) or !is_array($this->handlers[$eventType])) {
            $this->handlers[$eventType] = [];
            if(isset($filter)) {
                $this->filters[$eventType] = $filter;
            }
        }
        $this->setup[$eventType] = [];
        return $this;
    }




    /**
     * @return $this
     */
    protected function init()
    {
        ini_set('display_errors', 0);
        $this->setErrorHandler();
        $this->setExceptionHandler();
        $this->setShutdown();
        $this->eventBuffer = EventBuffer::create();
        $this->initialized = true;
        return $this;
    }

    /**
     * @param EventInterface $event
     * @return HandlerInterface[] $handlers
     */
    protected function getGetHandlersFor(EventInterface $event)
    {
        $found = [];
        if (is_array($this->handlers)) {
            foreach ($this->handlers as $eventCategory => $handlers) {
                if ($event->isCategoryMatch($eventCategory)) {
                    $filter = $this->getFilterFor($eventCategory);
                    if (!$filter or $event->passedThroughFilter($filter)) {
                        $found = $handlers;
                        break;
                    }
                }
            }
        }
        return $found;
    }

    /**
     * @param string|int $eventCategory
     * @return callable|false $filter
     */
    protected function getFilterFor($eventCategory) {
        return is_callable($this->filters[$eventCategory]) ? $this->filters[$eventCategory] : false;
    }

    /**
     * @return $this
     */
    protected function setErrorHandler()
    {
        $scope = $this->getOverallErrScope();
        set_error_handler(function ($code, $message, $file, $line) {
            $errorInfo = compact('code', 'message', 'file', 'line');
            if($this->eventBuffer->canPush('error',$errorInfo)) {
                $errorInfo['trace'] = debug_backtrace(false);
                $event = new ErrorEvent($errorInfo);
                $this->eventBuffer->push($event);
                $this->handleEvent($event);
            }
        },$scope);
        return $this;
    }

    /**
     * @return $this
     */
    protected function setExceptionHandler()
    {
        set_exception_handler(function ($exception) {
            if($this->eventBuffer->canPush('exception',$exception)) {
                $event = new ExceptionEvent($exception);
                $this->eventBuffer->push($event);
                $this->handleEvent($event);
            }
        });

        return $this;
    }

    /**
     * @return int $scope
     */
    protected function getOverallErrScope() {
        $scope = 0;
        foreach(array_keys($this->handlers) as $eventType) {
            if(is_numeric($eventType)) {
                $scope |= $eventType;
            }
        }
        return $scope;
    }

    /**
     * @return $this
     */
    protected function setShutdown() {
        register_shutdown_function(function () {
            $lastError = error_get_last();
            if(!empty($lastError)) {
                $trace = debug_backtrace(false);
                $lastError['trace'] = $trace;
                $lastError['code'] = $lastError['type'];
                unset($lastError['type']);
                $lastError = $this->exceptionForbiddenConvert($lastError);
                $event = new ErrorEvent($lastError);
                $this->handleEvent($event);
            }
        });
        return $this;
    }

    /**
     * TODO:separate this into a utility class/trait
     * @param array $errorInfo
     * @return array $errorInfoModified
     */
    protected function exceptionForbiddenConvert($errorInfo) {
        if(strpos($errorInfo['message'],'must not throw an exception') !== false) {
            $msg = $errorInfo['message'];
            $matches = [];
            preg_match('/in (.*?) on line \d+/',$msg,$matches);
            if(is_array($matches) and sizeof($matches) > 0) {
                $m = reset($matches);
                $m = trim(str_replace(['on line ','in '],['',''],$m));
                $fl = explode(' ',$m);
                $errorInfo['file'] = $fl[0];
                $errorInfo['line'] = $fl[1];
            }
            return is_array($errorInfo) ? $errorInfo : [];
        }
        else {
            return $errorInfo;

        }
    }


}