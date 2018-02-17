<?php

/*
* e107 website system
*
* Copyright (C) 2008-2009 e107 Inc (e107.org)
* timed_userclassd under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*
* e107 timed_userclass Plugin
*
* $Source: /cvs_backup/e107_0.8/e107_plugins/timed_userclass/admin_config.php,v $
* $Revision$
* $Date$
* $Author$
*
*/

require_once ("../../class2.php");
if (!getperms("P"))
{
    e107::redirect('admin');
    exit;
}
e107::lan('timed_userclass', true, true);

class plugin_timed_userclass_admin extends e_admin_dispatcher
{
    /**
     * Format: 'MODE' => array('controller' =>'CONTROLLER_CLASS'[, 'index' => 'list', 'path' => 'CONTROLLER SCRIPT PATH', 'ui' => 'UI CLASS NAME child of e_admin_ui', 'uipath' => 'UI SCRIPT PATH']);
     * Note - default mode/action is autodetected in this order:
     * - $defaultMode/$defaultAction (owned by dispatcher - see below)
     * - $adminMenu (first key if admin menu array is not empty)
     * - $modes (first key == mode, corresponding 'index' key == action)
     * @var array
     */
    protected $modes = array('main' => array(
            'controller' => 'plugin_timed_userclass_admin_ui',
            'path' => null,
            'ui' => 'plugin_timed_userclass_admin_form_ui',
            'uipath' => null));

    /* Both are optional
    * protected $defaultMode = null;
    * protected $defaultAction = null;
    */

    /**
     * Format: 'MODE/ACTION' => array('caption' => 'Menu link title'[, 'url' => '{e_PLUGIN}timed_userclass/admin_config.php', 'perm' => '0']);
     * Additionally, any valid e107::getNav()->admin() key-value pair could be added to the above array
     * @var array
     */
    protected $adminMenu = array(
        'main/list' => array('caption' => 'Manage Times', 'perm' => '0'),
        'main/create' => array('caption' => LAN_CREATE, 'perm' => '0'),
        'main/prefs' => array('caption' => 'Settings', 'perm' => '0'),
        'main/rules' => array('caption' => 'Manage Rules', 'perm' => '0'),
        'main/rulecreate' => array('caption' => 'LAN_CREATE', 'perm' => '0')
        );

    /**
     * Optional, mode/action aliases, related with 'selected' menu CSS class
     * Format: 'MODE/ACTION' => 'MODE ALIAS/ACTION ALIAS';
     * This will mark active main/list menu item, when current page is main/edit
     * @var array
     */
    protected $adminMenuAliases = array('main/edit' => 'main/list');

    /**
     * Navigation menu title
     * @var string
     */
    protected $menuTitle = 'Timed Userclass';
}


class plugin_timed_userclass_admin_ui extends e_admin_ui
{
    // required
    protected $pluginTitle = "timed_userclass";

    /**
     * plugin name or 'core'
     * IMPORTANT: should be 'core' for non-plugin areas because this
     * value defines what CONFIG will be used. However, I think this should be changed
     * very soon (awaiting discussion with Cam)
     * Maybe we need something like $prefs['core'], $prefs['timed_userclass'] ... multiple getConfig support?
     *
     * @var string
     */
    protected $pluginName = 'timed_userclass';

    /**
     * DB Table, table alias is supported
     * Example: 'r.timed_userclass'
     * @var string
     */
    protected $table = "tclass";

    /**
     * This is only needed if you need to JOIN tables AND don't wanna use $tableJoin
     * Write your list query without any Order or Limit.
     *
     * @var string [optional]
     */
    protected $listQry = "";
    //

    // optional - required only in case of e.g. tables JOIN. This also could be done with custom model (set it in init())
    //protected $editQry = "SELECT * FROM #timed_userclass WHERE timed_userclass_id = {ID}";

    // required - if no custom model is set in init() (primary id)
    protected $pid = "tclass_id";

    // optional
    protected $perPage = 20;

    protected $batchDelete = true;

    //	protected \$sortField		= 'somefield_order';


    //	protected \$sortParent      = 'somefield_parent';


    //	protected \$treePrefix      = 'somefield_title';


