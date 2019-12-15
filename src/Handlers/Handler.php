<?php

namespace WatchTower\Handlers;

use WatchTower\Events\EventInterface;
use WatchTower\Outputs\OutputTargetInterface;

/**
 * Class Handler
 * @package WatchTower\Handlers
 */
abstract class Handler implements HandlerInterface
{

    /** @var string $name */
    protected $name;

    /** @var array $output */
    protected $output;

    /** @var array $outputVars */
    protected $outputVars;

    /** @var OutputTargetInterface[] */
    protected $outputTargets;

    /** @var array $outputStarted */
    static protected $outputStarted;

    /**
     * @param array $config
     * @return HandlerInterface|static
     */
    static public function create($config = [])
    {
        return new static($config);
    }

    /**
     * Handler constructor.
     *
     * @param array $config
     */
    public function __construct($config = [])
    {

    }

    /**
     * @return array
     */
    public function __sleep()
    {
        //TODO: temporary solution
        $props = get_object_vars($this);
        if(is_array($props) and array_key_exists('outputTargets',$props)) {
            unset($props['outputTargets']);
        }
        return array_keys($props);
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

    /**
     * @return OutputTargetInterface[]
     */
    public function getOutputTargets()
    {
        return is_array($this->outputTargets) ? $this->outputTargets : [];
    }

    /**
     * @param EventInterface $event
     * @param array $canSend
     * @return $this|HandlerInterface
     */
    public function sendToOutputTargets(EventInterface $event,$canSend = [])
    {
        if(!isset(self::$outputStarted)) {
            self::$outputStarted = [];
        }
        if (is_array($this->outputTargets)) {
            $outputStack = [
                'handler'=>$this->output,
                'targets' => []
            ];
            /** @var OutputTargetInterface $outputTarget */
            foreach ($this->outputTargets as $key => $outputTarget) {
                if(isset($canSend[$key]) and $canSend[$key]) {
                    if(method_exists($this,'getOutputStart') and !$this->outputStarted($outputTarget)) {
                        $outputTarget->init($this->getOutputStart());
                        self::$outputStarted[get_class($outputTarget)] = true;
                    }
                    $this->sendToTarget($outputTarget,$event,$outputStack);
                    $outputStack['targets'][$outputTarget->getName()] = $outputTarget->getOutput('all');
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
     * @param EventInterface $event
     * @param array $outputStack
     */
    protected function sendToTarget($outputTarget,$event,$outputStack) {
        $outputTarget->execute($event, $this->getOutput(),$outputStack);
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
     * @param string $item
     * @return string $output
     */
    public function getOutput($item = '')
    {
        $o = !is_array($this->output) ? ['main' => $this->output] : $this->output;
        return (strlen($item) > 0 and array_key_exists($item,$o)) ? $o[$item] : $o['main'];
    }

    /**
     * @return string
     */
    public function getName()
    {
        return !empty($this->name) ? $this->name : get_class($this);
    }

}