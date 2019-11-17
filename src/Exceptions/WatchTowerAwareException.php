<?php

namespace WatchTower\Exceptions;

/**
 * Interface ExtendedExceptionInterface
 * @package WatchTower\Exceptions
 */
interface WatchTowerAwareException
{
    /**
     * @return string $title
     */
    public function getTitle();

    /**
     * @return array $extraInfo
     */
    public function getExtraInfo();

    /**
     * @return $this
     */
    public function handle();
}