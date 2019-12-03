<?php

namespace WatchTower\Handlers;

use WatchTower\ConfigValidation;
use WatchTower\Events\EventInterface;
use WatchTower\Exceptions\WatchTowerException;
use WatchTower\Outputs\OutputTargetInterface;

/**
 * Class Handler
 * @package WatchTower\Handlers
 */
abstract class Handler implements HandlerInterface
{
    use ConfigValidation;

    /** @var string $name */
    protected $name;

    /** @var array $config */
    protected $config;

    /** @var array $output */
    protected $output;

    /** @var array $outputVars */
    protected $outputVars;

    /**
     * @var OutputTargetInterface[]
     */
    protected $outputTargets;

    /** @var array $defaultConfig */
    protected $defaultConfig = [];

    /** @var array $outputStarted */
    static protected $outputStarted;

    /**
     * @param array $config
     * @return HandlerInterface|static
     * @throws WatchTowerException
     */
    static public function create($config = [])
    {
        return new static($config);
    }

    /**
     * SaveFile constructor.
     *
     * @param array $config
     * @throws WatchTowerException
     */
    public function __construct(array $config = [])
    {
        $this->config = $this->validateAndApplyConfig($this->getDefaultConfig(), $config);
    }

    /**
     * @param OutputTargetInterface $output
     * @return $this
     */
    public function sendTo(OutputTargetInterface $output)
    {
        $this->outputTargets[] = $output;
        return $this;
    }

    public function getOutputTargets()
    {
        return is_array($this->outputTargets) ? $this->outputTargets : [];
    }

    /**
     * @param EventInterface $event
     * @return $this|HandlerInterface
     */
    public function sendToOutputTargets(EventInterface $event)
    {
        if(!isset(self::$outputStarted)) {
            self::$outputStarted = [];
        }
        if (is_array($this->outputTargets)) {
            $globalVars = $this->getOutputVars();
            /** @var OutputTargetInterface $outputTarget */
            foreach ($this->outputTargets as $outputTarget) {

                if(method_exists($this,'getOutputStart') and !$this->outputStarted($outputTarget)) {
                    $outputTarget->init($this->getOutputStart());
                    self::$outputStarted[get_class($outputTarget)] = true;
                }

                $outputTarget->execute($event, $this->getOutput(), $globalVars);
                if(is_array($outputTarget->getOutputVars())) {
                    $globalVars = array_merge($globalVars, $outputTarget->getOutputVars());
                }
            }
            if(method_exists($this,'afterSendToOutput')) {
                $this->afterSendToOutput();
            }
        }
        return $this;
    }

    /**
     * @param OutputTargetInterface $outputTarget
     * @return bool $started
     */
    protected function outputStarted(OutputTargetInterface $outputTarget) {
        $otClass = get_class($outputTarget);
        if(is_array(self::$outputStarted) and array_key_exists($otClass,self::$outputStarted) and self::$outputStarted[$otClass]) {
            return true;
        }
        else{
            return false;
        }
    }

    /**
     * @param array|string|null $item
     * @return array|string|null
     */
    public function getDefaultConfig($item = null)
    {
        if (!empty($item)) {
            if(isset($this->defaultConfig[$item])) {
                return $this->defaultConfig[$item];
            }
            else {
                return null;
            }
        } else {
            return $this->defaultConfig;
        }
    }

    /**
     * @return array|mixed
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @return array $outputVars
     */
    public function getOutputVars()
    {
        return !empty($this->outputVars) ? $this->outputVars : [];
    }

    /**
     * @param string|null $item
     * @return mixed
     */
    public function getConfig($item = null)
    {
        if (empty($item)) {
            return $this->config;
        } else {
            if (array_key_exists($item, $this->config)) {
                return $this->config[$item];
            } else {
                return false;
            }
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return !empty($this->name) ? $this->name : get_class($this);
    }

}