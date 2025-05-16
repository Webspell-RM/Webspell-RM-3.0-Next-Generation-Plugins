<?php
# Sprachdateien aus dem Plugin-Ordner laden
$pm = new plugin_manager(); 
$plugin_language = $pm->plugin_language("clan_rules", $plugin_path);

use webspell\AccessControl;
// Den Admin-Zugriff für das Modul überprüfen
AccessControl::checkAdminAccess('clan_rules');



if (isset($_GET[ 'action' ])) {
    $action = $_GET[ 'action' ];
} else {
    $action = '';
}



// Initialisiere Captcha-Klasse
$CAPCLASS = new \webspell\Captcha;

// ADD-Formular anzeigen
if ($action == "add") {

    $CAPCLASS->createTransaction();
    $hash = $CAPCLASS->getHash();

    

    echo '<div class="card">
            <div class="card-header"><i class="bi bi-paragraph"></i> ' . $plugin_language['clan_rules'] . '</div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="admincenter.php?site=admin_clan_rules">' . $plugin_language['clan_rules'] . '</a></li>
                    <li class="breadcrumb-item active" aria-current="page">' . $plugin_language['add_clan_rules'] . '</li>
                </ol>
            </nav>
            <div class="card-body"><div class="container py-5">

            <form method="post" action="admincenter.php?site=admin_clan_rules" enctype="multipart/form-data" onsubmit="return chkFormular();">
                <div class="mb-3">
                    <label class="form-label">' . $plugin_language['clan_rules_name'] . '</label>
                    <input class="form-control" type="text" name="title" maxlength="255" />
                </div>
                <div class="mb-3">
                    <label class="form-label">' . $plugin_language['description'] . '</label>
                    <textarea class="ckeditor form-control" name="message" rows="10" style="width: 100%;"></textarea>
                </div>
                <div class="mb-3">
                    <label class="control-label">' . $plugin_language['is_displayed'] . '</label>
                    <input class="form-check-input" type="checkbox" name="displayed" value="1" checked />
                </div>
                <input type="hidden" name="captcha_hash" value="' . $hash . '" />
                <button class="btn btn-success btn-sm" type="submit" name="save">' . $plugin_language['add_clan_rules'] . '</button>
            </form>
            </div>
        </div></div>';
}

// EDIT-Formular anzeigen
elseif ($action == "edit" && isset($_GET["clan_rulesID"])) {

    $CAPCLASS->createTransaction();
    $hash = $CAPCLASS->getHash();



    $clan_rulesID = (int)$_GET["clan_rulesID"];
    $ds = mysqli_fetch_array(safe_query("SELECT * FROM plugins_clan_rules WHERE clan_rulesID='$clan_rulesID'"));

    $displayed = $ds['displayed'] == 1 ? 'checked' : '';

    echo '<div class="card">
            <div class="card-header"><i class="bi bi-paragraph"></i> ' . $plugin_language['clan_rules'] . '</div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="admincenter.php?site=admin_clan_rules">' . $plugin_language['clan_rules'] . '</a></li>
                    <li class="breadcrumb-item active" aria-current="page">' . $plugin_language['edit_clan_rules'] . '</li>
                </ol>
            </nav>
            <div class="card-body"><div class="container py-5">

            <form method="post" action="admincenter.php?site=admin_clan_rules" enctype="multipart/form-data" onsubmit="return chkFormular();">
                <div class="mb-3">
                    <label class="form-label">' . $plugin_language['clan_rules_name'] . '</label>
                    <input class="form-control" type="text" name="title" maxlength="255" value="' . htmlspecialchars($ds['title']) . '" />
                </div>
                <div class="mb-3">
                    <label class="form-label">' . $plugin_language['description'] . '</label>
                    <textarea class="ckeditor form-control" name="message" rows="10">' . htmlspecialchars($ds[ 'text' ]) . '</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">' . $plugin_language['is_displayed'] . '</label>
                    <input class="form-check-input" type="checkbox" name="displayed" value="1" ' . $displayed . ' />
                </div>
                <input type="hidden" name="captcha_hash" value="' . $hash . '" />
                <input type="hidden" name="clan_rulesID" value="' . $clan_rulesID . '" />
                <button class="btn btn-warning btn-sm" type="submit" name="saveedit">' . $plugin_language['edit_clan_rules'] . '</button>
            </form>
            </div>
        </div></div>';
}

