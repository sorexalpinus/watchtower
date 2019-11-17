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

    public function getMessage();

    public function getCode();

    public function getFile();

    public function getLine();

    public function getTrace();

    public function getTraceAsString();



}