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

/**
 * Table Definition for channel
 */

require_once MAX_PATH . '/lib/max/other/lib-acl.inc.php';
require_once MAX_PATH . '/lib/OA/Dal.php';
require_once 'DB_DataObjectCommon.php';

class DataObjects_Channel extends DB_DataObjectCommon
{
    var $onDeleteCascade = true;
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'channel';                         // table name
    var $channelid;                       // int(9)  not_null primary_key auto_increment
    var $agencyid;                        // int(9)  not_null
    var $affiliateid;                     // int(9)  not_null
    var $name;                            // string(255)
    var $description;                     // string(255)
    var $compiledlimitation;              // blob(65535)  not_null blob
    var $acl_plugins;                     // blob(65535)  blob
    var $active;                          // int(1)
    var $comments;                        // blob(65535)  blob
    var $updated;                         // datetime(19)  not_null binary
    var $acls_updated;                    // datetime(19)  not_null binary

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('DataObjects_Channel',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

    function delete($useWhere = false, $cascade = true, $parentid = null)
    {
    	// Find acls which use this channels
    	$dalAcls = OA_Dal::factoryDAL('acls');
    	$rsChannel = $dalAcls->getAclsByDataValueType($this->channelid, 'Site:Channel');
    	$rsChannel->reset();
    	while ($rsChannel->next()) {
    	    // Get the IDs of the banner that's using this channel
    	    $bannerId = $rsChannel->get('bannerid');

    	    // Get the remaining channels the banner will use, if any
    		$aChannelIds = explode(',', $rsChannel->get('data'));
    		$aChannelIds = array_diff($aChannelIds, array($this->channelid));

    		// Prepare to update the banner's limitations in the "acls" table
    		$doAcls = DB_DataObject::factory('acls');
    		$doAcls->init();
    		$doAcls->bannerid = $bannerId;
    		$doAcls->executionorder = $rsChannel->get('executionorder');
    		if (!empty($aChannelIds)) {
	    		$doAcls->data = implode(',', $aChannelIds);
	    		$doAcls->update();
    		} else {
    			$doAcls->delete();
    		}

    		// Re-compile the banner's limitations
            $aAcls = array();
    		$doAcls = DB_DataObject::factory('acls');
    		$doAcls->init();
    		$doAcls->bannerid = $bannerId;
    		$doAcls->orderBy('executionorder');
            $doAcls->find();
            while ($doAcls->fetch()) {
                $aData = $doAcls->toArray();
                list($package, $name) = explode(':', $aData['type']);
                $deliveryLimitationPlugin = MAX_Plugin::factory('deliveryLimitations', ucfirst($package), ucfirst($name));
                $deliveryLimitationPlugin->init($aData);
                if ($deliveryLimitationPlugin->isAllowed($page)) {
                    $aAcls[$aData['executionorder']] = $aData;
                }
            }
            $sLimitation = MAX_AclGetCompiled($aAcls, $page);
            // TODO: it should be done inside plugins instead, there is no need to slash the data
            $sLimitation = (!get_magic_quotes_runtime()) ? stripslashes($sLimitation) : $sLimitation;
            $doBanners = OA_Dal::factoryDO('banners');
            $doBanners->bannerid = $bannerId;
            $doBanners->find();
            $doBanners->fetch();
            $doBanners->acl_plugins = MAX_AclGetPlugins($aAcls);
            $doBanners->acls_updated = OA::getNow();
            $doBanners->compiledlimitation = $sLimitation;
            $doBanners->update();
    	}

    	return parent::delete($useWhere, $cascade, $parentid);
    }

    function duplicate($channelId)
    {
        //  Populate $this with channel data
        $this->get($channelId);

        // Prepare a new name for the channel
        $this->name = $this->getUniqueNameForDuplication('name');

        // Duplicate channel
        $this->channelid = null;
        $newChannelId = $this->insert();

        // Duplicate channel's acls
        $result = OA_Dal::staticDuplicate('acls_channel', $channelId, $newChannelId);

        return $newChannelId;
    }


    function _auditEnabled()
    {
        return true;
    }

     function _getContextId()
    {
        return $this->channelid;
    }

    function _getContext()
    {
        return 'Channel';
    }

    /**
     * A private method to return the account ID of the
     * account that should "own" audit trail entries for
     * this entity type; NOT related to the account ID
     * of the currently active account performing an
     * action.
     *
     * @return integer The account ID to insert into the
     *                 "account_id" column of the audit trail
     *                 database table.
     */
    function getOwningAccountId()
    {
        if (!empty($this->affiliateid)) {
            return $this->_getOwningAccountIdFromParent('affiliates', 'affiliateid');
        }

        return $this->_getOwningAccountIdFromParent('agency', 'agencyid');
    }

    /**
     * build a client specific audit array
     *
     * @param integer $actionid
     * @param array $aAuditFields
     */
    function _buildAuditArray($actionid, &$aAuditFields)
    {
        $aAuditFields['key_desc']   = $this->name;
        switch ($actionid)
        {
            case OA_AUDIT_ACTION_INSERT:
            case OA_AUDIT_ACTION_DELETE:
                        $aAuditFields['active'] = $this->_formatValue('active');
                        break;
            case OA_AUDIT_ACTION_UPDATE:
                        $aAuditFields['affiliateid'] = $this->affiliateid;
                        break;
        }
    }

    function _formatValue($field)
    {
        switch ($field)
        {
            case 'active':
                return $this->_boolToStr($this->$field);
            default:
                return $this->$field;
        }
    }
}

?>