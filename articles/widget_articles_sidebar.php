<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

# Sprachdateien aus dem Plugin-Ordner laden
	$pm = new plugin_manager(); 
	$plugin_language = $pm->plugin_language("articles", $plugin_path);

	$tpl = new Template();
	$config = mysqli_fetch_array(safe_query("SELECT selected_style FROM settings_headstyle_config WHERE id=1"));
	$class = htmlspecialchars($config['selected_style']);

    // Header-Daten
    $data_array = [
        'class'    => $class,
        'title' => $plugin_language['title'],
        'subtitle' => 'About'
    ];
    
    echo $tpl->loadTemplate("articles", "head", $data_array, 'plugin');

$qry = safe_query("SELECT * FROM plugins_articles WHERE articleID!=0 ORDER BY articleID DESC LIMIT 0,5");
	$anz = mysqli_num_rows($qry);
	if($anz) {

	echo $tpl->loadTemplate("articles", "widget_articles_head", $data_array, 'plugin');

  $n=1;
	while($ds = mysqli_fetch_array($qry)) {
		$timestamp = strtotime($ds['date']);
        $tag = date("d", $timestamp);
        $monat = date("n", $timestamp);
        $year = date("Y", $timestamp);
        $monate = array(
            1 => $plugin_language['jan'], 2 => $plugin_language['feb'],
            3 => $plugin_language['mar'], 4 => $plugin_language['apr'],
            5 => $plugin_language['may'], 6 => $plugin_language['jun'],
            7 => $plugin_language['jul'], 8 => $plugin_language['aug'],
            9 => $plugin_language['sep'], 10 => $plugin_language['oct'],
            11 => $plugin_language['nov'], 12 => $plugin_language['dec']
        );

        $monatname = $monate[$monat];

		$question = $ds[ 'question' ];
		$question_lang = $ds[ 'question' ];
		$answer = $ds[ 'answer' ];
		$articleID = $ds['articleID'];
		

		$translate = new multiLanguage(detectCurrentLanguage());
    	$translate->detectLanguages($question);
    	$question = $translate->getTextByLanguage($question);
		
		$settings = safe_query("SELECT * FROM plugins_articles_settings");
        $dn = mysqli_fetch_array($settings);

        $maxarticleschars = 20;
        if(mb_strlen($question)>$maxarticleschars) {
            $question=mb_substr($question, 0, $maxarticleschars);
            $question.='...';
        }

        $maxarticleschars = $dn['articleschars'];
        #$maxblogchars = 110;
        if(mb_strlen($answer)>$maxarticleschars) {
            $answer=mb_substr($answer, 0, $maxarticleschars);
            $answer.='...';
        }

		

		$title = '<a href="index.php?site=articles&amp;action=watch&amp;articleID='.$articleID.'" data-toggle="tooltip" data-bs-html="true" title="
        '.$question_lang.'">'.$question.'</a>';

		$data_array = [
            'title' => $title,
	        'text' => $answer,
	        'tag'            => $tag,
            'monat'          => $monatname,
            'year'          => $year,
			'articleID' => $articleID,
		];

	
		echo $tpl->loadTemplate("articles", "widget_content", $data_array, 'plugin');
		$n++;
	}
		echo $tpl->loadTemplate("articles", "widget_articles_foot", $data_array, 'plugin');
}
else {
	echo $plugin_language[ 'no_articles' ];
}
?>
