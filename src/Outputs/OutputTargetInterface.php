<?php

namespace WatchTower\Outputs;

use WatchTower\Events\EventInterface;

/**
 * Interface OutputTargetInterface
 * @package WatchTower\Outputs
 */
interface OutputTargetInterface
{
    /**
     * @param array $config
     * @return static
     */
    static public function create($config = []);

    /**
     * @return string $name
     */
    public function getName();

    /**
     * @param EventInterface $event
     * @param string $content
     * @param array $outputStack
     * @return mixed
     */
    public function execute(EventInterface $event,$content,$outputStack);

    /**
     * @param string $item
     * @return array $output
     */
    public function getOutput($item = '');

    /**
     * @param string $initialOutput
     * @return $this
     */
    public function init($initialOutput);

    /**
     * @param string $item
     * @return array|string|false
     */
    public function getConfig($item = '');

}