    //TODO change the timed_userclass_url type back to URL before timed_userclass.
    // required
    /**
     * (use this as starting point for wiki documentation)
     * $fields format  (string) $field_name => (array) $attributes
     *
     * $field_name format:
     * 	'table_alias_or_name.field_name.field_alias' (if JOIN support is needed) OR just 'field_name'
     * NOTE: Keep in mind the count of exploded data can be 1 or 3!!! This means if you wanna give alias
     * on main table field you can't omit the table (first key), alternative is just '.' e.g. '.field_name.field_alias'
     *
     * $attributes format:
     * 	- title (string) Human readable field title, constant name will be accpeted as well (multi-language support
     *
     *  - type (string) null (means system), number, text, dropdown, url, image, icon, datestamp, userclass, userclasses, user[_name|_loginname|_login|_customtitle|_email],
     *    boolean, method, ip
     *  	full/most recent reference list - e_form::renderTableRow(), e_form::renderElement(), e_admin_form_ui::renderBatchFilter()
     *  	for list of possible read/writeParms per type see below
     *
     *  - data (string) Data type, one of the following: int, integer, string, str, float, bool, boolean, model, null
     *    Default is 'str'
     *    Used only if $dataFields is not set
     *  	full/most recent reference list - e_admin_model::sanitize(), db::_getFieldValue()
     *  - dataPath (string) - xpath like path to the model/posted value. Example: 'dataPath' => 'prefix/mykey' will result in $_POST['prefix']['mykey']
     *  - primary (boolean) primary field (obsolete, $pid is now used)
     *
     *  - help (string) edit/create table - inline help, constant name will be accpeted as well, optional
     *  - note (string) edit/create table - text shown below the field title (left column), constant name will be accpeted as well, optional
     *
     *  - validate (boolean|string) any of accepted validation types (see e_validator::$_required_rules), true == 'required'
     *  - rule (string) condition for chosen above validation type (see e_validator::$_required_rules), not required for all types
     *  - error (string) Human readable error message (validation failure), constant name will be accepted as well, optional
     *
     *  - batch (boolean) list table - add current field to batch actions, in use only for boolean, dropdown, datestamp, userclass, method field types
     *    NOTE: batch may accept string values in the future...
     *  	full/most recent reference type list - e_admin_form_ui::renderBatchFilter()
     *
     *  - filter (boolean) list table - add current field to filter actions, rest is same as batch
     *
     *  - forced (boolean) list table - forced fields are always shown in list table
     *  - nolist (boolean) list table - don't show in column choice list
     *  - noedit (boolean) edit table - don't show in edit mode
     *
     *  - width (string) list table - width e.g '10%', 'auto'
     *  - thclass (string) list table header - th element class
     *  - class (string) list table body - td element additional class
     *
     *  - readParms (mixed) parameters used by core routine for showing values of current field. Structure on this attribute
     *    depends on the current field type (see below). readParams are used mainly by list page
     *
     *  - writeParms (mixed) parameters used by core routine for showing control element(s) of current field.
     *    Structure on this attribute depends on the current field type (see below).
     *    writeParams are used mainly by edit page, filter (list page), batch (list page)
     *
     * $attributes['type']->$attributes['read/writeParams'] pairs:
     *
     * - null -> read: n/a
     * 		  -> write: n/a
     *
     * - dropdown -> read: 'pre', 'post', array in format posted_html_name => value
     * 			  -> write: 'pre', 'post', array in format as required by e_form::selectbox()
     *
     * - user -> read: [optional] 'link' => true - create link to user profile, 'idField' => 'author_id' - tells to renderValue() where to search for user id (used when 'link' is true and current field is NOT ID field)
     * 				   'nameField' => 'comment_author_name' - tells to renderValue() where to search for user name (used when 'link' is true and current field is ID field)
     * 		  -> write: [optional] 'nameField' => 'comment_author_name' the name of a 'user_name' field; 'currentInit' - use currrent user if no data provided; 'current' - use always current user(editor); '__options' e_form::userpickup() options
     *
     * - number -> read: (array) [optional] 'point' => '.', [optional] 'sep' => ' ', [optional] 'decimals' => 2, [optional] 'pre' => '&euro; ', [optional] 'post' => 'LAN_CURRENCY'
     * 			-> write: (array) [optional] 'pre' => '&euro; ', [optional] 'post' => 'LAN_CURRENCY', [optional] 'maxlength' => 50, [optional] '__options' => array(...) see e_form class description for __options format
     *
     * - ip		-> read: n/a
     * 			-> write: [optional] element options array (see e_form class description for __options format)
     *
     * - text -> read: (array) [optional] 'htmltruncate' => 100, [optional] 'truncate' => 100, [optional] 'pre' => '', [optional] 'post' => ' px'
     * 		  -> write: (array) [optional] 'pre' => '', [optional] 'post' => ' px', [optional] 'maxlength' => 50 (default - 255), [optional] '__options' => array(...) see e_form class description for __options format
     *
     * - textarea 	-> read: (array) 'noparse' => '1' default 0 (disable toHTML text parsing), [optional] 'bb' => '1' (parse bbcode) default 0,
     * 								[optional] 'parse' => '' modifiers passed to e_parse::toHTML() e.g. 'BODY', [optional] 'htmltruncate' => 100,
     * 								[optional] 'truncate' => 100, [optional] 'expand' => '[more]' title for expand link, empty - no expand
     * 		  		-> write: (array) [optional] 'rows' => '' default 15, [optional] 'cols' => '' default 40, [optional] '__options' => array(...) see e_form class description for __options format
     * 								[optional] 'counter' => 0 number of max characters - has only visual effect, doesn't truncate the value (default - false)
     *
     * - bbarea -> read: same as textarea type
     * 		  	-> write: (array) [optional] 'pre' => '', [optional] 'post' => ' px', [optional] 'maxlength' => 50 (default - 0),
     * 				[optional] 'size' => [optional] - medium, small, large - default is medium,
     * 				[optional] 'counter' => 0 number of max characters - has only visual effect, doesn't truncate the value (default - false)
     *
     * - image -> read: [optional] 'title' => 'SOME_LAN' (default - LAN_PREVIEW), [optional] 'pre' => '{e_PLUGIN}myplug/images/',
     * 				'thumb' => 1 (true) or number width in pixels, 'thumb_urlraw' => 1|0 if true, it's a 'raw' url (no sc path constants),
     * 				'thumb_aw' => if 'thumb' is 1|true, this is used for Adaptive thumb width
     * 		   -> write: (array) [optional] 'label' => '', [optional] '__options' => array(...) see e_form::imagepicker() for allowed options
     *
     * - icon  -> read: [optional] 'class' => 'S16', [optional] 'pre' => '{e_PLUGIN}myplug/images/'
     * 		   -> write: (array) [optional] 'label' => '', [optional] 'ajax' => true/false , [optional] '__options' => array(...) see e_form::iconpicker() for allowed options
     *
     * - datestamp  -> read: [optional] 'mask' => 'long'|'short'|strftime() string, default is 'short'
     * 		   		-> write: (array) [optional] 'label' => '', [optional] 'ajax' => true/false , [optional] '__options' => array(...) see e_form::iconpicker() for allowed options
     *
     * - url	-> read: [optional] 'pre' => '{ePLUGIN}myplug/'|'http://somedomain.com/', 'truncate' => 50 default - no truncate, NOTE:
     * 			-> write:
     *
     * - method -> read: optional, passed to given method (the field name)
     * 			-> write: optional, passed to given method (the field name)
     *
     * - hidden -> read: 'show' => 1|0 - show hidden value, 'empty' => 'something' - what to be shown if value is empty (only id 'show' is 1)
     * 			-> write: same as readParms
     *
     * - upload -> read: n/a
     * 			-> write: Under construction
     *
     * Special attribute types:
     * - method (string) field name should be method from the current e_admin_form_ui class (or its extension).
     * 		Example call: field_name($value, $render_action, $parms) where $value is current value,
     * 		$render_action is on of the following: read|write|batch|filter, parms are currently used paramateres ( value of read/writeParms attribute).
     * 		Return type expected (by render action):
     * 			- read: list table - formatted value only
     * 			- write: edit table - form element (control)
     * 			- batch: either array('title1' => 'value1', 'title2' => 'value2', ..) or array('singleOption' => '<option value="somethig">Title</option>') or rendered option group (string '<optgroup><option>...</option></optgroup>'
     * 			- filter: same as batch
     * @var array
     */
    protected $fields = array(
        'checkboxes' => array(
            'title' => '',
            'type' => null,
            'data' => null,
            'width' => '5%',
            'thclass' => 'center',
            'forced' => true,
            'class' => 'center',
            'toggle' => 'e-multiselect'),
        'tclass_id' => array(
            'title' => LAN_ID,
            'type' => 'number',
            'data' => 'int',
            'width' => '5%',
            'thclass' => '',
            'class' => 'center',
            'forced' => true,
            'primary' => true /*, 'noedit'=>TRUE*/ ), //Primary ID is not editable
        'tclass_userid' => array(
            'title' => TCLASS_A3,
            'type' => 'user',
            'data' => 'str',
            'width' => '30%',
            'thclass' => '',
            'forced' => true,
            ), //Primary ID is not editable
        'tclass_from' => array(
            'title' => TCLASS_A4,
            'type' => 'userclass',
            'data' => 'str',
            'width' => 'auto',
            'thclass' => '',
            'batch' => true,
            'filter' => true,
            'forced' => true,
            ),
        'tclass_to' => array(
            'title' => TCLASS_A5,
            'type' => 'userclass',
            'data' => 'str',
            'width' => 'auto',
            'thclass' => '',
            'batch' => true,
            'filter' => true,
            'forced' => true),
        'tclass_start' => array(
            'title' => TCLASS_A6,
            'type' => 'datestamp',
            'data' => 'int',
            'width' => 'auto',
            'thclass' => '',
            'batch' => true,
            'filter' => true,
            'forced' => true),
        'tclass_admin' => array(
            'title' => TCLASS_A38,
            'type' => 'checkbox',
            'data' => 'str',
            'width' => 'auto',
            'thclass' => ''),
        'tclass_notify' => array(
            'title' => TCLASS_A2,
            'type' => 'method',
            'data' => 'str',
            'width' => 'auto',
            'thclass' => 'left'),
        'tclass_donestart' => array(
            'title' => TCLASS_A54,
            'type' => 'checkbox',
            'data' => 'int',
            'width' => 'auto',
            'thclass' => 'left'),
        'options' => array(
            'title' => LAN_OPTIONS,
            'type' => null,
            'data' => null,
            'width' => '10%',
            'thclass' => 'center last',
            'class' => 'center last',
            'forced' => true));

