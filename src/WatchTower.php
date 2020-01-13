<?php

namespace WatchTower;

use ReflectionException;
use Throwable;
use WatchTower\Events\ErrorEvent;
use WatchTower\Events\EventInterface;
use WatchTower\Events\EventTrait;
use WatchTower\Events\ExceptionEvent;
use WatchTower\Exceptions\WatchTowerException;
use WatchTower\Handlers\HandlerInterface;
use WatchTower\Outputs\OutputTargetInterface;

/**
 * Class WatchTower
 *
 * @package WatchTower
 */
class WatchTower
{
    use EventTrait;

    /** @var WatchTower $instance */
    static private $instance;

    static private $logfile = '/log/log';

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

    /** @var array $config */
    private $config;

    /**
     * WatchTower constructor.
     *
     * @param array $config
     */
    public function __construct($config)
    {
        $this->config = $config;

    }

    /**
     * @param array $config
     * @return WatchTower|static
     */
    static public function create($config)
    {
        self::$instance = new static($config);
        return self::$instance;
    }

    /**
     * @return WatchTower $instance
     * @throws WatchTowerException
     */
    static public function getInstance()
    {
        if (is_object(self::$instance)) {
            return self::$instance;
        } else {
            throw new WatchTowerException('WatchTower instance was not created yet. Use WatchTower::create().', 27);
        }
    }

    /**
     * @return WatchTower $WatchTower
     * @throws WatchTowerException
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

    /**
     *
     */
    static public function destroy()
    {
        self::$instance = null;
    }

    /**
     * For internal logging.
     */
    static public function log($msg)
    {

        $f = @fopen(WATCHTOWER_FROOT . self::$logfile, 'a+');
        @fwrite($f, date('Y-m-d H:i:s') . ' ' . $msg . PHP_EOL);
        @fclose($f);
    }

    /**
     * @return bool $enabled
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param array $request
     * @return string $out
     * @throws WatchTowerException
     */
    public function getBox($request)
    {
        $out = '';
        array_map(function ($val) {
            return addslashes($val);
        }, $request);
        if ($request['type'] == 'file') {
            $filepath = base64_decode($request['path']);
            if (file_exists($filepath)) {
                echo file_get_contents($filepath);
            } else {
                throw new WatchTowerException(sprintf('Could not find file to read: %s', $filepath), 28);
            }
        } elseif ($request['type'] == 'generate') {
            $event = @unserialize(@base64_decode($request['event']));
            $handler = @unserialize(@base64_decode($request['handler']));
            if ($event instanceof EventInterface and $handler instanceof HandlerInterface) {
                $out = $handler->handle($event)->getOutput();
            } else {
                throw new WatchTowerException('Wrong parameters provided.', 25);
            }
        }
        return $out;
    }


