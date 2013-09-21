<?php
/**
 * @version    %%PLUGINVERSION%%
 * @package    JCurler
 * @copyright  2013 David Jardin - djumla Webentwicklung
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link       http://www.djumla.de
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * Class PlgSystemJcurlerInstallerScript
 *
 * @category  JCurler
 * @package   JCurler
 * @author    David Jardin <d.jardin@djumla.de>
 * @license   GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link      http://www.djumla.de
 * @since     1.0.0
 */
class PlgSystemJcurlerInstallerScript
{
    /**
     * Called before any type of action
     *
     * @return  boolean  True on success
     */
    public function preflight()
    {
        $version = new JVersion;

        // Abort if the current Joomla release is older
        if (version_compare($version->getShortVersion(), "2.5.7", 'lt'))
        {
            Jerror::raiseWarning(null, 'Cannot install JCurler in a Joomla release prior to 2.5.7');

            return false;
        }

        // Abort if the current Joomla release is newer
        if (version_compare($version->getShortVersion(), "2.6.0", 'gt'))
        {
            Jerror::raiseWarning(null, 'Cannot install JCurler in Joomla 3.x or later');

            return false;
        }

        // Abort if curl is not installed
        if (!is_callable('curl_init'))
        {
            Jerror::raiseWarning(null, 'This plugin requires cURL - please active it');

            return false;
        }

        return true;
    }
}