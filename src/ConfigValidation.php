<?php

namespace WatchTower;

use WatchTower\Exceptions\WatchTowerException;

/**
 * Trait ConfigValidation
 * @package WatchTower
 */
trait ConfigValidation
{
    /**
     * @param $defaultConfig
     * @param array $config
     * @return array mixed
     * @throws WatchTowerException
     */
    protected function validateAndApplyConfig($defaultConfig,$config) {
        $resultingConfig = [];
        if(empty($defaultConfig)) {
            $defaultConfig = [];
        }
        if(is_array($defaultConfig)) {
            foreach($defaultConfig as $item => $setup) {
                $resultingConfig[$item] = $setup['value'];
            }
            foreach ($config as $item => $value) {
                if (array_key_exists($item, $defaultConfig)) {
                    $resultingConfig[$item] = $value;
                } else {
                    throw new WatchTowerException(sprintf('Config item "%s" is not suported in %s handler', $item, get_class($this)), 0005);
                }
            }
            $notSet = [];
            foreach($defaultConfig as $item => $setup) {
                if($setup['mandatory'] and empty($resultingConfig[$item])) {
                    $notSet[] = $item;
                }
            }
            if(!empty($notSet)) {
                throw new WatchTowerException(sprintf('The config variables "%s" %s missing in %s',implode('","',$notSet),count($notSet) == 1 ? 'is':'are',get_class($this)),0006);
            }
        }
        else {
            $resultingConfig = [];
        }
        return $resultingConfig;
    }
}