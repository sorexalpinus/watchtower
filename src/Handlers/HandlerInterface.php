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
     * @param array $canSend
     * @return $this
     */
    public function sendToOutputTargets(EventInterface $event,$canSend = []);

    /**
     * @return OutputTargetInterface[] $outputTargets
     */
    public function getOutputTargets();

    /**
     * @param OutputTargetInterface $output
     * @return $this
     */
    public function sendTo(OutputTargetInterface $output);

    /**
     * @param string $item
     * @return string $output
     */
    public function getOutput($item = '');


    /**
     * @return string $name
     */
    public function getName();

}