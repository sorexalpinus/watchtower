<?php
namespace WatchTower\Events;

use ReflectionClass;
use ReflectionException;
use Throwable;
use WatchTower\Exceptions\WatchTowerException;

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
     * @throws WatchTowerException
     */
    public function __construct(Throwable $exception)
    {
        $this->id = uniqid('', true);
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
     * @param Throwable $throwable
     * @return $this
     * @throws ReflectionException
     * @throws WatchTowerException
     */
    protected function filterTrace(Throwable $throwable) {
        if(is_array($throwable->getTrace())) {
            $reflection = (new ReflectionClass(get_class($throwable)));
            if($reflection->hasProperty('trace')) {
                $traceProperty = $reflection->getProperty('trace');
                $traceProperty->setAccessible(true);
                $trace = array_map(function($val) {
                    $val['args'] = [];
                    return $val;
                },$throwable->getTrace());
                $traceProperty->setValue($throwable, $trace);
                $traceProperty->setAccessible(false);
            }
            else {
                throw new WatchTowerException(sprintf('Class %s has not property trace',get_class($throwable)),29);
            }
        }
        return $this;
    }
}