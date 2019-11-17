<?php

namespace WatchTower\Outputs;

use WatchTower\Events\EventInterface;

class Browser extends OutputTarget
{
    public function execute(EventInterface $event,$content,$globalVars = []) {
        echo $content;
        return $this;
    }

    /**
     * @param string|null $item
     * @return mixed
     */
    public function getDefaultConfig($item = null)
    {
        return [];
    }

}