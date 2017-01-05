<?php
/**
 * @copyright Copyright (c) 2016 Studio Emma. (http://www.studioemma.com)
 */

namespace PluginInstallationStep\Installation;

interface InstallationStepInterface
{
    /**
     * @return bool
     */
    public function install();

    /**
     * @return bool
     */
    public function uninstall();

    /**
     * @return bool
     */
    public function isInstalled();

    /**
     * @return bool
     */
    public function needsReloadAfterInstall();

    /**
     * @return string
     */
    public function __toString();
}
