<?php
/**
 * @copyright Copyright (c) 2016 Studio Emma. (http://www.studioemma.com)
 */

namespace PluginInstallationStep\Installation;

use Exception;
use Pimcore\Model\Object\ClassDefinition;

class CreateExtraTableInDB implements InstallationStepInterface
{
    /** @var string */
    protected $tableName;

    /** @var string */
    protected $columnConfig;

    public function __construct($tableName, $columnConfig)
    {
        $this->tableName = $tableName;
        $this->columnConfig = $columnConfig;
    }

    /**
     * @return bool
     */
    public function install()
    {
        /** create table */
        if (!$this->isInstalled()) {

            $query = 'CREATE TABLE IF NOT EXISTS `' . $this->tableName . '` (';
            $addComma = false;
            foreach ($this->columnConfig["columns"] as $col => $type) {
                if ($addComma == true) {
                    $query .= ', ';
                }
                $query .= $col . ' ' . $type;
                $addComma = true;
            }
            if (isset($this->columnConfig["indexes"])) {
                foreach ($this->columnConfig["indexes"] as $index) {
                    $query .= ', ';
                    $query .= 'INDEX(' . $index . ')';
                }
            }
            $query .= ')';
            $this->getDB()->exec($query);
        }

        return $this->isInstalled();
    }

    /**
     * @return bool
     */
    public function uninstall()
    {
        $query = 'DROP TABLE IF EXISTS `' . $this->tableName . '` ';
        $this->getDB()->exec($query);

        return !$this->isInstalled();
    }

    /**
     * @return bool
     */
    public function isInstalled()
    {
        try {
            $query = 'DESC `' . $this->tableName . '`';
            $stmt = $this->getDB()->prepare($query);
            $stmt->execute();
            return true;
        } catch (\Exception $e) {
            return false;
        }
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
        return 'create new table in the database';
    }


    protected function getDB() {
        return \Pimcore\Db::getConnection()->getResource();
    }
}
