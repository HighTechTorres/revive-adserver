<?php

/*
+---------------------------------------------------------------------------+
| Openads v${RELEASE_MAJOR_MINOR}                                                              |
| ============                                                              |
|                                                                           |
| Copyright (c) 2003-2008 m3 Media Services Ltd                             |
| For contact details, see: http://www.openx.org/                           |
|                                                                           |
| Copyright (c) 2000-2003 the phpAdsNew developers                          |
| For contact details, see: http://www.phpadsnew.com/                       |
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
require_once '../../init.php';

// Required files
require_once MAX_PATH . '/www/admin/config.php';
require_once MAX_PATH . '/www/admin/lib-statistics.inc.php';
require_once MAX_PATH . '/lib/OA/Admin/UI/UserAccess.php';

// Security check
OA_Permission::enforceAccount(OA_ACCOUNT_ADMIN, OA_ACCOUNT_MANAGER);
OA_Permission::enforceAccountPermission(OA_ACCOUNT_MANAGER, OA_PERM_SUPER_ACCOUNT);
OA_Permission::enforceAccessToObject('agency', $agencyid);

/*-------------------------------------------------------*/
/* HTML framework                                        */
/*-------------------------------------------------------*/

if (OA_Permission::isAccount(OA_ACCOUNT_ADMIN)) {
    phpAds_PageHeader("4.1.3.1");
    echo "<img src='images/icon-advertiser.gif' align='absmiddle'>&nbsp;<b>".phpAds_getClientName($agencyid)."</b><br /><br /><br />";
    phpAds_ShowSections(array("4.1.2", "4.1.3", "4.1.3.1"));
} else {
    phpAds_PageHeader('4.4.1');
    phpAds_ShowSections(array("4.1", "4.2", "4.3", "4.4", "4.4.1"));
}

/*-------------------------------------------------------*/
/* Main code                                             */
/*-------------------------------------------------------*/

require_once MAX_PATH . '/lib/OA/Admin/Template.php';

$oTpl = new OA_Admin_Template('agency-user-start.html');
OA_Admin_UI_UserAccess::assignUserStartTemplateVariables($oTpl);
$oTpl->assign('action', 'agency-user.php');
$oTpl->assign('entityIdName', 'agencyid');
$oTpl->assign('entityIdValue', $agencyid);
$oTpl->display();

/*-------------------------------------------------------*/
/* HTML framework                                        */
/*-------------------------------------------------------*/

phpAds_PageFooter();

?>
