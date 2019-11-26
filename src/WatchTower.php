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

    /** @var array $setupPointer */
    private $setupPointer;

    /** @var HandlerInterface[] $handlers */
    private $handlers;

    /** @var callable[] $filters */
    private $filters;

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
     * @param string|int $eventType
     * @param callable|null $filter
     * @return $this
     */
    public function watchFor($eventType,$filter = null)
    {
        if(!is_array($this->handlers)) {
            $this->handlers = [];
        }
        if (!array_key_exists($eventType,$this->handlers) or !is_array($this->handlers[$eventType])) {
            $this->handlers[$eventType] = [];
            if(isset($filter)) {
                $this->filters[$eventType] = $filter;
            }
        }
        $this->setupPointer = [
            'eventType' => $eventType
        ];
        return $this;
    }

    /**
     * @param $handler
     * @return $this
     * @throws WatchTowerException
     */
    public function thenCreate(HandlerInterface $handler)
    {
        if (isset($this->setupPointer['eventType'])) {
            $h = &$this->handlers[$this->setupPointer['eventType']];
            if (!is_array($h)) {
                $h = [];
            }
            $hClass = get_class($handler);
            $h[$hClass] = $handler;
            $this->setupPointer['handlerClass'] = $hClass;
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
        if (isset($this->setupPointer['eventType']) and isset($this->setupPointer['handlerClass'])) {
            /** @var HandlerInterface $handler */
            $handler = $this->handlers[$this->setupPointer['eventType']][$this->setupPointer['handlerClass']];
            if (is_object($handler)) {
                foreach ($outputTargets as $outputTarget) {
                    $handler->sendTo($outputTarget);
                }
            } else {
                throw new WatchTowerException(sprintf('Wrong configuration. Please make sure you use "watchFor" method first'), 12);
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
        //echo 'handle event...<br />';
        /** @var HandlerInterface[] $handlers */
        $handlers = $this->getGetHandlersFor($event);
        if (is_array($handlers)) {
            foreach ($handlers as $handler) {
                $handler
                    ->handle($event)
                    ->sendToOutputTargets($event);
            }
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
     * @return $this
     */
    protected function init()
    {
        ini_set('display_errors', 0);
        $this->setErrorHandler();
        $this->setExceptionHandler();
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
        set_error_handler(function ($code, $message, $file, $line) {
            $trace = debug_backtrace(false);
            $event = new ErrorEvent(compact('code', 'message', 'file', 'line', 'trace'));
            $this->handleEvent($event);
        });

        return $this;
    }


    /**
     * @return $this
     */
    protected function setExceptionHandler()
    {
        set_exception_handler(function ($exception) {
            $event = new ExceptionEvent($exception);
            $this->handleEvent($event);
        });

        return $this;
    }
}