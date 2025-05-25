<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pm = new plugin_manager(); 
$plugin_language = $pm->plugin_language("userlist", $plugin_path);
$tpl = new Template();

$config = mysqli_fetch_array(safe_query("SELECT selected_style FROM settings_headstyle_config WHERE id=1"));
$class = htmlspecialchars($config['selected_style']);

// Header-Daten
$data_array = [
    'class'    => $class,
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