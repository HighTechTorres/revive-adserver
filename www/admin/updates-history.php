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

// Preliminary check before including config.php to prevent it from outputting HTML code
// in case session is expired
if (!empty($_POST['xajax'])) {
    require_once MAX_PATH . '/www/admin/lib-sessions.inc.php';
    require_once MAX_PATH . '/lib/OA/Permission.php';
    unset($session);
    phpAds_SessionDataFetch();
    if (!OA_Permission::isAccount(OA_ACCOUNT_ADMIN)) {
        $_POST['xajax'] = 'sessionExpired';
        $_POST['xajaxargs'] = array();
        require_once MAX_PATH . '/lib/xajax.inc.php';
    }
}

// Required files
require_once MAX_PATH . '/www/admin/config.php';
require_once MAX_PATH . '/www/admin/lib-maintenance.inc.php';
require_once MAX_PATH . '/lib/OA/Sync.php';
require_once MAX_PATH . '/lib/OA/Upgrade/Upgrade.php';
$oUpgrader = new OA_Upgrade();
require_once MAX_PATH . '/lib/xajax.inc.php';


// Security check
OA_Permission::enforceAccount(OA_ACCOUNT_ADMIN);

/*-------------------------------------------------------*/
/* HTML framework                                        */
/*-------------------------------------------------------*/

phpAds_PageHeader("5.5");
phpAds_ShowSections(array("5.1", "5.2", "5.4", "5.5", "5.3", "5.6", "5.7"));
phpAds_MaintenanceSelection("history", "updates");

/*-------------------------------------------------------*/
/* Main code                                             */
/*-------------------------------------------------------*/

function getDBAuditTable($aAudit)
{
    $td = "<td class=\"tablebody\">%s</td>";
    $th = "<th align=\"left\" style='background-color: #ddd; border-bottom: 1px solid #ccc;'><b>%s</b></th>";
    $schemas = "<table width='100%' cellpadding='8' cellspacing='0' style='border: 1px solid #ccc; background-color: #eee;'>";
    $schemas.= "<tr>";
    //$schemas.= sprintf($th, 'schema');
    //$schemas.= sprintf($th, 'version');
    $schemas.= sprintf($th, 'Table origin');
    $schemas.= sprintf($th, 'Backup table');
    $schemas.= sprintf($th, 'Size');
    $schemas.= sprintf($th, 'Rows');
    //$schemas.= sprintf($th, 'Delete');
    $schemas.= "</tr>";
    $totalSize = 0;
    $totalRows = 0;
    foreach ($aAudit AS $k => $aRec)
    {
        $schemas.= "<tr>";
        //$schemas.= sprintf($td, $aRec['schema_name']);
        //$schemas.= sprintf($td, $aRec['version']);
        $schemas.= sprintf($td, $aRec['tablename']);
        $schemas.= sprintf($td, $aRec['tablename_backup']);
        $schemas.= sprintf($td, round($aRec['backup_size'] * 10, 2) . ' kb');
        $schemas.= sprintf($td, $aRec['backup_rows']);
        //$schemas.= sprintf($td, "<input type=\"checkbox\" id=\"chk_tbl[{$aRec['database_action_id']}]\" name=\"chk_tbl[{$aRec['database_action_id']}]\" checked />");
        $schemas.= "</tr>";
        $totalSize = $totalSize + $aRec['backup_size'];
        $totalRows = $totalRows + $aRec['backup_rows'];
    }

    $schemas.= "<tr>";
    $schemas.= sprintf($th, 'Total');
    $schemas.= sprintf($th, count($aAudit) . ' tables');
    $schemas.= sprintf($th, round($totalSize * 10, 2) . ' kb');
    $schemas.= sprintf($th, $totalRows);
    //$schemas.= sprintf($th, 'Delete');
    $schemas.= "</tr>";

    $schemas.= "</table>";
    return $schemas;
}

$oUpgrader->initDatabaseConnection();

if (array_key_exists('btn_clean_audit', $_POST))
{
    $upgrade_id = $_POST['upgrade_action_id'];
    $oUpgrader->oAuditor->cleanAuditArtifacts($upgrade_id);
}

$aAudit = $oUpgrader->oAuditor->queryAuditAllDescending();


/*-------------------------------------------------------*/
/* Error handling                                        */
/*-------------------------------------------------------*/

