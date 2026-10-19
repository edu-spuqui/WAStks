<?php
$ownerNameFile = '.owner/name.txt';
if (file_exists($ownerNameFile)) {
	$ownerName = trim(file_get_contents($ownerNameFile));
}
$tokenFile = '.owner/token.txt';
$isOwner = false;
if (file_exists($tokenFile)) {
    $secret = trim(file_get_contents($tokenFile));
    if (isset($_COOKIE['owner_auth']) && $_COOKIE['owner_auth'] === $secret) {
        $isOwner = true;
    }
}
echo'<div id=owner-login-content>';
	if ($isOwner) include 'owner-server.php';
	else include 'how-to-get.php';
echo'</div>';

if (filter_var(parse_url("http://".$_SERVER['HTTP_HOST'], PHP_URL_HOST), FILTER_VALIDATE_IP)) {
	$currentServerLink = 'http://'.$_SERVER['HTTP_HOST'];
} else {
	$currentServerLink = 'https://'.$_SERVER['HTTP_HOST'];
}

echo'<div class="server-title">';
	echo'<span style="display:inline-flex;align-items:center;">';
		if ($_SERVER['HTTP_HOST']==="127.0.0.1:8269")
			echo'<span class=serverTitleTop>🏠 <a href="'.$currentServerLink.'">This Device</a></span>';
		else
			echo'<span class=serverTitleTop>🏠 <a href="'.$currentServerLink.'">'.$_SERVER['HTTP_HOST'].'</a></span>';
	echo'</span>';
	echo'<span style="display:inline-flex;align-items:center;">';
	if (!$isOwner) {
		echo '<input id=owner-passwd type="password" autocomplete="off" style="text-align:center;" size=16 placeholder="PASSWORD" />';
		echo '<button id=owner-login-button title="Login" class="smallButton" onclick="ownerLogin()">▶</button>';
	} else {
		if (isset($ownerName))	echo '<span id="owner_name">'.$ownerName.'</span>';
		else					echo 'Owner Logged In';
		echo '<button title="Logout" class="smallButton" onclick="ownerLogout()">⏏</button>';
	}
	echo'</span>';
echo'</div>';

?>