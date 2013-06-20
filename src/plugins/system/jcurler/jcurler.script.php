<?php
/**
 * @version     %%PLUGINVERSION%%
 * @package     JCurler
 * @copyright   Copyright (C) 2013 David Jardin - djumla Webentwicklung
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.djumla.de
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

class plgsystemjcurlerInstallerScript
{
    /**
     * Called before any type of action
     *
     * @param   string  $type  Which action is happening (install|uninstall|discover_install)
     * @param   JAdapterInstance  $parent  The object responsible for running this script
     *
     * @return  boolean  True on success
     */
    public function preflight($type, JAdapterInstance $parent) {
        $version = new JVersion();

        // abort if the current Joomla release is older
        if( version_compare( $version->getShortVersion(), "2.5.7", 'lt' ) ) {
            Jerror::raiseWarning(null, 'Cannot install JCurler in a Joomla release prior to 2.5.7');
            return false;
        }

        // abort if the current Joomla release is newer
        if( version_compare( $version->getShortVersion(), "2.6.0", 'gt' ) ) {
            Jerror::raiseWarning(null, 'Cannot install JCurler in Joomla 3.x or later');
            return false;
        }

        // abort if curl is not installed
        if( !is_callable('curl_init')) {
            Jerror::raiseWarning(null, 'This plugin requires cURL - please install it first');
            return false;
        }

        return true;
    }
}