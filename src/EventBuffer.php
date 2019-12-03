<?php
namespace WatchTower;
use Countable;
use WatchTower\Events\EventInterface;
use WatchTower\Events\EventTrait;
use WatchTower\Exceptions\WatchTowerException;

/**
 * Class EventBuffer
 */
class EventBuffer implements Countable
{
    use EventTrait;

    /** @var string $timelogFilePath */
    protected $timelogFilePath = WATCHTOWER_FROOT.'/log/';

    /** @var string $timelogFileName */
    protected $timelogFileName = 'timelog';


    /** @var resource $historyFile */
    protected $timelogFile;

    /** @var array $buffer */
    protected $buffer;

    /** @var array $history */
    protected $timelog;

    /** @var int $maxHistorySize */
    protected $maxTimelogSize = 20;

    /** @var int $maxBufferSize */
    protected $maxBufferSize = 50;

    /** @var int $reportSpan */
    private $reportSpan;

    /**
     * @return EventBuffer $eventBuffer
     */
    static public function create()
    {
        return new self();
    }

    /**
     * EventBuffer constructor.
     */
    public function __construct()
    {
        $this->buffer = [];
        $this->timelog = $this->getLoggedData();
    }

    /**
     * @param string $type
     * @param array|string $info
     * @return bool $canPush
     */
    public function canPush($type,$info)
    {
        if($this->count() < $this->maxBufferSize) {
            $hash = $this->getCommonLocationHash($type,$info);
            if(!array_key_exists($hash,$this->buffer)) {
                $ts = $this->timelog[$hash] ?? 0;
                if((time() - $this->reportSpan) > $ts) {
                    return true;
                }
                else {
                    return false;
                }
            }
        }
        return  false;
    }

    /**
     * @param string $time
     * @return $this
     */
    public function setReportSpan($time) {
        $this->reportSpan = time() - strtotime('-'.$time);
        return $this;
    }

    /**
     * @param EventInterface $event
     * @return EventBuffer $eventBuffer
     */
    public function push(EventInterface $event) {
        $hash = $event->getLocationHash();
        $this->buffer[$hash] = $event;
        $this->timelog[$hash] = date('U');
        return $this;
    }

    /**
     * @return $this
     * @throws WatchTowerException
     */
    public function persist() {
        $file = $this->getLoggedData();
        if(is_array($this->timelog)) {
            foreach($this->timelog as $hash => $ts) {
                $file[$hash] = $hash." ".$ts;
            }
            $fSize = count($file);
            if($fSize > $this->maxTimelogSize) {
                $cut = $fSize - $this->maxTimelogSize;
                $file = array_slice($file,$cut-1,$fSize-1);
            }
            file_put_contents($this->getFullTimeLogPath(),$file);
        }
        return $this;
    }
    /**
     * @return int $count
     */
    public function count()
    {
        return count($this->buffer);
    }

    /**
     * @return array $loggedData
     * @throws WatchTowerException
     */
    protected function getLoggedData() {
        $hData = [];
        if(!file_exists($this->timelogFilePath)) {
            if (!mkdir($this->timelogFilePath, 0755, true)) {
                throw new WatchTowerException('Could not create timeLog folder: ' . $this->timelogFilePath, 21);
            }
        }
        $this->timelogFile = fopen($this->getFullTimeLogPath(), 'a+');
        if ($this->timelogFile) {
            $data = file($this->getFullTimeLogPath());
            if(is_array($data)) {
                foreach($data as $d) {
                    $d = explode(' ',$d);
                    $hData[$d[0]] = $d[1];
                }
            }
            fclose($this->timelogFile);
        }
        else {
            throw new WatchTowerException('Could not open/create timeLog file ' . $this->getFullTimeLogPath(), 20);
        }
        return $hData;
    }

    protected function getFullTimeLogPath() {
        return $this->timelogFilePath . $this->timelogFileName;
    }

}