    //required - default column user prefs
    protected $fieldpref = array(
        'checkboxes',
        'timed_userclass_id',
        'timed_userclass_type',
        'timed_userclass_url',
        'timed_userclass_compatibility',
        'options');

    // FORMAT field_name=>type - optional if fields 'data' attribute is set or if custom model is set in init()
    /*protected $dataFields = array();*/

    // optional, could be also set directly from $fields array with attributes 'validate' => true|'rule_name', 'rule' => 'condition_name', 'error' => 'Validation Error message'
    /*protected  $validationRules = array(
    * 'timed_userclass_url' => array('required', '', 'timed_userclass URL', 'Help text', 'not valid error message')
    * );*/

    // optional, if $pluginName == 'core', core prefs will be used, else e107::getPluginConfig($pluginName);
    protected $prefs = array(
        'pluginActive' => array(
            'title' => TCLASS_A35,
            'type' => 'checkbox',
            'data' => 'int',
            'validate' => true),
        'useCSS' => array(
            'title' => TCLASS_A30,
            'type' => 'checkbox',
            'data' => 'integer'),
        'emailFrom' => array(
            'title' => TCLASS_A31,
            'type' => 'text',
            'data' => 'str'),
        'emailAddress' => array(
            'title' => TCLASS_A32,
            'help' => OTD_H03,
            'type' => 'email',
            'data' => 'str'),
        'pmAs' => array(
            'title' => TCLASS_A33,
            'type' => 'method',
            'data' => 'integer'),
 /*        'otd_adminclass' => array(
            'title' => OTD_A57,
            'type' => 'userclass',
            'data' => 'integer'),
           
        'otd_showall' => array(
            'title' => OTD_A58,
            'type' => 'boolean',
            'data' => 'integer')
            */
            );

