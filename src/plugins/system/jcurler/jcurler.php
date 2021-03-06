<?php
/**
 * @version    %%PLUGINVERSION%%
 * @package    JCurler
 * @copyright  2013 David Jardin - djumla Webentwicklung
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link       http://www.djumla.de
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Class plgSystemJCurler
 *
 * @category  JCurler
 * @package   JCurler
 * @author    David Jardin <d.jardin@djumla.de>
 * @license   GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link      http://www.djumla.de
 * @since     1.0.0
 */
class PlgSystemJCurler extends JPlugin
{
    /**
     * execute main plugin code right after the initialise process is done
     *
     * @return  boolean
     */
    function onAfterInitialise()
    {
        // Get input object from application
        $input = JFactory::getApplication()->input;

        // version check
        $version = new JVersion();

        // abort if the current Joomla release is older
        if( version_compare( $version->getShortVersion(), "2.5.7", 'lt' ) ) {
            return false;
        }

        // abort if the current Joomla release is newer
        if( version_compare( $version->getShortVersion(), "2.5.14", 'gt' ) ) {
            return false;
        }
        
        // Make sure that we really need this functionality
        if ( !ini_get('allow_url_fopen')
         && is_callable('curl_init')
         && (JFactory::getApplication() instanceof JAdministrator
         && $input->get('option', '') == "com_installer")
         || $this->params->get('forced', 0))
        {
            // Include httpCurlStream which works as our stream wrapper for http
            include_once JPATH_PLUGINS . "/system/jcurler/library/httpCurlStream.php";

            $wrappers = stream_get_wrappers();

            // Not specifying the STREAM_IS_URL parameters allow us
            // To bypass limitations of allow_url_fopen = 0
            if (array_search('http', $wrappers) !== false)
            {
                stream_wrapper_unregister('http');
                stream_wrapper_register('http', 'HTTPCurlStream');
            }

            if (array_search('https', $wrappers) !== false)
            {
                stream_wrapper_unregister('https');
                stream_wrapper_register('https', 'HTTPCurlStream');
            }

            // Make the auto loader aware of our modified JUpdater class
            JLoader::register(
                'JUpdater',
                JPATH_PLUGINS . "/system/jcurler/library/updater.php",
                true
            );
        }
    }
}