$aErrors = $oUpgrader->getErrors();
if (count($aErrors)>0)
{
?>
<div class='errormessage'><img class='errormessage' src='images/errormessage.gif' width='16' height='16' border='0' align='absmiddle'>
    <?php
        foreach ($aErrors AS $k => $err)
        {
            echo $err.'<br />';
        }
    ?>
</div>
<?php
}
$aMessages = $oUpgrader->getMessages();
if (count($aMessages)>0)
{
?>
<div class='errormessage' style='background-color: #eee;'><img class='errormessage' src='images/info.gif' width='16' height='16' border='0' align='absmiddle'>
    <?php
        foreach ($aMessages AS $k => $msg)
        {
            echo $msg.'<br />';
        }
    ?>
</div>
<?php
}

/*-------------------------------------------------------*/
/* Display                                               */
/*-------------------------------------------------------*/
?>
        <script type="text/javascript" src="js/xajax.js"></script>
        <script type="text/javascript">
        <?php
        include MAX_PATH . '/var/templates_compiled/schema.js';
        ?>
        </script>

		<table width='100%' border='0' cellspacing='0' cellpadding='0'>
		<tr>
			<td width='40'>&nbsp;</td>
			<td>
                <br /><br />
                <table border='0' width='90%' cellpadding='0' cellspacing='0'>
                <tr height='25'>
                    <td height='25'>&nbsp;</td>
                    <td height='25'>
                        <b style="color: #003399;">&nbsp;&nbsp;Date</b>
                    </td>
                    <td height="25">
                        <b style="color: #003399;">From Version</b>
                    </td>
                    <td height="25">
                        <b style="color: #003399;">To Version</b>
                    </td>
                    <td height="25">
                        <b style="color: #003399;">Status</b>
                    </td>
                    <td height='25' width='70'>
                        <b style="color: #003399;">&nbsp;</b>
                    </td>
                </tr>
                <tr height='1'>
                    <td colspan='6' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td>
                </tr>
                <?php
                $i=0;
                asort($aAudit);
                foreach ($aAudit AS $k => $v)
                {
                    if (($v['backups'] || !empty($v['logfile']) || !empty($v['confbackup'])) && $v['logfile'] != 'cleaned by user' && $v['logfile'] != 'file not found'&& $v['confbackup'] != 'cleaned by user' && $v['confbackup'] != 'file not found')
                    {
                        $v['backupsExist'] = true;
                    }
                ?>
                    <form name="frmOpenads" action="updates-history.php" method="POST">
                    <tr height='25' <?php echo ($i%2==0?"bgcolor='#F6F6F6'":""); ?>>
                        <?php
                            if ($v['backups']) {
                        ?>
                        <td height='25' width='25'>
                            &nbsp;<a href="#" onclick="return false;" title="Toggle data backup details"><img id="img_expand_<?php echo $v['upgrade_action_id']; ?>" src="images/<?php echo $phpAds_TextDirection; ?>/triangle-l.gif" alt="click to view backup details" onclick="xajax_expandOSURow('<?php echo $v['upgrade_action_id']; ?>');" border="0" /><img id="img_collapse_<?php echo $v['upgrade_action_id']; ?>" src="images/triangle-d.gif" style="display:none" alt="click to hide backup details" onclick="xajax_collapseOSURow('<?php echo $v['upgrade_action_id']; ?>');" border="0" /></a>
                        </td>
                        <td height='25'>
                            <b>&nbsp;<a href="#" title="Show data backup details" id="text_expand_<?php echo $v['upgrade_action_id']; ?>" onclick="xajax_expandOSURow('<?php echo $v['upgrade_action_id']; ?>');return false;"><?php echo $v['updated']; ?></a><a href="#" title="Hide data backup details" id="text_collapse_<?php echo $v['upgrade_action_id']; ?>" style="display:none" onclick="xajax_collapseOSURow('<?php echo $v['upgrade_action_id']; ?>');return false;"><?php echo $v['updated']; ?></a></b>
                        <?php
                            } else {
                        ?>
                            <td colspan="2"><b style="color: #003399;">&nbsp;<?php echo $v['updated']; ?></a></b></td>
                        <?php
                            }
                        ?>
                        </td>
                        <td height='25'>
                            <?php echo ($v['version_from']) ? $v['version_from'] : '<b>Installation</b>'; ?>
                        </td>
                        <td height='25'>
                            <?php echo $v['version_to']; ?>
                        </td>
                        <td height='25'>
                            <span style="text-transform:lowercase;"><?php  echo ($v['upgrade_name'] == 'version stamp') ? 'Updated database version stamp' : $v['description']; ?></span>
                        </td>
                        <td height='25' align='right'>
                        </td>
                </tr>
                <tr height='1'><td colspan='2' bgcolor='#F6F6F6'><img src='images/spacer.gif' width='1' height='1'></td><td colspan='4' bgcolor='#888888'><img src='images/break-l.gif' height='1' width='100%'></td></tr>
                <tr style="display:table-row;" <?php echo ($i%2==0?"bgcolor='#F6F6F6'":""); ?>>
                    <td colspan='2'>&nbsp;</td>
                    <td colspan='4'>
                        <table width='100%' cellpadding='5' cellspacing='0' border='0' style='border: 0px solid #ccc; margin: 10px 0 10px 0; '>
                        <tr height='20'>
                            <td width="235" style="border-bottom: 1px solid #ccc;">
                            Artifacts:
                            </td>
                            <td width="100" style="border-bottom: 1px solid #ccc;">
                            <?php echo ($v['backups']) ? "<b>" : ""; echo ($v['backupsExist']) ? $v['backups'] + !empty($v['logfile']) + !empty($v['confbackup']) : 0; echo ($v['backups']) ? "</b>" : ""; ?>
                            </td>
                            <td align="right" style="border-bottom: 1px solid #ccc;">
                            <?php
                            if ($v['backupsExist']) {
                            ?>
                                <img src='images/icon-recycle.gif' border='0' align='absmiddle' alt='Delete'><input type="submit" name="btn_clean_audit" onClick="return confirm('Do you really want to delete all backups created from this upgrade?')" style="cursor: pointer; border: 0; background: 0; color: #003399;font-size: 13px;" value="Delete Artifacts">
                            <?php
                            } else {
                            ?>
                                &nbsp;
                            <?php
                            }
                            ?>
                            </td>
                        </tr>
                        <tr>
                            <?php
                            if ($v['backupsExist']) {
                            ?>
                            <td width="235">
                            Backup database tables:
                            </td>
                            <td width="100" colspan="2">
                            <?php echo $v['backups'];
                            if ($v['backups']) {
                            ?>
                            <a href="#" onclick="return false;" title="Toggle data backup details"><img id="info_expand_<?php echo $v['upgrade_action_id']; ?>" src="images/info.gif" alt="click to view backup details" onclick="xajax_expandOSURow('<?php echo $v['upgrade_action_id']; ?>');" border="0" /><img id="info_collapse_<?php echo $v['upgrade_action_id']; ?>" src="images/info.gif" style="display:none" alt="click to hide backup details" onclick="xajax_collapseOSURow('<?php echo $v['upgrade_action_id']; ?>');" border="0" /></a>
                            <?php
                            }
                            ?>
                            </td>
                        </tr>
                        <tr height='20'>
                            <td>Log files:</td>
                            <td colspan="2">
                            <?php echo ($v['logfile']) ? '1' : '0'; ?>
                            </td>
                        </tr>
                        <tr height='20'>
                            <td>Conf backups:</td>
                            <td colspan="2">
                            <?php echo ($v['confbackup']) ? '1' : '0'; ?>
                            </td>
                            <?php
                            } else {
                            ?>
                            <td>&nbsp;</td>
                            <?php
                            }
                            ?>
            <?php
            if ($v['logfile'] || $v['confbackup'])
            {
            }
            ?>
                        </tr>
                        <tr>
                            <td colspan='3'>
                            <div id="cell_<?php echo $v['upgrade_action_id']; ?>"> </div>
                            </td>
                        </tr>
                        </table>
                    </td>
                    <input type="hidden" name="upgrade_action_id" value="<?php echo $v['upgrade_action_id']; ?>" />
                </tr>
              </form>
                <tr height='1'><td colspan='6' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>
                <?php
                    $i++;
                }
                ?>
                <tr height='25'>
                    <td colspan='6' height='25' align='right'>
                    </td>
                </tr>
                </table>
                <br /><br />
            </td>
			<td width='40'>&nbsp;</td>
		</tr>
		<tr>
			<td width='40' height='20'>&nbsp;</td>
			<td height='20'>&nbsp;</td>
		</tr>
		</table>
<?php

/*-------------------------------------------------------*/
/* Footer                                                */
/*-------------------------------------------------------*/

phpAds_PageFooter();

?>
