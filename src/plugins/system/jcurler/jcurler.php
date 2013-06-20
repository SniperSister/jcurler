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
    function onAfterInitialise()
    {
        if(! ini_get('allow_url_fopen') && is_callable('curl_init')) {
            require_once(JPATH_PLUGINS."/system/jcurler/httpCurlStream.php");

            $wrappers = stream_get_wrappers();
            // Not specifying the STREAM_IS_URL parameters allow us to bypass limitations of allow_url_fopen = 0
            if (array_search('http', $wrappers) !== false)
                stream_wrapper_unregister('http');
            stream_wrapper_register('http', 'HTTPCurlStream') or die("Failed to register HTTP protocol.");

            if (array_search('https', $wrappers) !== false)
                stream_wrapper_unregister('https');
            stream_wrapper_register('https', 'HTTPCurlStream') or die("Failed to register HTTPS protocol.");

            JLoader::register('JUpdater', JPATH_PLUGINS."/system/jcurler/updater.php", true);
        }
    }
}
