<?php
/**
 * @copyright Copyright (c) 2016 Studio Emma. (http://www.studioemma.com)
 */

namespace PluginInstallationStep\Plugin;

use PluginInstallationStep\Installation\CreateObjectFolderAndWebsiteSetting;
use PluginInstallationStep\Installation\InstallClassDefinition;
use Pimcore\API\Plugin as PluginLib;
use Pimcore\Model;
use Pimcore\Model\Object;
use Exception;
use Pimcore\Model\Object\ClassDefinition;
use Pimcore\Model\Object\Fieldcollection;


abstract class PluginInstallation extends PluginLib\AbstractPlugin implements PluginLib\PluginInterface
{
    const PLUGIN_NAME = '';
    const DEFAULT_PAGE_FOLDER = '';
    const PAGE_CLASS = '';

    /**
     * @return string $statusMessage
     */
    public static function install()
    {
        // run all installation steps
        /** @var \PluginInstallationStep\Installation\InstallationStepInterface[] */
        $installationSteps = static::getInstallationSteps();
        foreach ($installationSteps as $installationStep) {
            /** @TODO check if $installationStep is instance of InstallationStepInterface */
            // Do not install again if already installed, except for the install class definition step, on upgrade this should always be saved
            if (!$installationStep->isInstalled() || $installationStep instanceof InstallClassDefinition) {
                $installationStep->install();
            }
        }

        if (static::isInstalled()) {
            return "Plugin successfully installed.";
        } else {
            return "Plugin could not be installed";
        }
    }

    /**
     * @return boolean $isInstalled
     */
    public static function isInstalled()
    {
        $isInstalled = true;

        /** @var \PluginInstallationStep\Installation\Instaluse Pimcore\Model\Object\ClassDefinition;
lationStepInterface $installationStep */
        foreach (static::getInstallationSteps() as $installationStep) {
            if (!$installationStep->isInstalled()) {
                $isInstalled = false;
                break;
            }
        }

        return $isInstalled;
    }

    public static function isClassInstalled(string $className)
    {
        $class = ClassDefinition::getByName($className);
        return !is_null($class);
    }

    /**
     * @return string $statusMessage
     */
    public static function uninstall()
    {
        // run all installation steps in the opposite direction
        $installationSteps = static::getInstallationSteps();
        $installationSteps = array_reverse($installationSteps);
        /** @var \PluginInstallationStep\Installation\InstallationStepInterface $installationStep */
        foreach ($installationSteps as $installationStep) {
            if ($installationStep->isInstalled()) {
                $installationStep->uninstall();
            }
        }

        if (!static::isInstalled()) {
            return "Plugin successfully uninstalled.";
        } else {
            return "Plugin could not be uninstalled";
        }
    }

    /**
     * update an existing classdefinition
     *
     * @TODO UpdateClassDefinition? || InstallClassDefinition::update
     *
     * @param string $className
     * @return bool
     */
    public static function updateClass(string $className = '')
    {

        if (empty($className)) {
            $className = static::PAGE_CLASS;
        }

        /** update class definition */
        if (static::isClassInstalled($className)) {
            // clear possible 'older' cached classdefinitions
            \Zend_Registry::_unsetInstance();

            $class = ClassDefinition::getByName($className);

            $filename = 'class_' . $className . '_export.json';
            $file = PIMCORE_PLUGINS_PATH . '/' . static::PLUGIN_NAME
                . '/data/' . $filename;
            if (! file_exists($file)) {
                throw new Exception('datafile not found ' . $filename);
            }

            $file =  file_get_contents($file);

            ClassDefinition\Service::importClassDefinitionFromJson(
                $class,
                $file,
                true
            );
        }

        return static::isInstalled();
    }

    /**
     * update an existing classdefinition
     *
     * @param string $className
     * @return bool
     */
    public static function updateFieldCollectionDefinition(string $fcName = '')
    {

        if (empty($fcName)) {
            $fcName = static::PAGE_CLASS;
        }

        /** update class definition */
        if (static::isInstalled()) {
            $fc = Fieldcollection\Definition::getByKey($fcName);
            $filename = 'fieldcollection_' . $fcName . '_export.json';
            $file = PIMCORE_PLUGINS_PATH . '/' . static::PLUGIN_NAME
                . '/data/' . $filename;
            if (! file_exists($file)) {
                throw new Exception('datafile not found ' . $filename);
            }

            $file =  file_get_contents($file);

            ClassDefinition\Service::importFieldCollectionFromJson(
                $fc,
                $file,
                true
            );
        }

        return static::isInstalled();
    }

    /**
     * update multiple classdefinitions in one run
     *
     * @param array $classNames
     * @return bool
     */
    public static function updateClasses(array $classNames)
    {
        foreach ($classNames as $className) {
            static::updateClass($className);
        }

        return static::isInstalled();
    }

    /**
     * @return \PluginInstallationStep\Installation\InstallationStepInterface[]
     */
    public static function getInstallationSteps()
    {
        return [
            new InstallClassDefinition(static::PAGE_CLASS),
            new CreateObjectFolderAndWebsiteSetting(
                static::CONFIG_OBJECT_FOLDER,
                static::DEFAULT_PAGE_FOLDER
            ),
        ];
    }
}
