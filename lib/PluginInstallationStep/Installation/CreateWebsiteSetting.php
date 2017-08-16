<?php
/**
 * @copyright Copyright (c) 2017 Studio Emma. (http://www.studioemma.com)
 */


namespace PluginInstallationStep\Installation;


use Pimcore\Cache;
use Pimcore\Model\WebsiteSetting;

class CreateWebsiteSetting implements InstallationStepInterface
{

    const TEXT = 'text';
    const CACHEKEY = 'CW';

    protected $configKey;
    protected $data;

    function __construct($configKey, $data = null)
    {
        $this->configKey = $configKey;
        $this->data = $data;
    }

    public function install()
    {
        $setting = WebsiteSetting::getByName($this->configKey);

        // set setting
        if (is_null($setting)) {
            // create setting if it does not yet exists
            $setting = new WebsiteSetting();
            $setting->setName($this->configKey);
        }
        $setting->setValues(array(
            'type' => strtolower(self::TEXT),
            'data' => $this->data
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

        return !is_null($setting);
    }

    protected function getWebsiteSetting()
    {
        $cacheKey = self::CACHEKEY . '_' . $this->configKey;

        if (false !== Cache::test($cacheKey)) {
            return Cache::load($cacheKey);
        }

        $setting = WebsiteSetting::getByName($this->configKey);

        if (! empty($setting)) {
            $hasCacheWriteLock = Cache::hasWriteLock();
            if ($hasCacheWriteLock) {
                Cache::removeWriteLock();
            }
            // force writing of our dynamic dropdown cache
            Cache::save($setting, $cacheKey, [], 3600, 0, true);
            if ($hasCacheWriteLock) {
                Cache::setWriteLock();
            }
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
        return 'Create website setting';
    }
}