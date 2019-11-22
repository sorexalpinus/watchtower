<?php
namespace WatchTower\Events;

use Throwable;

/**
 * Class ErrorEvent
 * @package WatchTower\Events
 */
class ErrorEvent extends Event
{
    use EventTrait;

    /** @var array $errorinfo : code, message, file, line */
    protected $errorInfo;


    /**
     * ErrorEvent constructor.
     *
     * @param array $errorInfo
     */
    public function __construct(array $errorInfo)
    {
        $this->id = uniqid('', true);
        $this->errorInfo = $errorInfo;
    }

    /**
     * @return string $name
     */
    public function getName()
    {
        return self::getFriendlyErrorType($this->getCode());
    }

    /**
     * @return string $type
     */
    public function getType()
    {
        return 'error';
    }

    /**
     * @return Throwable $throwable
     */
    public function getException()
    {
        return new \ErrorException($this->getMessage(), $this->getCode(),1,$this->getFile(),$this->getLine());
    }

    /**
     * @param string|int $category in case of error event, this means error level
     * @return bool $isMatch
     */
    public function isCategoryMatch($category)
    {
        return (bool)is_integer($category) and $category & $this->getCode();
    }

    /**
     * @return array $errorinfo
     */
    public function getErrorInfo()
    {
        return $this->errorInfo;
    }

    public function getMessage()
    {
        return $this->errorInfo['message'];

    }

    public function getCode()
    {
        return $this->errorInfo['code'];

    }

    public function getFile()
    {
        return $this->errorInfo['file'];

    }

    public function getLine()
    {
        return $this->errorInfo['line'];
    }

    public function getTrace()
    {
        return $this->errorInfo['trace'];
    }

    public function getTraceAsString()
    {
        $trace = $this->errorInfo['trace'];
        $out = '';
        if (is_array($trace) and sizeof($trace) > 0) {
            $trace[0]['args'] = null;
            foreach ($trace as $t) {
                $out .= $t['file'] . ':' . $t['line'] . PHP_EOL;
                $out .= $t['class'] . $t['type'] . $t['function'] . '()' . PHP_EOL;
                if (is_array($t['args'])) {
                    foreach ($t['args'] as $key => $arg) {
                        if (is_string($arg) or is_numeric($arg)) {
                            $out .= '# ' . $arg . PHP_EOL;
                        } elseif (is_array($arg)) {
                            $out .= '# array(' . count($arg) . ')' . PHP_EOL;
                        } elseif (is_object($arg)) {
                            $out .= '# object(' . get_class($arg) . ')' . PHP_EOL;
                        }
                    }
                }
                $out .= '---' . PHP_EOL;
            }
            return PHP_EOL . $out;
        }
        return '';
    }

}