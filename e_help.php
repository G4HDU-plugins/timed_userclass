<?php
/**
*  Help Plugin for the e107 Website System
*
* Copyright (C) 2008-2018 Barry Keal G4HDU (http://www.keal.me.uk)
* Released under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*
*/
if (!defined('LAN_HELP_TITLE'))
{
    define(LAN_HELP_TITLE, "Help");
    define(LAN_HELP_BUG, "Bugs");
    define(LAN_HELP_LINK, "For help with this plugin");
    define(LAN_HELP_BUGS, "To report a bug in this plugin");
    define(LAN_HELP_VERSIONTEXT, "A newer version is available");
    define(LAN_HELP_VERSION, "Github");

}
$helpObj = new eversion();
$helplink_text = $helpObj->runHelp();
$ns->tablerender(LAN_HELP_TITLE, $helplink_text, 'hduhelp');

/**
 * eversion
 * 
 * @package   
 * @author OnThisDay
 * @copyright Father Barry
 * @version 2018
 * @access public
 */
class eversion
{
    

    /**
     * eversion::__construct()
     * 
     * @return
     */
    function __construct()
    {
        // Get the folder name of the current plugin which should match the plugin name
        $this->plugname = basename(__dir__ );
        $this->name = "e107:plugins:" . $this->plugname;
        $this->thisDay = date('z');
        $this->lastRemoteCheck = e107::pref($this->plugname, 'lastRemoteCheck');

        $this->localVersion = e107::pref($this->plugname, 'localVersion');
        $this->remoteVersion = e107::pref($this->plugname, 'remoteVersion');
    }
    /**
     * eversion::runHelp()
     * 
     * @return
     */
    public function runHelp()
    {
        $this->getLocal();
        if ($this->thisDay != $this->lastRemoteCheck)
        {
            $this->getRemote();
        }
        $this->saveSettings();
        $helplink_text = "<div style='width=100%;margin:0 auto;text-align: center;' >";
        $helplink_text .= $this->buttonHelp();
        $helplink_text .= $this->buttonBugs();

        $this->result = version_compare($this->remoteVersion, $this->localVersion);
        if ($this->result === 1)
        {
            $helplink_text .= $this->buttonVersion();
        }
        $helplink_text .= "</div>";
        return $helplink_text;
    }
    /**
     * eversion::getRemote()
     * 
     * @return
     */
    private function getRemote()
    {
        $remoteFile = file_get_contents('https://raw.githubusercontent.com/G4HDU-plugins/' . $this->plugname . '/master/plugin.xml');
        $remotePosn = strpos($remoteFile, 'version="', 40) + 9;
        $remoteEnding = strpos($remoteFile, "\"", $remotePosn);
        $this->remoteVersion = substr($remoteFile, $remotePosn, $remoteEnding - $remotePosn);
    }
    /**
     * eversion::getLocal()
     * 
     * @return
     */
    private function getLocal()
    {
        $localFile = file_get_contents('plugin.xml');
        $localPosn = strpos($localFile, 'version="', 40) + 9;
        $localEnding = strpos($localFile, "\"", $localPosn);
        $this->localVersion = substr($localFile, $localPosn, $localEnding - $localPosn);
    }
    /**
     * eversion::saveSettings()
     * 
     * @return
     */
    private function saveSettings()
    {
        $settings = e107::getConfig($this->plugname);
        $settings->setPref('lastRemoteCheck', $this->thisDay);
        $settings->setPref('localVersion', $this->localVersion);
        $settings->setPref('remoteVersion', $this->remoteVersion);
        $settings->save(false, true, false);
    }
    /**
     * eversion::buttonHelp()
     * 
     * @return
     */
    private function buttonHelp()
    {
        $retval = LAN_HELP_LINK . "<br>
    <a href='http://manual.keal.me.uk/doku.php?id=e107:plugins:{$this->plugname}' id='HelpHelp' target='_blank'>                    
        <button type='button' class='btn btn-info' style='font-size:14px;color:white;'>
            <i class='fa fa-info' aria-hidden='true'></i> " . LAN_HELP_TITLE . "
        </button>
    </a>";
        return $retval;
    }
    /**
     * eversion::buttonBugs()
     * 
     * @return
     */
    private function buttonBugs()
    {
        $retval = "<br><br>" . LAN_HELP_BUGS . "<br>
    <a href='https://github.com/G4HDU-plugins/{$this->plugname}/issues' id='HelpBugs' target='_blank'>                    
        <button type='button' class='btn btn-info' style='font-size:14px;color:white;'>
       <i class='fa fa-bug' aria-hidden='true'></i> " . LAN_HELP_BUG . "
        </button>
    </a>";
        return $retval;
    }
    /**
     * eversion::buttonVersion()
     * 
     * @return
     */
    private function buttonVersion()
    {
        $retval = "<br><br>" . LAN_HELP_VERSIONTEXT . "<br>
    <a href='https://github.com/G4HDU-plugins/{$this->plugname}/tree/master' id='HelpVersion' target='_blank'>                    
        <button type='button' class='btn btn-info' style='font-size:14px;color:white;'>
       <i class='fa fa-download' aria-hidden='true'></i> " . LAN_HELP_VERSION . "
        </button>
    </a>";
        return $retval;
    }
}
