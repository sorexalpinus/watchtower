<?php
namespace WatchTower\Outputs;
use WatchTower\Events\EventInterface;
use WatchTower\Exceptions\WatchTowerException;
use WatchTower\WatchTower;

/**
 * Class OutputTarget
 * @package WatchTower\Outputs
 */
abstract class OutputTarget implements OutputTargetInterface
{
    /** @var array $config */
    protected $config;

    /** @var array $output */
    protected $output;

    /**
     * @param array $config
     * @return OutputTargetInterface|static
     * @throws WatchTowerException
     */
    static public function create($config = []) {
        return new static($config);
    }

    /**
     * File constructor.
     *
     * @param array $config
     * @throws WatchTowerException
     */
    public function __construct($config = [])
    {
        $this->config = $this->validateConfig($config,[]);
    }

    /**
     * @return string $name
     */
    public function getName() {
        return get_class($this);
    }

    /**
     * @param EventInterface $event
     * @param $content
     * @param array $outputStack
     * @return void|OutputTargetInterface
     * @throws WatchTowerException
     */
    public function execute(EventInterface $event,$content,$outputStack = [])
    {
       throw new WatchTowerException(sprintf('Execute method not implemented for <b>%s</b> output target',get_class($this)),10);
    }

    /**
     * @param string $item
     * @return string|array $output
     * @throws WatchTowerException
     */
    public function getOutput($item = '')
    {
        $out = !is_array($this->output) ? ['main' => $this->output] : $this->output;
        if(strlen($item) > 0) {
            if($item == 'all') {
                return $out;
            }
            elseif(array_key_exists($item,$out)) {
                return $out[$item];
            }
            else {
                throw new WatchTowerException(sprintf('Unknown output item %s',$item),29);
            }
        }
        else {
            return $out;
        }
    }

    /**
     * @param string $initialOutput
     * @return $this
     */
    public function init($initialOutput) {
        return $this;
    }

    /**
     * @param array $config
     * @param array $mandatory
     * @return array $config
     * @throws WatchTowerException
     */
    protected function validateConfig($config,$mandatory) {
        $missing = [];
        foreach($mandatory as $name) {
            $config[$name] = $config[$name] ?? WatchTower::getInstance()->getConfig($name);
            if(empty($config[$name])) {
                $missing[] = $name;
            }
        }
        if(sizeof($missing) > 0) {
            throw new WatchTowerException(sprintf('The config variables "%s" %s missing in %s',implode('","',$missing),count($missing) == 1 ? 'is':'are',get_class($this)),6);
        }
        return $config;
    }
}