// Speichern: Neue Regel
elseif (isset($_POST["save"])) {

    $title = $_POST["title"];
    $text = $_POST["message"];
    $displayed = isset($_POST["displayed"]) ? 1 : 0;
    $date = date("Y-m-d H:i:s", time());

    if ($CAPCLASS->checkCaptcha(0, $_POST["captcha_hash"])) {

        // Eintrag erstellen
        safe_query("INSERT INTO plugins_clan_rules (title, text, date, poster, displayed, sort)
                    VALUES ('" . $title . "', '" . $text . "', '" . $date . "', '" . $userID . "', '$displayed', '1')");

/////////////////////////////////////////////////////////////
 /*       // Neue ID holen
        $new_ruleID = $_database->insert_id;

        // Logging-Daten vorbereiten
        $new_data = json_encode([
            'title' => $title,
            'text' => $text,
            'displayed' => $displayed
        ]);

        // Admin-Log schreiben
        write_admin_log(
            $userID,
            'Erstellen',
            'Clan Rules',
            $new_ruleID,
            null,
            $new_data,
            $_SERVER['REMOTE_ADDR'],
            time(),
            'plugins_clan_rules'
        );*/
/////////////////////////////////////////////////////////////

        redirect("admincenter.php?site=admin_clan_rules", "", 0);
    } else {
        echo $_language->module['transaction_invalid'];
    }
}



// Speichern: Bearbeitung
if (isset($_POST["saveedit"]) && $CAPCLASS->checkCaptcha(0, $_POST["captcha_hash"])) {

    $clan_rulesID = (int)$_POST["clan_rulesID"];
    $title = $_POST["title"] ?? '';
    $text = $_POST["message"] ?? '';
    $displayed = isset($_POST["displayed"]) ? 1 : 0;
    $date = date("Y-m-d H:i:s");  // Für DATETIME-Spalte
    $poster = $userID;

    // Speichern mit safe_query (nicht ändern)
    safe_query(
        "UPDATE plugins_clan_rules 
         SET title='" . $title . "',
             text='" . $text . "',
             date='" . $date . "',
             poster='" . (int)$poster . "',
             displayed='" . (int)$displayed . "' 
         WHERE clan_rulesID='" . (int)$clan_rulesID . "'"
    );

    // Logging (separat, korrekt nach safe_query)
    AdminLogger::updateWithLog(
        'plugins_clan_rules',
        'clan_rulesID',
        $clan_rulesID,
        ['title' => $title, 'text' => $text],
        'Bearbeiten',
        'Clanrules',
        $userID
    );

    redirect("admincenter.php?site=admin_clan_rules", "", 3);
}














// Sortierung
elseif (isset($_POST['sortieren'])) {

    if ($CAPCLASS->checkCaptcha(0, $_POST["captcha_hash"])) {

        foreach ($_POST['sort'] as $sortstring) {
            [$id, $sortval] = explode("-", $sortstring);
            safe_query("UPDATE plugins_clan_rules SET sort='$sortval' WHERE clan_rulesID='$id'");
        }

        redirect("admincenter.php?site=admin_clan_rules", "", 0);
    } else {
        echo $plugin_language['transaction_invalid'];
    }
}

// Löschen
elseif (isset($_GET["delete"], $_GET["captcha_hash"], $_GET["clan_rulesID"])) {

    if ($CAPCLASS->checkCaptcha(0, $_GET["captcha_hash"])) {
        $clan_rulesID = (int)$_GET["clan_rulesID"];

        if (safe_query("DELETE FROM plugins_clan_rules WHERE clan_rulesID='$clan_rulesID'")) {
            redirect("admincenter.php?site=admin_clan_rules", "", 0);
        } else {
            echo "Fehler beim Löschen!";
        }
    } else {
        echo $_language->module['transaction_invalid'];
    }
} elseif (isset($_POST[ 'clan_rules_settings_save' ])) {  

   
    $CAPCLASS = new \webspell\Captcha;
    if ($CAPCLASS->checkCaptcha(0, $_POST[ 'captcha_hash' ])) {
        safe_query(
            "UPDATE
                plugins_clan_rules_settings
            SET
                
                clan_rules='" . $_POST[ 'clan_rules' ] . "' "
        );
        
        redirect("admincenter.php?site=admin_clan_rules&action=admin_clan_rules_settings", "", 0);
    } else {
        redirect("admincenter.php?site=admin_clan_rules&action=admin_clan_rules_settings", $plugin_language[ 'transaction_invalid' ], 3);
    }
}  








if ($action == "admin_clan_rules_settings") {

 
    $settings = safe_query("SELECT * FROM plugins_clan_rules_settings");
    $ds = mysqli_fetch_array($settings);

    
  $maxshownclan_rules = $ds[ 'clan_rules' ];
if (empty($maxshownclan_rules)) {
    $maxshownclan_rules = 10;
}


    

    

    $CAPCLASS = new \webspell\Captcha;
    $CAPCLASS->createTransaction();
    $hash = $CAPCLASS->getHash();
    
echo'    <form method="post" action="admincenter.php?site=admin_clan_rules&action=admin_clan_rules_settings">
        <div class="card">
            <div class="card-header">
                '.$plugin_language[ 'settings' ].'
            </div>

            <div class="card-body">


            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="admincenter.php?site=admin_clan_rules">' . $plugin_language[ 'clan_rules' ] . '</a></li>
                <li class="breadcrumb-item active" aria-current="page">' . $plugin_language[ 'settings' ] . '</li>
                </ol>
            </nav>  

            <div class="container py-5">    
                <div class="row">
                    <div class="col-md-6">
                        

                        
                            <div class="col-md-6">
                                '.$plugin_language['max_clan_rules'].':
                            </div>

                            <div class="col-md-6">
                                <span class="pull-right text-muted small"><em data-toggle="tooltip" title="'.$plugin_language[ 'tooltip' ].'"><input class="form-control" type="text" name="clan_rules" value="'.$ds['clan_rules'].'" size="35"></em></span>
                            </div>
                       

                        

                        
                    </div>

                    <div class="col-md-6">
                        
                    </div>
               </div>
                <br>
 <div class="form-group">
<input type="hidden" name="captcha_hash" value="'.$hash.'"> 
<button class="btn btn-primary btn-sm" type="submit" name="clan_rules_settings_save">'.$plugin_language['update'].'</button>
</div>

        

 </div>
            </div></div>
       
        
    </form>';

} elseif ($action == "") {


echo'<div class="card">
            <div class="card-header">
                            <i class="bi bi-paragraph"></i> ' . $plugin_language[ 'title' ] . '</div>

            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="admincenter.php?site=admin_clan_rules">' . $plugin_language[ 'clan_rules' ] . '</a></li>
                <li class="breadcrumb-item active" aria-current="page">New / Edit</li>
                </ol>
            </nav>  
                        <div class="card-body">

<div class="form-group row">
    <label class="col-md-1 control-label">' . $plugin_language['options'] . ':</label>
    <div class="col-md-8">
      <a href="admincenter.php?site=admin_clan_rules&amp;action=add" class="btn btn-primary btn-sm" type="button">' . $plugin_language[ 'new_clan_rules' ] . '</a>      
      <a href="admincenter.php?site=admin_clan_rules&action=admin_clan_rules_settings" class="btn btn-primary btn-sm" type="button">' . $plugin_language[ 'settings' ] . '</a>
    </div>
  </div>

<div class="container py-5">
<div class="table-responsive">
    
    <form method="post" action="admincenter.php?site=admin_clan_rules">

<table class="table table-bordered table-striped bg-white shadow-sm">
        <thead class="table-light">
        <tr>

            <th width="29%"><b>' . $plugin_language[ 'clan_rules' ] . '</b></th>
            <th width="15%"><b>' . $plugin_language[ 'is_displayed' ] . '</b></th>
            <th width="20%"><b>' . $plugin_language[ 'actions' ] . '</b></th>
            <th width="8%"><b>' . $plugin_language[ 'sort' ] . '</b></th>
            
        </tr>
        </thead>
        <tbody>
   
       
        ';

    $CAPCLASS = new \webspell\Captcha;
    $CAPCLASS->createTransaction();
    $hash = $CAPCLASS->getHash();

    $qry = safe_query("SELECT * FROM plugins_clan_rules ORDER BY sort");
    $anz = mysqli_num_rows($qry);
    if ($anz) {
        $i = 1;
        while ($ds = mysqli_fetch_array($qry)) {
            if ($i % 2) {
                $td = 'td1';
            } else {
                $td = 'td2';
            }

            $ds[ 'displayed' ] == 1 ?
            $displayed = '<font color="green"><b>' . $plugin_language[ 'yes' ] . '</b></font>' :
            $displayed = '<font color="red"><b>' . $plugin_language[ 'no' ] . '</b></font>';
            

            
                $title = htmlspecialchars($ds[ 'title' ]);
           
                $title = htmlspecialchars($ds[ 'title' ]);
           
            $title = $ds[ 'title' ];
    
            $translate = new multiLanguage(detectCurrentLanguage());
            $translate->detectLanguages($title);
            $title = $translate->getTextByLanguage($title);
            
            echo '<tr>
            <td width="29%" class="' . $td . '">' . $title . '</td>
            
            <td width="15%" class="' . $td . '">' . $displayed . '</td>
            
            <td width="20%" class="' . $td . '" >

            <a class="btn btn-warning btn-sm" href="admincenter.php?site=admin_clan_rules&amp;action=edit&amp;clan_rulesID=' . $ds[ 'clan_rulesID' ] .
                '" class="input">' . $plugin_language[ 'edit' ] . '</a>
                
                    <!-- Button trigger modal -->
    <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#confirm-delete" data-href="admincenter.php?site=admin_clan_rules&amp;delete=true&amp;clan_rulesID='.$ds['clan_rulesID'].'&amp;captcha_hash='.$hash.'">
    ' . $plugin_language['delete'] . '
    </button>
    <!-- Button trigger modal END-->

     <!-- Modal -->
<div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">' . $plugin_language[ 'clan_rules' ] . '</h5>
        <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal" aria-label="' . $plugin_language[ 'close' ] . '"></button>
      </div>
      <div class="modal-body"><p>' . $plugin_language['really_delete'] . '</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">' . $plugin_language[ 'close' ] . '</button>
        <a class="btn btn-danger btn-ok btn-sm">' . $plugin_language['delete'] . '</a>
      </div>
    </div>
  </div>
</div>
<!-- Modal END -->      
                           
                    </td>
				<td width="8%" class="' . $td . '" align="center"><select name="sort[]">';
            for ($j = 1; $j <= $anz; $j++) {
                if ($ds[ 'sort' ] == $j) {
                    echo '<option value="' . $ds[ 'clan_rulesID' ] . '-' . $j . '" selected="selected">' . $j .
                        '</option>';
                } else {
                    echo '<option value="' . $ds[ 'clan_rulesID' ] . '-' . $j . '">' . $j . '</option>';
                }
            }
            echo '</select>
</td>
</tr>';
            $i++;
        }
        
    } else {
        echo '<tr><td class="td1" colspan="6">' . $plugin_language[ 'no_entries' ] . '</td></tr>';
    }

    echo '<tr>
<td class="td_head" colspan="6" align="right"><input type="hidden" name="captcha_hash" value="' . $hash .
    '"><br><input class="btn btn-success btn-sm" type="submit" name="sortieren" value="' . $plugin_language[ 'to_sort' ] . '" /></td>
</tr>
</tbody></table>
</form></div></div></div>';
}
