<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pm = new plugin_manager(); 
$plugin_language = $pm->plugin_language("userlist", $plugin_path);

$config = mysqli_fetch_array(safe_query("SELECT selected_style FROM settings_headstyle_config WHERE id=1"));
$class = htmlspecialchars($config['selected_style']);

// Header-Daten
$data_array = [
    'class'    => $class,
    'title' => $plugin_language['registered_users'],
    'subtitle' => 'Userlist'
];
echo $tpl->loadTemplate("userlist", "head", $data_array, "plugin");

function clear($text)
{
    return str_replace("javascript:", "", strip_tags($text));
}

$alle = safe_query("SELECT userID FROM users");
$gesamt = mysqli_num_rows($alle);
$pages = 1;

$settings = safe_query("SELECT * FROM plugins_userlist");
$ds = mysqli_fetch_array($settings);

$maxusers = $ds['users_list'] ?: 10;

for ($n = $maxusers; $n <= $gesamt; $n += $maxusers) {
    if ($gesamt > $n) $pages++;
}

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$sort = in_array($_GET['sort'] ?? '', ['username', 'lastlogin', 'registerdate', 'homepage']) ? $_GET['sort'] : 'username';
$type = ($_GET['type'] ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';

$page_link = $pages > 1 ? makepagelink("index.php?site=userlist&amp;sort=$sort&amp;type=$type", $page, $pages) : '';

$start = ($page - 1) * $maxusers;
$ergebnis = safe_query("SELECT * FROM users ORDER BY $sort $type LIMIT $start, $maxusers");
$n = ($type === "DESC") ? $gesamt - $start : $start + 1;

if (mysqli_num_rows($ergebnis)) {
    $sorter = '<a href="index.php?site=userlist&amp;page=' . $page . '&amp;sort=' . $sort . '&amp;type=' . ($type === 'ASC' ? 'DESC' : 'ASC') . '">' . $plugin_language['sort'] . '</a>';
    $sorter .= $type === 'ASC' ? ' <i class="bi bi-arrow-down"></i>' : ' <i class="bi bi-arrow-up"></i>';

    $data_array = [
        'page_link' => $page_link,
        'gesamt' => $gesamt,
        'page' => $page,
        'sorter' => $sorter,
        'registered_users' => $plugin_language['registered_users'],
        'username' => $plugin_language['username'],
        'contact' => $plugin_language['contact'],
        'homepage' => $plugin_language['homepage'],
        'last_login' => $plugin_language['last_login'],
        'registration' => $plugin_language['registration']
    ];

    echo $tpl->loadTemplate("userlist", "header", $data_array, "plugin");

    while ($ds = mysqli_fetch_array($ergebnis)) {
        $id = $ds['userID'];
        $username = '<a href="index.php?site=profile&amp;id=' . $id . '">' . getusername($id) . '</a>';

        $dx = mysqli_fetch_array(safe_query("SELECT * FROM settings_plugins WHERE modulname='squads'"));
        $member = (@$dx['modulname'] === 'squads' && isclanmember($id)) ? ' <i class="bi bi-person" style="color: #5cb85c"></i>' : '';

        $email = $ds['email_hide']
            ? '<span class=""><i class="bi bi-envelope-slash"> email</i></span>'
            : '<a href="mailto:' . htmlspecialchars(mail_protect($ds['email'])) . '"><i class="bi bi-envelope"></i> email</a>';

        if ($ds['homepage']) {
            $protocol = stristr($ds['homepage'], "https://") ? '' : 'http://';
            $homepage = '<a href="' . $protocol . htmlspecialchars($ds['homepage']) . '" target="_blank" rel="nofollow"><i class="bi bi-house" style="font-size:18px;"></i> ' . $plugin_language['homepage'] . '</a>';
        } else {
            $homepage = '<i class="bi bi-house-slash" style="font-size:18px;"></i><i> ' . $plugin_language['homepage'] . '</i>';
        }

        $pm = ($loggedin && $id != $userID)
            ? ' / <a href="index.php?site=messenger&amp;action=touser&amp;touser=' . htmlspecialchars($id, ENT_QUOTES, 'UTF-8') . '"><i class="bi bi-messenger"></i> ' . $plugin_language['message'] . '</a>'
            : ' / <i class="bi bi-slash-circle"> ' . $plugin_language['message'] . '</i>';

        $status = isonline($id);
        $lastlogin = $ds['lastlogin'];

        if ($status === "offline") {
            $login = ($lastlogin === '1970-01-01 00:00:00' || $lastlogin === $ds['registerdate'])
                ? $plugin_language['n_a']
                : date("d.m.Y - H:i", strtotime($lastlogin));
        } else {
            $login = '<span class="badge bg-success">online</span> ' . $plugin_language['now_on'];
        }

        $avatar = ($getavatar = getavatar($id)) ? '<img class="img-fluid avatar_small" src="./images/avatars/' . htmlspecialchars($getavatar) . '">' : '';

        $date = strtotime(date("Y-m-d"));
        $reg_date = strtotime($ds['registerdate']);
        $difference = floor(($reg_date - $date) / (60 * 60 * 24));

        if ($difference == 0) {
            $register = $plugin_language['today'];
        } elseif ($difference > 1) {
            $register = $plugin_language['future_date'];
        } elseif ($difference == 1) {
            $register = $plugin_language['tomorrow'];
        } elseif ($difference == -1) {
            $register = $plugin_language['yesterday'];
        } else {
            $register = date("d.m.Y", $reg_date);
        }

        $data_array = [
            'username' => $username,
            'avatar' => $avatar,
            'member' => $member,
            'homepage' => $homepage,
            'email' => $email,
            'pm' => $pm,
            'login' => $login,
            'register' => $register
        ];

        echo $tpl->loadTemplate("userlist", "user_data", $data_array, "plugin");
    }

    echo $tpl->loadTemplate("userlist", "footer", ['page_link' => $page_link], "plugin");

} else {
    echo '<i>' . htmlspecialchars($plugin_language['no_users_found']) . '</i>';
}
