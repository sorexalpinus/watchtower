<?php
namespace WatchTower\Events;

/**
 * that covers all non-standard events - both errors and exceptions
 * @package WatchTower
 */
interface EventInterface
{
    /**
     * @return mixed
     */
    public function getType();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return \Throwable $throwable
     */
    public function getException();


    /**
     * @param string|int $category
     * @return bool $isMatch
     */
    public function isCategoryMatch($category);

    /**
     * @param callable $filter
     * @return bool
     */
    public function passedThroughFilter($filter);

    /**
     * @return string
     */
    public function getMessage();

    /**
     * @return int
     */
    public function getCode();
    /**
     * @return string
     */
    public function getFile();

    /**
     * @return int
     */
    public function getLine();

    public function getTrace();
    /**
     * @return string
     */
    public function getTraceAsString();



}