<?php
namespace WatchTower\Events;

use ReflectionClass;
use ReflectionException;
use Throwable;

/**
 * Class ExceptionEvent
 * @package WatchTower\Events
 */
class ExceptionEvent extends Event
{

    /** @var Throwable $exception */
    protected $exception;

    /**
     * ExceptionEvent constructor.
     *
     * @param Throwable $exception
     * @throws ReflectionException
     */
    public function __construct(Throwable $exception)
    {
        $this->id = uniqid('', true);
//        $this->flattenExceptionBacktrace($exception);
        $this->filterTrace($exception);
        $this->exception = $exception;
    }

    /**
     * @return string $type
     */
    public function getType()
    {
        return 'exception';
    }

    public function getName() {
        return get_class($this->exception);
    }

    /**
     * @return Throwable $throwable
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * @param string|int $category in case of exception event, this means exception class or interface
     * @return bool $isMatch
     */
    public function isCategoryMatch($category) {

        return (bool) is_string($category) and $this->getException() instanceof $category;
    }

    /**
     * @return string $message
     */
    public function getMessage()
    {
        return $this->exception->getMessage();
    }

    /**
     * @return string $code
     */
    public function getCode()
    {
        return $this->exception->getCode();
    }

    /**
     * @return string $file
     */
    public function getFile()
    {
        return $this->exception->getFile();

    }

    /**
     * @return string $line
     */
    public function getLine()
    {
        return $this->exception->getLine();
    }

    /**
     * @return array $trace
     */
    public function getTrace()
    {
        return $this->exception->getTrace();
    }

    /**
     * @return string $trace
     */
    public function getTraceAsString()
    {
        return $this->exception->getTraceAsString();
    }

    /**
     * @return string $hash
     */
    public function getLocationHash() {
        return $this->getCommonLocationHash('exception',$this->getException());
    }

    /**
     * @param Throwable $exception
     * @return $this
     * @throws ReflectionException
     */
    protected function filterTrace(Throwable $exception) {
        if(is_array($exception->getTrace())) {
            $traceProperty = (new ReflectionClass('Exception'))->getProperty('trace');
            $traceProperty->setAccessible(true);
            $trace = array_map(function($val) {
                $val['args'] = [];
                return $val;
            },$exception->getTrace());
            $traceProperty->setValue($exception, $trace);
            $traceProperty->setAccessible(false);
        }
        return $this;
    }
}