<?php

/*
+---------------------------------------------------------------------------+
| Openads v${RELEASE_MAJOR_MINOR}                                                              |
| ============                                                              |
|                                                                           |
| Copyright (c) 2003-2008 m3 Media Services Ltd                             |
| For contact details, see: http://www.openx.org/                           |
|                                                                           |
| This program is free software; you can redistribute it and/or modify      |
| it under the terms of the GNU General Public License as published by      |
| the Free Software Foundation; either version 2 of the License, or         |
| (at your option) any later version.                                       |
|                                                                           |
| This program is distributed in the hope that it will be useful,           |
| but WITHOUT ANY WARRANTY; without even the implied warranty of            |
| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
| GNU General Public License for more details.                              |
|                                                                           |
| You should have received a copy of the GNU General Public License         |
| along with this program; if not, write to the Free Software               |
| Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA |
+---------------------------------------------------------------------------+
$Id$
*/

require_once MAX_PATH . '/lib/OA/Permission.php';

/**
 * @package    MaxUI
 * @subpackage Language
 * @author     Andrew Hill <andrew@m3.net>
 */

/**
 * A class that can be used to load the necessary language file(s) for
 * the user interface.
 *
 * @static
 */
class Language_Default
{
    /**
     * The method to load the default language file(s).
     */
    function load()
    {
        $aConf = $GLOBALS['_MAX']['CONF'];
        if (!empty($GLOBALS['_MAX']['PREF'])) {
            $aPref = $GLOBALS['_MAX']['PREF'];
        } else {
            $aPref = array();
        }
        // Always load the English language, in case of incomplete translations
        include_once MAX_PATH . '/lib/max/language/english/default.lang.php';
        // Load the language from preferences, if possible, otherwise load
        // the global preference, if possible
        if (!empty($aPref['language']) && ($aPref['language'] != 'english') && file_exists(MAX_PATH .
                '/lib/max/language/' . $aPref['language'] . '/default.lang.php')) {
            include_once MAX_PATH . '/lib/max/language/' . $aPref['language'] .
                '/default.lang.php';
        } elseif (($aConf['max']['language'] != 'english') && file_exists(MAX_PATH .
                '/lib/max/language/' . $aConf['max']['language'] . '/default.lang.php')) {
            include_once MAX_PATH . '/lib/max/language/' . $aConf['max']['language'] .
                '/default.lang.php';
        }
    }
}

?>
