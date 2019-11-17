<?php
namespace WatchTower\Outputs;

use WatchTower\ConfigValidation;
use WatchTower\Events\EventInterface;
use WatchTower\Exceptions\WatchTowerException;

/**
 * Class OutputTarget
 * @package WatchTower\Outputs
 */
abstract class OutputTarget implements OutputTargetInterface
{
    use ConfigValidation;
    /**
     * @var array $config
     */
    protected $config;

    /** @var array $outputVars */
    protected $outputVars;

    /** @var array $defaultConfig */
    protected $defaultConfig;

    static public function create($config = []) {
        return new static($config);
    }

    /**
     * OutputTarget constructor.
     * @param array $config
     * @throws WatchTowerException
     */
    public function __construct($config = [])
    {
        $this->config = $this->validateAndApplyConfig($this->getDefaultConfig(),$config);
    }

    public function getName() {
        return get_class($this);
    }

    /**
     * @param EventInterface $event
     * @param $content
     * @param array $globalVars
     * @return void|OutputTargetInterface
     * @throws WatchTowerException
     */
    public function execute(EventInterface $event,$content,$globalVars = [])
    {
       throw new WatchTowerException(sprintf('Execute method not implemented for <b>%s</b> output target',get_class($this)),10);
    }

    public function getOutputVars() {
        return is_array($this->outputVars) ? $this->outputVars : [];
    }
}