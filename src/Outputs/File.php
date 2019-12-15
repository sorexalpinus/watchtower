<?php

namespace WatchTower\Outputs;

use WatchTower\Events\EventInterface;
use WatchTower\Exceptions\WatchTowerException;

/**
 * Class File
 * @package WatchTower\Outputs
 */
class File extends OutputTarget
{

    /**
     * @return string $name
     */
    public function getName() {
        return 'file';
    }

    /**
     * File constructor.
     *
     * @param array $config
     * @throws WatchTowerException
     */
    public function __construct($config = [])
    {
        $mandatory = ['watchtower.reader','file.dir','file.ttl'];
        $this->config = $this->validateConfig($config,$mandatory);
    }

    public function execute(EventInterface $event, $content, $outputStack = [])
    {
        $dir = $this->prepareDir($this->config['file.dir']);
        $this->removeOldFiles($dir, $this->config['file.ttl']);
        $fileName = $this->getFilename($event->getId());
        $result = file_put_contents($dir . $fileName, $content);
        if(file_exists($dir . $fileName) and $result) {
            $error = '';
            $success = true;
        }
        else {
            $success = false;
            $error = 'Could not write error file';
        }
        $this->output = [
            'success' => $success,
            'errorMsg' => $error,
            'accessLink' => $this->config['watchtower.reader'].'?type=file&path='.base64_encode($dir . $fileName)
        ];
        return $this;
    }

    /**
     * @param int $eventId
     * @return string $filename
     */
    public function getFilename($eventId)
    {
        return 'event_' . date('Ymd_His') . '_' . $eventId . '.html';
    }

    /**
     * @param string $dir
     * @param string $ttl
     * @throws WatchTowerException
     */
    protected function removeOldFiles($dir, $ttl)
    {
        if (strlen($ttl) > 0) {
            $ttlTs = strtotime(' - ' . $ttl);
            if (file_exists($dir)) {
                $contents = scandir($dir);
                if (is_array($contents)) {
                    foreach ($contents as $archiveFile) {
                        if (substr($archiveFile, -5, 5) == '.html') {
                            $fileDate = substr($archiveFile, 10, 15);
                            $fileTs = strtotime(str_replace('_', '', $fileDate));
                            if ($fileTs <= $ttlTs) {
                                $deleted = unlink($dir . $archiveFile);
                                if (!$deleted) {
                                    throw new WatchTowerException('Could not delete old exception file.', 1);
                                }
                            }
                        }
                    }
                }
            } else {
                throw new WatchTowerException('Exception file storage folder does not exist.', 2);
            }
        }
    }


    /**
     * @param string $dir
     * @return string $dir
     * @throws WatchTowerException
     */
    protected function prepareDir($dir)
    {
        $dir = str_replace(["//", "\\\\"], [DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR], $dir);
        if (!file_exists($dir) or !dir($dir)) {
            $result = mkdir($dir, 0755, true);
            if (!$result) {
                throw new WatchTowerException('Not able to create exception file folder', 0004);
            }
        }
        if(substr($dir,-1) !== DIRECTORY_SEPARATOR) {
            $dir .= DIRECTORY_SEPARATOR;
        }
        return $dir;
    }
}