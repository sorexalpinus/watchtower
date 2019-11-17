<?php

namespace WatchTower\Outputs;

use WatchTower\Events\EventInterface;

interface OutputTargetInterface
{
    /**
     * @param array $config
     * @return static
     */
    static public function create($config = []);

    /**
     * @param EventInterface $event
     * @param $content
     * @param array $globalVars
     * @return mixed
     */
    public function execute(EventInterface $event,$content,$globalVars = []);

    /**
     * @param string|null $item
     * @return array $defaultConfig
     */
    public function getDefaultConfig($item = null);

    /**
     * @return array $outputVars
     */
    public function getOutputVars();

}