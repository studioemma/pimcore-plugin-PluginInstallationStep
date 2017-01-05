<?php
/**
 * @copyright Copyright (c) 2016 Studio Emma. (http://www.studioemma.com)
 */

namespace PluginInstallationStep\Installation;

use Pimcore\Model\Object;
use Pimcore\Model\WebsiteSetting;

class CreateObjectFolderAndWebsiteSetting implements InstallationStepInterface
{
    const TYPE = 'Object';
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

        return !is_null($setting) && !is_null($folder);
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
