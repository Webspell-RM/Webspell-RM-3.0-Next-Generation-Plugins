<?php
/**
 *¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯*
 *                  Webspell-RM      /                        /   /                                          *
 *                  -----------__---/__---__------__----__---/---/-----__---- _  _ -                         *
 *                   | /| /  /___) /   ) (_ `   /   ) /___) /   / __  /     /  /  /                          *
 *                  _|/_|/__(___ _(___/_(__)___/___/_(___ _/___/_____/_____/__/__/_                          *
 *                               Free Content / Management System                                            *
 *                                           /                                                               *
 *¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯*
 * @version         webspell-rm                                                                              *
 *                                                                                                           *
 * @copyright       2018-2025 by webspell-rm.de                                                              *
 * @support         For Support, Plugins, Templates and the Full Script visit webspell-rm.de                 *
 * @website         <https://www.webspell-rm.de>                                                             *
 * @forum           <https://www.webspell-rm.de/forum.html>                                                  *
 * @wiki            <https://www.webspell-rm.de/wiki.html>                                                   *
 *                                                                                                           *
 *¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯*
 * @license         Script runs under the GNU GENERAL PUBLIC LICENCE                                         *
 *                  It's NOT allowed to remove this copyright-tag                                            *
 *                  <http://www.fsf.org/licensing/licenses/gpl.html>                                         *
 *                                                                                                           *
 *¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯*
 * @author          Code based on WebSPELL Clanpackage (Michael Gruber - webspell.at)                        *
 * @copyright       2005-2011 by webspell.org / webspell.info                                                *
 *                                                                                                           *
 *¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯*
*/

$pm = new plugin_manager(); 
$plugin_language = $pm->plugin_language("userlist", $plugin_path);
$tpl = new Template();

$data_array = [
    'title' => $plugin_language['userlist_title'],
    'subtitle' => 'User online'
];

echo $tpl->loadTemplate("userlist", "head", $data_array, "plugin");

$settings = safe_query("SELECT * FROM plugins_userlist");
$ds = mysqli_fetch_array($settings);
$maxusers = $ds[ 'users_online' ];

$ergebnis = safe_query("SELECT w.*, u.username FROM whowasonline w LEFT JOIN users u ON u.userID = w.userID ORDER BY time DESC LIMIT 0 , ".$maxusers."");
	
echo $tpl->loadTemplate("userlist", "useronline_head", $data_array, "plugin");

$n=1;
while($ds=mysqli_fetch_array($ergebnis)) {	

	if(isonline($ds['userID'])=="offline") {
		$statuspic='<span class="badge bg-danger">OffLine</span> ';
        $timestamp = time();
        $time_now = date("d.m.Y - H:i",$timestamp);
        $time_lastlogin = date("d.m.Y - H:i", $ds['time']);
        $timestamp_lastlogin = $ds['time'];
		$diffzeit = $timestamp - $timestamp_lastlogin;
		$minuten = $diffzeit / 60;
		$minuten_rest = floor(($minuten - floor($minuten / 60) * 60));
		$stunden = floor($minuten / 60);
			if(	$stunden=="0"){
				$stunden='';
			}elseif(	$stunden=="1"){
				$stunden=$stunden.' '.$plugin_language['hour_and'].' ';
				$minuten_rest=str_pad($minuten_rest, 2, "0", STR_PAD_LEFT);
			}else {
				$stunden=$stunden.' '.$plugin_language['hours_and'].' ';
				$minuten_rest=str_pad($minuten_rest, 2, "0", STR_PAD_LEFT);
			}
			$last_active = ''.$plugin_language['was_online'].': '.$stunden.''.$minuten_rest.' '.$plugin_language['minutes'].'';
	}else {	
		$statuspic='<span class="badge bg-success">OnLine</span> ';	// Ausgabe Statuspic "Online"
		$last_active=''.$plugin_language['now_on'].'';
	}
	$username=''.$statuspic.' <a href="index.php?site=profile&amp;id='.$ds['userID'].'"><b>'.$ds['username'].'</b></a>';
	$ttID='sc_useronline_.'.$ds['userID'].'';				// erzeugt die ID für den Tooltip

	$data_array = [
	    'username' => $username,
	    'last_active' => $last_active
	];

    echo $tpl->loadTemplate("userlist", "useronline_content", $data_array, "plugin");
	$n++;
}
	echo $tpl->loadTemplate("userlist", "useronline_foot", $data_array, "plugin");
?>