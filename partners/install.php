<?php
safe_query("CREATE TABLE IF NOT EXISTS plugins_partners (
  id int(11) NOT NULL AUTO_INCREMENT,
  name varchar(255) NOT NULL DEFAULT '',
  url varchar(255) NOT NULL DEFAULT '',
  banner varchar(255) NOT NULL DEFAULT '',
  info text NOT NULL,
  date int(14) NOT NULL DEFAULT '0',
  sort int(11) NOT NULL DEFAULT '0',
  displayed varchar(255) NOT NULL DEFAULT '1',
  hits int(11) DEFAULT '0',
  PRIMARY KEY (id)
) AUTO_INCREMENT=1
  DEFAULT CHARSET=utf8 DEFAULT COLLATE utf8_unicode_ci");

safe_query("INSERT IGNORE INTO plugins_partners (id, name, url, banner, info, date, sort, displayed) VALUES
(1, 'Partner 1', 'https://www.webspell-rm.de', '1.png', 'Hallo. Ich bin ein kleiner Blindtext. Und zwar schon so lange ich denken kann.', 1577612091, 1, '1'),
(2, 'Partner 2', 'https://www.webspell-rm.de', '2.png', 'Hallo. Ich bin ein kleiner Blindtext. Und zwar schon so lange ich denken kann.', 1577612226, 1, '1'),
(3, 'Partner 3', 'https://www.webspell-rm.de', '3.png', 'Hallo. Ich bin ein kleiner Blindtext. Und zwar schon so lange ich denken kann.', 1577612301, 1, '1'),
(4, 'Partner 4', 'https://www.webspell-rm.de', '4.png', 'Hallo. Ich bin ein kleiner Blindtext. Und zwar schon so lange ich denken kann.', 1577612446, 1, '1'),
(5, 'Partner 5', 'https://www.webspell-rm.de', '5.png', 'Hallo. Ich bin ein kleiner Blindtext. Und zwar schon so lange ich denken kann.', 1577613122, 1, '1'),
(6, 'Partner 6', 'https://www.webspell-rm.de', '6.png', 'Hallo. Ich bin ein kleiner Blindtext. Und zwar schon so lange ich denken kann.', 1577613187, 1, '1'),
(7, 'Partner 7', 'https://www.webspell-rm.de', '7.png', 'Hallo. Ich bin ein kleiner Blindtext. Und zwar schon so lange ich denken kann.', 1577613221, 1, '1')");



safe_query("CREATE TABLE IF NOT EXISTS plugins_partners_clicks (
  id int(11) NOT NULL AUTO_INCREMENT,
  partner_id int(11) NOT NULL,
  click_date date NOT NULL,
  clicks int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  UNIQUE KEY partner_id (partner_id, click_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

  
safe_query("CREATE TABLE IF NOT EXISTS plugins_partners_settings (
  partnerssetID int(11) NOT NULL AUTO_INCREMENT,
  partners int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (partnerssetID)
) AUTO_INCREMENT=2
  DEFAULT CHARSET=utf8 DEFAULT COLLATE utf8_unicode_ci");

safe_query("INSERT IGNORE INTO plugins_partners_settings (partnerssetID, partners) VALUES (1, 5)");

safe_query("CREATE TABLE IF NOT EXISTS plugins_partners_settings_widgets (
  id int(11) NOT NULL AUTO_INCREMENT,
  position varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  modulname varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  themes_modulname varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  widgetname varchar(255) NOT NULL DEFAULT '',
  widgetdatei varchar(255) NOT NULL DEFAULT '',
  activated int(1) DEFAULT 1,
  sort int(11) DEFAULT 1,
PRIMARY KEY (id)
) AUTO_INCREMENT=1
  DEFAULT CHARSET=utf8 DEFAULT COLLATE utf8_unicode_ci");

safe_query("INSERT IGNORE INTO plugins_partners_settings_widgets (id, position, modulname, themes_modulname, widgetname, widgetdatei, activated, sort) VALUES
('1', 'navigation_widget', 'navigation', 'default', 'Navigation', 'widget_navigation', 1, 1),
('2', 'footer_widget', 'footer_easy', 'default', 'Footer Easy', 'widget_footer_easy', 1, 1)");

## SYSTEM #####################################################################################################################################

safe_query("INSERT IGNORE INTO settings_plugins (pluginID, name, modulname, info, admin_file, activate, author, website, index_link, hiddenfiles, version, path, status_display, plugin_display, widget_display, delete_display, sidebar) VALUES
('', 'Partner', 'partners', '[[lang:de]]Mit diesem Plugin könnt ihr eure Partner mit Slider und Page anzeigen lassen.[[lang:en]]With this plugin you can display your partners with slider and page.[[lang:it]]Con questo plugin puoi visualizzare i tuoi partner con slider e pagina.', 'admin_partners', 1, 'T-Seven', 'https://webspell-rm.de', 'partners', '', '0.1', 'includes/plugins/partners/', 1, 1, 1, 1, 'deactivated')");

safe_query("INSERT IGNORE INTO settings_plugins_widget (id, modulname, widgetname, widgetdatei, area) VALUES
('', 'partners', 'Partners Content', 'widget_partners_content', 3),
('', 'partners', 'Partners Sidebar', 'widget_partners_sidebar', 4)");

## NAVIGATION #####################################################################################################################################

safe_query("INSERT IGNORE INTO navigation_dashboard_links (linkID, catID, name, modulname, url, sort) VALUES
('', 12, '[[lang:de]]Partner[[lang:en]]Partners[[lang:it]]Partner', 'partners', 'admincenter.php?site=admin_partners', 1)");


safe_query("INSERT IGNORE INTO navigation_website_sub (snavID, mnavID, name, modulname, url, sort, indropdown, themes_modulname) VALUES
('', 4, '[[lang:de]]Partner[[lang:en]]Partners[[lang:it]]Partner', 'partners', 'index.php?site=partners', 1, 1, 'default')");

#######################################################################################################################################

safe_query("
  INSERT IGNORE INTO user_role_admin_navi_rights (id, roleID, type, modulname, accessID)
  VALUES ('', 1, 'link', 'partners', (
    SELECT linkID FROM navigation_dashboard_links WHERE modulname = 'partners' LIMIT 1
  ))
");
 ?>