    // optional
    public function init()
    {
     }

 
}

class plugin_timed_userclass_admin_form_ui extends e_admin_form_ui
{

    function tclass_notify($curVal, $mode) // not really necessary since we can use 'dropdown' - but just an example of a custom function.
    {
        $frm = e107::getForm();

        
            $types[0] = TCLASS_A53;
            $types[1] = TCLASS_A26;
            $types[2] = TCLASS_A27;

        if ($mode == 'read')
        {
            return vartrue($types[$curVal]);
        }

        if ($mode == 'batch') // Custom Batch List for otd_day
        {
            return $types;
        }

        if ($mode == 'filter') // Custom Filter List for otd_day
        {
            return $types;
        }

        return $frm->select('tclass_notify', $types, $curVal);
    }
    function otd_month($curVal, $mode) // not really necessary since we can use 'dropdown' - but just an example of a custom function.
    {
        $frm = e107::getForm();

        for ($i = 1; $i < 13; $i++)
        {
            $types[$i] = $i;
        }
        if ($mode == 'read')
        {
            return vartrue($types[$curVal]);
        }

        if ($mode == 'batch') // Custom Batch List for otd_day
        {
            return $types;
        }

        if ($mode == 'filter') // Custom Filter List for otd_day
        {
            return $types;
        }

        return $frm->select('otd_month', $types, $curVal);
    }

}


/*
* After initialization we'll be able to call dispatcher via e107::getAdminUI()
* so this is the first we should do on admin page.
* Global instance variable is not needed.
* NOTE: class is auto-loaded - see class2.php __autoload()
*/
/* $dispatcher = */

new plugin_timed_userclass_admin();

/*
* Uncomment the below only if you disable the auto observing above
* Example: $dispatcher = new plugin_timed_userclass_admin(null, null, false);
*/
//$dispatcher->runObservers(true);

require_once (e_ADMIN . "auth.php");

/*
* Send page content
*/
e107::getAdminUI()->runPage();

require_once (e_ADMIN . "footer.php");

/* OBSOLETE - see admin_shortcodes::sc_admin_menu()
* function admin_config_adminmenu() 
* {
* //global $rp;
* //$rp->show_options();
* e107::getRegistry('admin/timed_userclass_dispatcher')->renderMenu();
* }
*/

/* OBSOLETE - done within header.php
* function headerjs() // needed for the checkboxes - how can we remove the need to duplicate this code?
* {
* return e107::getAdminUI()->getHeader();
* }
*/

?>