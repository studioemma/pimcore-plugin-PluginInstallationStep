<?php
/**
 * @copyright Copyright (c) 2016 Studio Emma. (http://www.studioemma.com)
 */

namespace PluginInstallationStep\Installation;

use Pimcore\Model\Object;
use Pimcore\Model\WebsiteSetting;
use Pimcore\Cache;

class CreateObjectFolderAndWebsiteSetting implements InstallationStepInterface
{
    const TYPE = 'Object';
    const CACHEKEY = 'COFAW';

    protected $configKey;
    protected $folderName;

    function __construct($configKey, $folderName)
    {
        $this->configKey = $configKey;
        $this->folderName = $folderName;
    }

    public function install()
    {
        // if setting exists with a folder, use that setting if that folder also exists
        $setting = WebsiteSetting::getByName($this->configKey);
        if (!is_null($setting)) {
            $folderId = $setting->getData();
        }

        $folderClass = self::TYPE . '\Folder';
        if (isset($folderId)) {
            $folder = $folderClass::getById($folderId);
        } else {
            $folder = $folderClass::getByPath('/' . $this->folderName);
        }

        // create the folder if it does not exists
        if (is_null($folder)) {
            $folder = new $folderClass();
            $folder->setParentId(1);
            $folder->setKey($this->folderName);
            $folder->save();
        }

        // set setting to the folder
        if (is_null($setting)) {
            // create setting if it does not yet exists
            $setting = new WebsiteSetting();
            $setting->setName($this->configKey);
        }
        $setting->setValues(array(
            'type' => strtolower(self::TYPE),
            'data' => $folder->getId()
        ));
        $setting->save();

        return $this->isInstalled();
    }

    public function uninstall()
    {
        // do not delete the folder
        // but do delete the setting
        $setting = WebsiteSetting::getByName($this->configKey);
        $setting->delete();

        return !$this->isInstalled();
    }

    public function isInstalled()
    {
        $setting = $this->getWebsiteSetting();

        if (!is_null($setting)) {
            $folderId = $setting->getData();
        }

        $folderClass = self::TYPE . '\Folder';
        if (isset($folderId)) {
            // no need to get the folder because we know it exists
            $folder = true;
        } else {
            $folder = $folderClass::getByPath('/' . $this->folderName);
        }

        return !is_null($setting) && !is_null($folder);
    }

    protected function getWebsiteSetting()
    {
        $cacheKey = self::CACHEKEY . '_' . $this->configKey;

        if (false !== Cache::test($cacheKey)) {
            return Cache::load($cacheKey);
        }

        $setting = WebsiteSetting::getByName($this->configKey);

        $hasCacheWriteLock = Cache::hasWriteLock();
        if ($hasCacheWriteLock) {
            Cache::removeWriteLock();
        }
        // force writing of our dynamic dropdown cache
        Cache::save($setting, $cacheKey, [], 3600, 0, true);
        if ($hasCacheWriteLock) {
            Cache::setWriteLock();
        }

        return $setting;
    }

    public function needsReloadAfterInstall()
    {
        return false;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return 'Create '.strtolower(self::TYPE).' folder and website setting';
    }
}
