<?php

namespace WatchTower\Handlers;

use WatchTower\Events\EventInterface;
use WatchTower\Outputs\OutputTargetInterface;

/**
 * Interface HandlerInterface
 * @package WatchTower\Handlers
 */
interface HandlerInterface
{
    /**
     * @param array $config
     * @return HandlerInterface $config
     */
    static public function create($config = []);
    /**
     * @param EventInterface $event
     *
     * @return $this
     */
    public function handle(EventInterface $event);

    /**
     * @param EventInterface $event
     * @return $this
     */
    public function sendToOutputTargets(EventInterface $event);


    /**
     * @param OutputTargetInterface $output
     * @return $this
     */
    public function sendTo(OutputTargetInterface $output);

    /**
     * @return string $output
     */
    public function getOutput();

    /**
     * @return array $ouputVars
     */
    public function getOutputVars();

    /**
     * @param string|null $item
     * @return array|string $config
     */
    public function getConfig($item = null);

    /**
     * @param null $item
     * @return mixed
     */
    public function getDefaultConfig($item = null);


    /**
     * @return string $name
     */
    public function getName();

}