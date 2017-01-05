<?php
/**
 * @copyright Copyright (c) 2016 Studio Emma. (http://www.studioemma.com)
 */

namespace PluginInstallationStep\Installation;

use Exception;
use Pimcore\Model\Object\Fieldcollection;
use Pimcore\Model\Object\ClassDefinition;

class InstallFieldCollectionDefinition implements InstallationStepInterface
{
    /** @var string */
    protected $fieldCollectionName;
    /** @var string */
    protected $pluginName;

    public function __construct($fieldCollectionName, $pluginName = null)
    {
        $this->fieldCollectionName = $this->pluginName = $fieldCollectionName;
        if (null !== $pluginName) {
            $this->pluginName = $pluginName;
        }
    }

    /**
     * @return bool
     */
    public function install()
    {
        /** create FieldCollection */
        if (!$this->isInstalled()) {

            /** create Fieldcollection */
            $fieldCollection = new Fieldcollection\Definition();
            $fieldCollection->setKey($this->fieldCollectionName);
            $fieldCollection->save();
            $file = $this->getDataFile();
            ClassDefinition\Service::importFieldCollectionFromJson(
                $fieldCollection,
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
        /** remove Page class */
        $fieldCollection = Fieldcollection\Definition::getByKey(
            $this->fieldCollectionName
        );
        $fieldCollection->delete();

        return !$this->isInstalled();
    }

    /**
     * @return bool
     */
    public function isInstalled()
    {
        try {
            $fieldCollection = Fieldcollection\Definition::getByKey(
                $this->fieldCollectionName
            );
        } catch (Exception $e) {
            // if field collection does not exist, getByKey will throw an
            // exception
            return false;
        }
        return !is_null($fieldCollection);
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
        return 'Install fieldcollection definition';
    }

    protected function getDataFile()
    {
        $file = PIMCORE_PLUGINS_PATH
            . '/' . $this->pluginName
            . '/data/'
            . 'fieldcollection_' . $this->fieldCollectionName . '_export.json';
        if (! file_exists($file)) {
            throw new Exception('datafile not found ' . $file);
        }

        return file_get_contents($file);
    }
}
