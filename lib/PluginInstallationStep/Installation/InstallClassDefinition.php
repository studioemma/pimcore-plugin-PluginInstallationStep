<?php
/**
 * @copyright Copyright (c) 2016 Studio Emma. (http://www.studioemma.com)
 */

namespace PluginInstallationStep\Installation;

use Exception;
use Pimcore\Model\Object\ClassDefinition;

class InstallClassDefinition implements InstallationStepInterface
{
    /** @var string */
    protected $className;
    /** @var string */
    protected $pluginName;

    public function __construct($className, $pluginName = null)
    {
        $this->className = $this->pluginName = $className;
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
            $class = ClassDefinition::create(
                ['name' => $this->className]
            );
            $class->save();
            $file = $this->getDataFile();
            ClassDefinition\Service::importClassDefinitionFromJson(
                $class,
                $file,
                true
            );
        }

        return $this->isInstalled();
    }

    /**
     * @return bool
     */
    public function uninstall()
    {
        /** remove ClassDefinition */
        $class = ClassDefinition::getByName($this->className);
        $class->delete();

        return !$this->isInstalled();
    }

    /**
     * @return bool
     */
    public function isInstalled()
    {
        $class = ClassDefinition::getByName($this->className);
        return !is_null($class);
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
        return 'Install class definition';
    }

    protected function getDataFile()
    {
        $file = PIMCORE_PLUGINS_PATH
            . '/' . $this->pluginName
            . '/data/'
            . 'class_' . $this->className . '_export.json';
        if (! file_exists($file)) {
            throw new Exception('datafile not found ' . $file);
        }

        return file_get_contents($file);
    }
}
