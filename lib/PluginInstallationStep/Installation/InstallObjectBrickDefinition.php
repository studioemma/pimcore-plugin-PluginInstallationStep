<?php
/**
 * @copyright Copyright (c) 2016 Studio Emma. (http://www.studioemma.com)
 */

namespace PluginInstallationStep\Installation;

use Exception;
use Pimcore\Model\Object\ClassDefinition;
use Pimcore\Model\Object\Objectbrick\Definition;

class InstallObjectBrickDefinition implements InstallationStepInterface
{
    /** @var string */
    protected $objectBrickName;
    /** @var string */
    protected $pluginName;

    public function __construct($brickName, $pluginName = null)
    {
        $this->objectBrickName = $this->pluginName = $brickName;
        if (null !== $pluginName) {
            $this->pluginName = $pluginName;
        }
    }

    /**
     * @return bool
     */
    public function install()
    {
        /** create ClassDefinition */
        if (!$this->isInstalled()) {
            /** create Fieldcollection */
            $objectBrick = new Definition();
            $objectBrick->setKey($this->objectBrickName);
            $objectBrick->save();
            $file = $this->getDataFile();
            ClassDefinition\Service::importObjectBrickFromJson(
                $objectBrick,
                $file,
                true
            );
        }

        return $this->isInstalled();
    }

    /**
     * @return bool
     */
    public function isInstalled()
    {
        try {
            $objectBrick = Definition::getByKey($this->objectBrickName);
            return !is_null($objectBrick);
        }catch(Exception $e) {
            return false;
        }
    }

    protected function getDataFile()
    {
        $file = PIMCORE_PLUGINS_PATH
            . '/' . $this->pluginName
            . '/data/'
            . 'objectbrick_' . $this->objectBrickName . '_export.json';
        if (!file_exists($file)) {
            throw new Exception('datafile not found ' . $file);
        }

        return file_get_contents($file);
    }

    /**
     * @return bool
     */
    public function uninstall()
    {
        /** remove ClassDefinition */
        $objectBrick = Definition::getByKey($this->objectBrickName);
        $objectBrick->delete();

        return !$this->isInstalled();
    }

    /**
     * @return bool
     */
    public function needsReloadAfterInstall()
    {
        return true;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return 'Install objectBrick definition';
    }
}
