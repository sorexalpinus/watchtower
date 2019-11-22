<?php

namespace WatchTower\Outputs;

use WatchTower\ConfigValidation;
use WatchTower\Events\EventInterface;
use WatchTower\Exceptions\WatchTowerException;

/**
 * Class File
 * @package WatchTower\Outputs
 */
class File extends OutputTarget
{
    use ConfigValidation {
        validateAndApplyConfig as parentValidateAndApplyConfig;
    }

    /**
     * @param string|null $item
     * @return array $defaultConfig
     */
    public function getDefaultConfig($item = null)
    {
        $webRoot = '';
        if (PHP_SAPI !== 'cli') {
            $webRoot = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'];
        }

        if (!isset($this->defaultConfig)) {
            $accessLinkFunc = function (EventInterface $event, OutputTargetInterface $outputTarget) use ($webRoot) {
                $filename = $outputTarget->getFilename($event->getId());
                return $webRoot . 'tests/files/readReport.php?e=' . hash('sha256', 'some-salt' . $filename) . '&f=' . base64_encode($filename);
            };
            $this->defaultConfig = [
                'dir' => ['mandatory' => true, 'value' => $_SERVER['DOCUMENT_ROOT'] . '/files/exceptions/'],
                'ttl' => ['mandatory' => true, 'value' => '1 month'],
                'accessLink' => ['mandatory' => false, 'value' => $accessLinkFunc]
            ];
        }
        return $this->defaultConfig;
    }

    public function execute(EventInterface $event, $content, $globalVars = [])
    {
        $dir = $this->prepareDir($this->config['dir']);
        $fileName = $this->getFilename($event->getId());
        $result = file_put_contents($dir . $fileName, $content);
        $this->removeOldFiles($dir, $this->config['ttl']);
        $accessLink = is_callable($this->config['accessLink']) ? $this->config['accessLink']($event, $this) : $this->config['accessLink'];
        $this->outputVars = [
            'success' => (bool)$result,
            'fileAccessLink' => $accessLink
        ];
        return $this;
    }

    /**
     * @param int $eventId
     * @return string $filename
     */
    public function getFilename($eventId)
    {
        return 'exception_' . date('Ymd_His') . '_' . $eventId . '.html';
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
        return $dir;
    }

    /**
     * @param $defaultConfig
     * @param array $config
     * @return array mixed
     * @throws WatchTowerException
     */
    protected function validateAndApplyConfig($defaultConfig, $config)
    {
        $result = $this->parentValidateAndApplyConfig($defaultConfig, $config);
        if (substr($result['dir'], -1) != DS) {
            $result['dir'] .= DS;
        }
        return $result;
    }
}