    /**
     * @param string|int|array $eventType
     * @param callable|null $filter
     * @return $this
     */
    public function watchFor($eventType, $filter = null)
    {
        $this->setup = [];
        $eventTypes = is_array($eventType) ? $eventType : [$eventType];
        if (!is_array($this->handlers)) {
            $this->handlers = [];
        }
        foreach ($eventTypes as $eventType) {
            $this->setEventType($eventType, $filter);
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
            foreach ($this->setup as $eventType => $emptyArray) {
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
            foreach ($this->setup as $eventType => $handlerClass) {
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
     * @param Throwable $exception
     * @return bool $result
     * @throws ReflectionException
     * @throws WatchTowerException
     */
    public function handleException(Throwable $exception)
    {
        $result = false;
        if ($this->isEnabled()) {
            if ($this->getEventBuffer()->canPush('exception', $exception)) {
                $event = new ExceptionEvent($exception);
                $result = $this->handleEvent($event);
                $this->getEventBuffer()->push($event);
            }
        }
        return $result;
    }


    /**
     * @param EventInterface $event
     * @return bool $result
     * @throws WatchTowerException
     */
    public function handleEvent(EventInterface $event)
    {
        $handlers = $this->getGetHandlersFor($event);
        if (is_array($handlers) and sizeof($handlers) > 0) {
            /** @var HandlerInterface $handler */
            foreach ($handlers as $handler) {
                $handler->handle($event);
                $targets = $handler->getOutputTargets();
                $canReport = [];
                foreach ($targets as $key => $target) {
                    $canReport[$key] = $this->getEventBuffer()->canReport($event, $handler, $target);
                }
                $handler->sendToOutputTargets($event, $canReport);
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
    public function reset()
    {
        $this->handlers = null;
        return $this;
    }

    /**
     * @param string $item
     * @return array|string|false
     */
    public function getConfig($item = '')
    {
        if (!empty($item)) {
            if (isset($this->config[$item])) {
                return $this->config[$item];
            } else {
                return false;
            }
        } else {
            return $this->config;
        }
    }

    /**
     * @param string|int $eventType
     * @param callable|null $filter
     * @return $this
     */
    protected function setEventType($eventType, $filter = null)
    {
        if (!array_key_exists($eventType, $this->handlers) or !is_array($this->handlers[$eventType])) {
            $this->handlers[$eventType] = [];
            if (isset($filter)) {
                $this->filters[$eventType] = $filter;
            }
        }
        $this->setup[$eventType] = [];
        return $this;
    }


    /**
     * @return $this
     * @throws WatchTowerException
     */
    protected function init()
    {
        ini_set('display_errors', 0);
        $this->setErrorHandler();
        $this->setExceptionHandler();
        $this->setShutdown();
        $this->initialized = true;
        return $this;
    }

    /**
     * @return EventBuffer $eventBuffer
     * @throws WatchTowerException
     */
    protected function getEventBuffer()
    {
        if (!isset($this->eventBuffer)) {
            ;
            $this->eventBuffer = EventBuffer::create();
        }
        return $this->eventBuffer;

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
    protected function getFilterFor($eventCategory)
    {
        return (array_key_exists($eventCategory,$this->filters) and is_callable($this->filters[$eventCategory])) ? $this->filters[$eventCategory] : false;
    }

    /**
     * @return $this
     */
    protected function setErrorHandler()
    {
        $scope = $this->getOverallErrScope();
        set_error_handler(function ($code, $message, $file, $line) {
            try {
                $errorInfo = compact('code', 'message', 'file', 'line');
                if ($this->getEventBuffer()->canPush('error', $errorInfo)) {
                    $errorInfo['trace'] = debug_backtrace(false);
                    $event = new ErrorEvent($errorInfo);
                    $this->getEventBuffer()->push($event);
                    $this->handleEvent($event);
                }
            } catch (Throwable $e) {
                self::log(get_class($e) . '; ' . $e->getMessage() . ' ' . $e->getFile() . ':' . $e->getLine());
            }

        }, $scope);
        return $this;
    }

    /**
     * @return $this
     */
    protected function setExceptionHandler()
    {
        set_exception_handler(function ($exception) {
            try {
                if ($this->getEventBuffer()->canPush('exception', $exception)) {
                    $event = new ExceptionEvent($exception);
                    $this->getEventBuffer()->push($event);
                    $this->handleEvent($event);
                }
            } catch (Throwable $e) {
                self::log(get_class($e) . '; ' . $e->getMessage() . ' ' . $e->getFile() . ':' . $e->getLine());
            }
        });

        return $this;
    }

    /**
     * @return int $scope
     */
    protected function getOverallErrScope()
    {
        $scope = 0;
        foreach (array_keys($this->handlers) as $eventType) {
            if (is_numeric($eventType)) {
                $scope |= $eventType;
            }
        }
        return $scope;
    }

    /**
     * @return $this
     */
    protected function setShutdown()
    {
        register_shutdown_function(function () {
            try {
                $this->getEventBuffer()->persist();
                $lastError = error_get_last();
                if (!empty($lastError)) {
                    $trace = debug_backtrace(false);
                    $lastError['trace'] = $trace;
                    $lastError['code'] = $lastError['type'];
                    unset($lastError['type']);
                    $lastError = $this->exceptionForbiddenConvert($lastError);
                    $event = new ErrorEvent($lastError);
                    $this->handleEvent($event);
                }
            } catch (Throwable $e) {
                self::log(get_class($e) . '; ' . $e->getMessage() . ' ' . $e->getFile() . ':' . $e->getLine());
            }
        });
        return $this;
    }

    /**
     * TODO:separate this into a utility class/trait
     *
     * @param array $errorInfo
     * @return array $errorInfoModified
     */
    protected function exceptionForbiddenConvert($errorInfo)
    {
        if (strpos($errorInfo['message'], 'must not throw an exception') !== false) {
            $msg = $errorInfo['message'];
            $matches = [];
            preg_match('/in (.*?) on line \d+/', $msg, $matches);
            if (is_array($matches) and sizeof($matches) > 0) {
                $m = reset($matches);
                $m = trim(str_replace(['on line ', 'in '], ['', ''], $m));
                $fl = explode(' ', $m);
                $errorInfo['file'] = $fl[0];
                $errorInfo['line'] = $fl[1];
            }
            return is_array($errorInfo) ? $errorInfo : [];
        } else {
            return $errorInfo;

        }
    }


}