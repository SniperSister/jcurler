<?php
/**
 * @version     %%PLUGINVERSION%%
 * @package     JCurler
 * @copyright   Copyright (C) 2013 David Jardin - djumla Webentwicklung
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.djumla.de
 */

// no direct access
defined('_JEXEC') or die;

/**
 * JCurler Plugin Class
 *
 */
class plgSystemJCurler extends JPlugin
{
    /**
     *  execute main plugin code right after the initialise process is done
     *
     *  @return boolean
     */
    function onAfterInitialise()
    {
        // get input object from application
        $input = JFactory::getApplication()->input;

        // version check
        $version = new JVersion();

        // abort if the current Joomla release is older
        if( version_compare( $version->getShortVersion(), "2.5.7", 'lt' ) ) {
            Jerror::raiseWarning(null, 'Cannot use JCurler in a Joomla release prior to 2.5.7');
            return false;
        }

        // abort if the current Joomla release is newer
        if( version_compare( $version->getShortVersion(), "2.5.14", 'gt' ) ) {
            Jerror::raiseWarning(null, 'Cannot use JCurler in Joomla 2.5.15 or later');
            return false;
        }

        // make sure that we really need this functionality
        if((!ini_get('allow_url_fopen')
            && is_callable('curl_init')
            && JFactory::getApplication() instanceof JAdministrator
            && $input->get('option', '') == "com_installer")
            || $this->params->get('forced', 0))
        {
            // include httpCurlStream which works as our stream wrapper for http
            include_once JPATH_PLUGINS."/system/jcurler/library/httpCurlStream.php";

            $wrappers = stream_get_wrappers();

            // Not specifying the STREAM_IS_URL parameters allow us
            //to bypass limitations of allow_url_fopen = 0
            if (array_search('http', $wrappers) !== false) {
                stream_wrapper_unregister('http');
                stream_wrapper_register('http', 'HTTPCurlStream');
            }

            if (array_search('https', $wrappers) !== false) {
                stream_wrapper_unregister('https');
                stream_wrapper_register('https', 'HTTPCurlStream');
            }

            // make the autoloader aware of our modified JUpdater class
            JLoader::register(
                'JUpdater',
                JPATH_PLUGINS."/system/jcurler/library/updater.php",
                true
            );
        }
    }
}
