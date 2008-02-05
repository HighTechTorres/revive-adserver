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

// Require the initialisation file
require_once '../../init-delivery.php';

// Required files
require_once MAX_PATH . '/lib/max/Delivery/cache.php';
require_once MAX_PATH . '/lib/max/Delivery/javascript.php';
require_once MAX_PATH . '/lib/max/Delivery/flash.php';

// Get the affiliateid from the querystring if present
MAX_commonRegisterGlobalsArray(array('id'));

// Get JS
$output = OA_SPCGetJavaScript($id);

// Output JS
header("Content-Type: text/javascript");
header("Content-Size: ".strlen($output));
header("Expires: ".gmdate('r', time() + 86400));

// Flush cookies
MAX_cookieFlush();

echo $output;

function OA_SPCGetJavaScript($affiliateid)
{
    $conf = $GLOBALS['_MAX']['CONF'];
    $varprefix = $conf['var']['prefix'];
    $aZones = OA_cacheGetPublisherZones($affiliateid);
    foreach ($aZones as $zoneid => $aZone) {
        $zones[$aZone['type']][] = "            '" . addslashes($aZone['name']) . "' : {$zoneid}";
    }
    $additionalParams = '';
    foreach ($_GET as $key => $value) {
        if ($key == 'id') { continue; }
        $additionalParams .= "&amp;{$key}={$value}";
    }
    $script = "
    if (typeof({$varprefix}zones) != 'undefined') {
        var {$varprefix}zoneids = '';
        for (var zonename in {$varprefix}zones) {$varprefix}zoneids += escape(zonename+'=' + {$varprefix}zones[zonename] + \"|\");
        {$varprefix}zoneids += '&nz=1';
    } else {
        var {$varprefix}zoneids = '" . implode('|', array_keys($aZones)) . "';
    }

    if (typeof({$varprefix}source) == 'undefined') { {$varprefix}source = ''; }
    var {$varprefix}p=location.protocol=='https:'?'https:':'http:';
    var {$varprefix}r=Math.floor(Math.random()*99999999);
    {$varprefix}output = new Array();

    var {$varprefix}spc=\"<\"+\"script type='text/javascript' \";
    {$varprefix}spc+=\"src='\"+{$varprefix}p+\"".MAX_commonConstructPartialDeliveryUrl($conf['file']['singlepagecall'])."?zones=\"+{$varprefix}zoneids;
    {$varprefix}spc+=\"&source=\"+{$varprefix}source+\"&r=\"+{$varprefix}r;" .
    ((!empty($additionalParams)) ? "\n    {$varprefix}spc+=\"{$additionalParams}\";" : '') . "
    if (window.location) {$varprefix}spc+=\"&loc=\"+escape(window.location);
    if (document.referrer) {$varprefix}spc+=\"&referer=\"+escape(document.referrer);
    {$varprefix}spc+=\"'><\"+\"/script>\";
    document.write({$varprefix}spc);

    function {$varprefix}show(name) {
        if (typeof({$varprefix}output[name]) == 'undefined') {
            return;
        } else {
            document.write({$varprefix}output[name]);
        }
    }

    function {$varprefix}showpop(name) {
        if (typeof({$varprefix}popupZones[name]) == 'undefined') {
            return;
        }

        var {$varprefix}pop=\"<\"+\"script type='text/javascript' \";
        {$varprefix}pop+=\"src='\"+{$varprefix}p+\"".MAX_commonConstructPartialDeliveryUrl($conf['file']['popup'])."?zoneid=\"+{$varprefix}popupZones[name];
        {$varprefix}pop+=\"&source=\"+{$varprefix}source+\"&r=\"+{$varprefix}r;" .
        ((!empty($additionalParams)) ? "\n        {$varprefix}spc+=\"{$additionalParams}\";" : '') . "
        {$varprefix}spc+=\"{$additionalParams}\";
        if (window.location) {$varprefix}pop+=\"&loc=\"+escape(window.location);
        if (document.referrer) {$varprefix}pop+=\"&referer=\"+escape(document.referrer);
        {$varprefix}pop+=\"'><\"+\"/script>\";

        document.write({$varprefix}pop);
    }
";

    // Add the FlashObject include to the SPC output
    $script .= MAX_javascriptToHTML(MAX_flashGetFlashObjectExternal(), $varprefix . 'fo');

    return $script;
}

?>