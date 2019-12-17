<?php

namespace WatchTower\Exceptions;

use ErrorException;
use mysqli;
use ReflectionException;
use WatchTower\WatchTower;

/**
 * Class MysqlException
 * @package WatchTower\Exceptions
 */
class MysqlException extends ErrorException implements WatchTowerAwareException
{
    /** @var string $title */
    protected $title = 'MySQL error';

    /** @var string $message */
    protected $message;

    /** @var int $code */
    protected $code;

    /** @var string $filename */
    protected $filename;

    /** @var int $lineno */
    protected $lineno;

    /** @var \Throwable|null $previous ; */
    protected $previous;

    /** @var array $mysqlErrorInfo */
    protected $mysqlErrorInfo;

    /**
     * MysqlException constructor.
     *
     * @param mysqli $mysqli
     * @param string $query
     * @param string $message
     * @param int $code
     * @param int $severity
     * @param string $filename
     * @param int $lineno
     * @param null $previous
     */
    public function __construct(mysqli $mysqli, $query, $message = "", $code = 0, $severity = 1, $filename = __FILE__, $lineno = __LINE__, $previous = null)
    {
        $this->message = $message . ': ['.$mysqli->errno.'] '.$mysqli->error;
        $this->code = $this->createCode($mysqli,$code);
        $this->severity = $severity;
        $this->filename = $filename;
        $this->lineno = $lineno;
        $this->previous = $previous;
        $query = $this->formatQuery($query);
        $this->mysqlErrorInfo = [
            'code' => $mysqli->errno,
            'message' => $mysqli->error,
            'query' => $query
        ];
    }

    /**
     * @return MysqlException|false
     * @throws WatchTowerException
     * @throws ReflectionException
     */
    public function handle()
    {
        $wt = WatchTower::getInstance();
        if ($wt) {
            $wt->handleException($this);
            return $this;
        } else {
            return false;
        }
    }

    /**
     * @return array $extraInfo
     */
    public function getExtraInfo()
    {
        return $this->mysqlErrorInfo;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $query
     * @return String $formattedQuery
     */
    protected function formatQuery($query)
    {
        \SqlFormatter::$pre_attributes = 'style="color: black; background-color: transparent;';
        return \SqlFormatter::format($query);
    }

    /**
     * @param mysqli $mysqli
     * @param int $code
     * @return int $code
     */
    protected function createCode(mysqli $mysqli, $code) {
        if(empty($code)) {
            //$code =  (int)'1'.sprintf("%05d", $mysqli->errno);
            $code =  $mysqli->errno + 100000;
        }
        return $code;
    }
}