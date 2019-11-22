<?php


namespace WatchTower\Tests\Core;


use ArgumentCountError;
use ArithmeticError;
use AssertionError;
use CompileError;
use DivisionByZeroError;
use Error;
use ParseError;
use Throwable;
use TypeError;
use WatchTower\Events\EventTrait;

class EventLauncher
{
    use EventTrait;

    const AVAILABLE_ERROR_TYPES = [E_WARNING,E_NOTICE,E_USER_ERROR,E_USER_WARNING,E_USER_NOTICE,E_DEPRECATED,E_USER_DEPRECATED];
    const AVAILABLE_EXCEPTION_TYPES = [Throwable::class, Error::class, ArithmeticError::class, DivisionByZeroError::class, AssertionError::class, CompileError::class, ParseError::class, TypeError::class, ArgumentCountError::class];


    public function throwException($class,$message,$code) {
        throw new $class($message,$code);
    }

    public function triggerError($type) {
        switch($type) {
            case E_WARNING: {
                trigger_error('Triggered parse error',E_WARNING);
                break;
            }
            case E_PARSE: {
                trigger_error('Triggered parse error',E_PARSE);
                break;
            }
            case E_NOTICE: {
                trigger_error('Triggered notice',E_NOTICE);
                break;
            }
            case E_USER_ERROR: {
                trigger_error('Triggered user error',E_USER_ERROR);
                break;
            }
            case E_USER_WARNING: {
                trigger_error('Triggered user warning',E_USER_WARNING);
                break;
            }
            case E_USER_NOTICE: {
                trigger_error('Triggered user notice',E_USER_NOTICE);
                break;
            }
            case E_DEPRECATED: {
                split('[/.-]', "2000-01-01");
                break;
            }
            case E_USER_DEPRECATED: {
                trigger_error('Triggered user deprecated',E_USER_DEPRECATED);
                break;
            }
            default: {
                return false;
            }

        }
        return true;
    }
}