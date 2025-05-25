<?php

## SYSTEM #####################################################################################################################################



## NAVIGATION #####################################################################################################################################

safe_query("INSERT IGNORE INTO navigation_dashboard_links (linkID, catID, name, modulname, url, sort) VALUES
('', 3, '[[lang:de]]Letzte Anmeldung[[lang:en]]Last Login[[lang:it]]Ultimi Login', 'lastlogin', 'admincenter.php?site=admin_lastlogin', 2)");

#######################################################################################################################################

safe_query("
  INSERT IGNORE INTO user_role_admin_navi_rights (id, roleID, type, modulname, accessID)
  VALUES ('', 1, 'link', 'plugin_lastlogin', (
    SELECT linkID FROM navigation_dashboard_links WHERE modulname = 'plugin_lastlogin' LIMIT 1
  ))
");
 ?>