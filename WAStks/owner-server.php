<?php

echo'<div id=owner-server-content>';

	if (!is_dir('Favorites'))	mkdir('Favorites', 0777);
	if (!is_dir('Packs'))		mkdir('Packs', 0777);
	if (!is_dir('PrivatePacks'))mkdir('PrivatePacks', 0777);
	if (!is_dir('Workshop'))	mkdir('Workshop', 0777);
	if (!is_dir('Logs'))		mkdir('Logs', 0777);
	if (!is_dir('Temp'))		mkdir('Temp', 0777);
	if (!is_dir('Temp/png'))	mkdir('Temp/png', 0777);
	if (!is_dir('Temp/webp'))	mkdir('Temp/webp', 0777);

	if (php_uname('s') == 'Windows NT')		$foldsep = "\\";
	else									$foldsep = "/";
	require 'qrcode.php';
	/*	Functions	*/
	function genQRcode($link) {
		$generator = new QRCode($link);
		$reflector = new ReflectionClass('QRCode');
		$method = $reflector->getMethod('qr_encode');
		$result = $method->invoke($generator, $link, 0);
		
		$matrix = $result['b']; 
		$size = count($matrix);
		
		$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 '.$size.' '.$size.'" shape-rendering="crispEdges" style="">';
		$svg .= '<path fill="currentColor" d="';
		foreach ($matrix as $y => $row) {
			foreach ($row as $x => $val) {
				if ($val) $svg .= "M{$x},{$y}h1v1h-1z ";
			}
		}
		$svg .= '"/></svg>';
		
		return $svg;
	}

	if (!function_exists('cdate_asc')) {function cdate_asc($element1, $element2)	{ return filectime($element1) <=> filectime($element2);	}	}
	if (!function_exists('cdate_desc')) {function cdate_desc($element1, $element2)	{ return filectime($element2) <=> filectime($element1);	}	}
	if (!function_exists('mdate_asc')) {function mdate_asc($element1, $element2)	{ return filemtime($element1) <=> filemtime($element2);	}	}
	if (!function_exists('mdate_desc')) {function mdate_desc($element1, $element2)	{ return filemtime($element2) <=> filemtime($element1);	}	}

	function getAllFolders() {
		if (is_dir('Favorites')) $allFolders['Favorites'] = count(glob('Favorites/*.fav'));
		if (is_dir('Packs')) {
			$stickerPacks = glob("Packs/*", GLOB_ONLYDIR);
			if ($stickerPacks) {
				$allFolders['Packs'] = count($stickerPacks);
				rsort($stickerPacks);
				foreach($stickerPacks as $first) {
					$count = count(glob($first.'/*.webp'));
					if (file_exists($first.'/_tray.webp')) $count--;
					$allFolders[$first] = $count;
				}
			}
		}
		if (is_dir('PrivatePacks')) {
			$stickerPacks = glob("PrivatePacks/*", GLOB_ONLYDIR);
			if ($stickerPacks) {
				$allFolders['PrivatePacks'] = count($stickerPacks);
				rsort($stickerPacks);
				foreach($stickerPacks as $first) {
					$count = count(glob($first.'/*.webp'));
					if (file_exists($first.'/_tray.webp')) $count--;
					$allFolders[$first] = $count;
				}
			}
		}
		if (is_dir('WA-Media/WhatsApp Stickers')) {
			$folderStickers = glob('WA-Media/WhatsApp Stickers/*.webp');
			$count = count($folderStickers);
			if ($count) $allFolders['WA-Media/WhatsApp Stickers'] = $count;
		}
		if (is_dir('ComfyUI')) $allFolders['ComfyUI'] = count(glob('ComfyUI/*.webp'));
		if (is_dir('User-Media/Stickers')) {
			$folderStickers = glob('User-Media/Stickers/*.webp');
			if (count($folderStickers)) $allFolders['User-Media/Stickers'] = count($folderStickers);
			$stickerFolders = glob("User-Media/Stickers/*", GLOB_ONLYDIR);
			if ($stickerFolders) {
				rsort($stickerFolders);
				foreach($stickerFolders as $first) {
					$subFolderStickers = glob($first.'/*.webp');
					if (count($subFolderStickers)) $allFolders[$first] = count($subFolderStickers);
					$stickerSubFolders = glob($first."/*", GLOB_ONLYDIR);
					if ($stickerSubFolders) {
						rsort($stickerSubFolders);
						foreach($stickerSubFolders as $second) {
							$subFolder2Stickers = glob($second.'/*.webp');
							if (count($subFolder2Stickers)) $allFolders[$second] = count($subFolder2Stickers);
						}
					}
				}
			}
		}
		return $allFolders;
	}
	function getAllStickers() {
		$allStickers = [];
		if (!isset($allFolders)) $allFolders = getAllFolders();
		if (isset($allFolders)) {
			foreach($allFolders as $folder => $count) {
				$folderStickers = glob($folder.'/*.webp');
				if ($folderStickers) {
					if (str_starts_with($folder,'Packs')) {
						// sort by modified date descending
						usort($folderStickers,'mdate_desc');
					} else if (str_starts_with($folder,'PrivatePacks')) {
						// sort by modified date descending
						usort($folderStickers,'mdate_desc');
					} else if (str_starts_with($folder,'WA-Media/WhatsApp Stickers/')) {
						// sort by name descending == date descending
						rsort($folderStickers);
					} else if (str_starts_with($folder,'User-Media/Stickers/_Compare')) {
						// sort by name ascending
						sort($folderStickers);
					} else if (str_starts_with($folder,'User-Media/Stickers/_Improved')) {
						// sort by name ascending
						sort($folderStickers);
					} else if (str_starts_with($folder,'User-Media/Stickers/')) {
						// sort by modified date descending
						usort($folderStickers,'mdate_desc');
					} else {
						// sort by modified date descending
						usort($folderStickers,'mdate_desc');
					}
					foreach($folderStickers as $stickerfile) {
						$allStickers[] = $stickerfile;
					}
					// foreach($folderStickers as $i => $stickerfile) { $allStickers[] = $stickerfile; if($i>30) break; }
				}
			}
		}
		return $allStickers;
	}
	function enrichStickerData($allStickers) {
		$enriched = [];
		
		$jsonPath = 'metadata.json';
		$metadata = file_exists($jsonPath) ? json_decode(file_get_contents($jsonPath), true) : [];

		foreach ($allStickers as $i => $stk) {
			$info = pathinfo($stk);
			$meta = $metadata[$info['filename']] ?? [];
			
			// Normalize Name and Publisher
			$rawName = $meta['sticker-pack-name'] ?? '';
			$rawPub  = $meta['sticker-pack-publisher'] ?? '';

			$isUnknown = ($rawName === '' || $rawName === 'Unknown' || $rawPub === '' || $rawPub === 'Unknown');

			$stickerData = [
				'id'						=> $i,
				'filename'					=> $info['filename'],
				'dirname'					=> $info['dirname'],
				'sticker-pack-name'			=> $isUnknown ? 'Unknown' : $rawName,
				'sticker-pack-publisher'	=> $isUnknown ? 'Unknown' : $rawPub,
				'emojis'					=> $meta['emojis'] ?? [],
				'audioFile'					=> null,
				'isFavorite'				=> false
			];

			// Gets audio file, if there is one
			if ( (str_starts_with($info['filename'],'STK-')) && (strlen($info['filename'])==19) ) {
				$stkdate = substr($info['filename'],4,8);
				$stkindex = substr($info['filename'],-4,4);
				$audindex = sprintf("%04d", intval($stkindex) + 1);
				$audfile = 'WA-Media/WhatsApp Audio/AUD-'.$stkdate.'-WA'.$audindex.'.aac';
				if (file_exists($audfile)) $stickerData['audioFile'] = $audfile;
				else {
					$audfilesent = 'WA-Media/WhatsApp Audio/Sent/AUD-'.$stkdate.'-WA'.$audindex.'.aac';
					if (file_exists($audfilesent)) $stickerData['audioFile'] = $audfilesent;
				}
			} else {
				if (file_exists($info['dirname'].'/'.$info['filename'].'.aac'))
					$stickerData['audioFile'] = $info['dirname'].'/'.$info['filename'].'.aac';
			}

			// Favorites
			if (file_exists('Favorites/'.$info['filename'].'.fav'))	{
				$realfavfile = file_get_contents('Favorites/'.$info['filename'].'.fav');
				if ($info['dirname'].'/'.$info['filename'].'.webp'==$realfavfile) {
					$stickerData['isFavorite'] = true;
				}
			}
			
			$enriched[] = $stickerData;
		}
		
		return $enriched;
	}
	function saveManifest($hostname, $allStickers, $allFolders, $manifestPath) {
		global $foldsep;
		$data = [
			'hostname'    => $hostname,
			'generated'   => date("Y-m-d H:i:s"),
			'separator'   => $foldsep,
			'allFolders'  => $allFolders,
			'allStickers' => $allStickers
		];

		$json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR | JSON_PRETTY_PRINT);
		if ($json) {
			file_put_contents($manifestPath.'.tmp', $json);
			rename($manifestPath.'.tmp', $manifestPath);
		}
	}
	function getZrokState($alias) {
		if (php_uname('s') == 'Windows NT') {
			// --- Your existing Windows Logic ---
			$psCommand = "powershell -Command \"Get-CimInstance Win32_Process -Filter \\\"Name = 'zrok.exe'\\\" | Where-Object { \$_.CommandLine -like '*$alias*' } | Select-Object ProcessId\"";
			$psOutput = shell_exec($psCommand) ?? '';
			if (preg_match('/\d+/', $psOutput)) return 'running';
		} else {
			// Linux / Termux
			$check = shell_exec("pgrep -f \"[z]rok share reserved $alias\" 2>&1");
			if (trim((string)$check) !== '') return 'running';
		}
		
		// 2. Check zrok overview
		$overview_json = shell_exec("zrok overview") ?? '{}';
		$data = json_decode($overview_json, true);

		if (isset($data['environments']) && is_array($data['environments'])) {
			foreach ($data['environments'] as $env) {
				if (isset($env['shares']) && is_array($env['shares'])) {
					foreach ($env['shares'] as $share) {
						if ($share['shareToken'] === $alias) {
							return 'reserved';
						}
					}
				}
			}
		}

		return 'idle';
	}

	if (isset($_COOKIE['privateVisibility'])) 	$privateVisibility = $_COOKIE['privateVisibility'];
	else										$privateVisibility = 'show';
	$ownerNameFile = '.owner/name.txt';
	if (file_exists($ownerNameFile)) 			$ownerName = trim(file_get_contents($ownerNameFile));
	$deviceNameFile = '.owner/device.txt';
	if (file_exists($deviceNameFile)) 			$deviceName = trim(file_get_contents($deviceNameFile));
	else										$deviceName = gethostname();
	$hostname = $ownerName.$deviceName.'WAStks';
	$publisherFile = '.owner/publisher.txt';
	if (file_exists($publisherFile)) 			$publisher = trim(file_get_contents($publisherFile));
	else										$publisher = $ownerName.$deviceName;

	if (!file_exists('owner-manifest.json')) {
		$allFolders = [];
		$allStickers = [];

		$allFolders = getAllFolders();
		$rawStickers = getAllStickers();
		$allStickers = enrichStickerData($rawStickers);
		
		saveManifest($hostname, $allStickers, $allFolders, 'owner-manifest.json');

		$publicFolders = [];
		$publicStickers = [];

		foreach($allFolders as $folderpath => $count) {
			$foldername = basename($folderpath);
			if ( !str_starts_with($foldername, '_') && !str_starts_with($folderpath, 'Private') ) {
				$publicFolders[$folderpath] = $count;
			}
		}
		foreach($allStickers as $stk) {
			$folderpath = $stk['dirname'];
			$foldername = basename($folderpath);
			if ( !str_starts_with($foldername, '_') && !str_starts_with($folderpath, 'Private') ) {
				$publicStickers[] = $stk;
			}
		}
		
		saveManifest($hostname, $publicStickers, $publicFolders, 'manifest.json');
		
		echo '<div class="serverMsgDiv" style="display:none;">';
			echo '<img width="40px" src="assets/ghost_1f47b_Spuqui.webp">';
			echo '<div class="serverMsg">';
				echo '<div class="timestamp">'.date("H:i:s").' </div>';
				echo 'Manifest created!';
			echo '</div>';
		echo '</div>'.PHP_EOL;
	} else {
		$manifestData = json_decode(file_get_contents('owner-manifest.json'), true);
		$allFolders = $manifestData['allFolders'] ?? [];
		$allStickers = $manifestData['allStickers'] ?? [];
		$publicManifestData = json_decode(file_get_contents('manifest.json'), true);
		$publicFolders = $publicManifestData['allFolders'] ?? [];
		$publicStickers = $publicManifestData['allStickers'] ?? [];
	}
	if ( (isset($_POST['Refresh']))&&( $_POST['Refresh']=='Manifest' || $_POST['Refresh']=='Stickers' ) ) {
		$reqID = $_REQUEST['reqID'];
		
		$allFolders = [];
		$allStickers = [];
		$publicFolders = [];
		$publicStickers = [];

		$allFolders = getAllFolders();
		$rawStickers = getAllStickers();
		$allStickers = enrichStickerData($rawStickers);

		saveManifest($hostname, $allStickers, $allFolders, 'owner-manifest.json');

		foreach($allFolders as $folderpath => $count) {
			$foldername = basename($folderpath);
			if ( !str_starts_with($foldername, '_') && !str_starts_with($folderpath, 'Private') ) {
				$publicFolders[$folderpath] = $count;
			}
		}
		foreach($allStickers as $stk) {
			$folderpath = $stk['dirname'];
			$foldername = basename($folderpath);
			if ( !str_starts_with($foldername, '_') && !str_starts_with($folderpath, 'Private') ) {
				$publicStickers[] = $stk;
			}
		}

		saveManifest($hostname, $publicStickers, $publicFolders, 'manifest.json');
		
		echo '<div class="serverMsgDiv" style="display:none;" id="'.$reqID.'">';
			echo '<img width="40px" src="assets/ghost_1f47b_Spuqui.webp">';
			echo '<div class="serverMsg">';
				echo '<div class="timestamp">'.date("H:i:s").' </div>';
				echo '<span title="Success" class="reply up">👍</span> Manifest updated!';
			echo '</div>';
		echo '</div>'.PHP_EOL;
	}

	if (php_uname('s') == 'Windows NT')	{	$ipconfig = shell_exec('ipconfig');
		if ($ipconfig) {
			$ipconfigArray = explode("\n", $ipconfig);
			foreach($ipconfigArray as $line) {
				$pair = explode(" : ", $line);
				if (str_contains($pair[0], 'IPv4 Address')) { $serverIP = $pair[1]; break; }
			}
		}
	}
	else {									$ifconfig = shell_exec("ifconfig | grep inet");
		if ($ifconfig) {
			$ifconfigArray = explode("\n", $ifconfig);
			unset($ifconfigArray[count($ifconfigArray)-1]);
			foreach($ifconfigArray as $line) {
				$pair = explode("netmask", $line);
				$serverIP = trim($pair[0]);
			}
			$serverIP = substr($serverIP,5);
		}
	}

	echo'<div class="flex row space-evenly">';
		$zrokAlias = $ownerName.$deviceName.'WAStks';
		$zrokalias = strtolower($zrokAlias);
		$zrokState = getZrokState($zrokalias);
		echo'<div id="owner-sharing">';
			if ($zrokState == 'running') {
				$serverWanLink = 'https://'.$zrokalias.'.share.zrok.io';
				echo'<div class="qrcode">';
					echo'<span>🌐 WAN</span>';
					echo'<span><a href="'.$serverWanLink.'">'.$zrokalias.'.share.zrok.io</a></span>';
					echo genQRcode($serverWanLink);
				echo'</div>';
			} else {
				if (isset($serverIP)) {
					$serverLanLink = 'http://'.$serverIP.':8269';
					echo'<div class="qrcode">';
						echo'<span>🛜 LAN</span>';
						echo'<a href="'.$serverLanLink.'">'.$serverLanLink.'</a>';
						echo genQRcode($serverLanLink);
					echo'</div>';
				}
			}
		echo'</div>';
		echo'<div id=owner-actions>';
			if ($zrokState == 'running') {
				echo'<button onclick="post(\'zrokStop\',\''.$zrokalias.'\')"><span class="mediumButton"><span class="subicon">⏹</span>🌐</span> <span align=left>zrok stop sharing</span></button><br>';
			} else {
				echo'<button onclick="post(\'zrokShare\',\''.$zrokalias.'\')"><span class="mediumButton"><span class="subicon">▶</span>🌐</span> <span align=left>zrok share</span></button><br>';
			}
			echo $zrokalias.' ('.$zrokState.')<br>';
			// echo'<button onclick="post(\'Refresh\', \'Manifest\')"><span class="mediumButton"><img src="assets/wastk.webp"></span> <span align=left>Refresh Manifest</span></button>';
			echo'Total: <span id=localAllStickersCount>'.count($allStickers).'</span> stks<br>';
			echo'Public: <span id=localPublicStickersCount>'.count($publicStickers).'</span> stks<br>';
			echo'Private: ';
			if ($privateVisibility == 'hide')
				echo'<input type="radio" name="privatevisibility" value="hide" checked><label for="hide">hide</label>';
			else 
				echo'<input type="radio" name="privatevisibility" value="hide"><label for="hide">hide</label>';
			if ($privateVisibility == 'show')
				echo'<input type="radio" name="privatevisibility" value="show" checked><label for="show">show</label>';
			else
				echo'<input type="radio" name="privatevisibility" value="show"><label for="show">show</label>';
			echo'<button onclick="savePrivateVisibility()" title="Save Private Visibility">💾</button><br>';
			echo'<div style="width:100%;display:flex;align-items:center;justify-content:space-between;"><span style="font-size:1.5vmin;display:block;">Owner Name:</span><input id="ownerName" type="text" style="max-width:12ch;" value="'.$ownerName.'" /><button onclick="post(\'Save\',\'OwnerName\')" title="Save Owner Name">💾</button></div>';
			echo'<div style="width:100%;display:flex;align-items:center;justify-content:space-between;"><span style="font-size:1.5vmin;display:block;">Device Name:</span><input id="deviceName" type="text" style="max-width:12ch;" value="'.$deviceName.'" /><button onclick="post(\'Save\',\'DeviceName\')" title="Save Device Name">💾</button></div>';
			echo'<div style="width:100%;display:flex;align-items:center;justify-content:space-between;"><span style="font-size:1.5vmin;display:block;">Pack Publisher:</span><input id="publisher" type="text" style="max-width:11ch;" value="'.$publisher.'" /><button onclick="post(\'Save\',\'Publisher\')" title="Save Publisher">💾</button></div>';
			echo'<button onclick="post(\'Prepare\', \'StickerPacks\')"><span class="mediumButton">📦</span> <span align=left>Prepare Sticker Packs</span></button><br>';
			$androidMediaBase = '/storage/emulated/0/Android/media';
			if (is_dir($androidMediaBase)) {
				echo'Download <a href="https://spuquipcwastks.share.zrok.io/wastks_v5.apk" download="wastks_v5.apk">WAStks_v5.APK</a><br>';
			}
		echo'</div>';
	echo'</div>';
	echo'<br>';
	echo'<button onclick="post(\'Clean\', \'Favorites\')"><span class="mediumButton">🧼</span> <span align=left>Clean Favorites</span></button><br>';
	echo'<button onclick="indexMetadata()"><span class="mediumButton">📇</span> <span align=left>Index Metadata</span></button><br>';
	echo'<button onclick="post(\'Update\', \'Server\')"><span class="mediumButton">🔄</span> <span align=left>Update WAStks Server</span></button><br>';
	echo '<br>=== Checking requirements ===<br>';
	if (phpversion()) 								echo '👍 PHP(v'.phpversion().') webserver<br>';
	else											echo '⚠ PHP(missing) webserver<br>';
	$cwebpVersion = shell_exec('cwebp -version');
	if ($cwebpVersion)								echo '👍 WebP Encoder version: '.$cwebpVersion.'<br>';
	else {
		echo '⚠ WebP Encoder: missing<br>';
		if (php_uname('s') != 'Windows NT')			echo(shell_exec('pkg install libwebp'));
	}
	$webpinfoVersion = shell_exec('webpinfo -version');
	if ($webpinfoVersion)							echo '👍 '.$webpinfoVersion.'<br>';
	else {
		echo '⚠ WebP Decoder: missing<br>';
		if (php_uname('s') != 'Windows NT')			echo(shell_exec('pkg install libwebp'));
	}
	$webpmuxVersion = shell_exec('webpmux -version');
	if ($webpmuxVersion)							echo '👍 WebP Muxer version: '.$webpmuxVersion.'<br>';
	else {
		echo '⚠ WebP Muxer: missing<br>';
		if (php_uname('s') != 'Windows NT')			echo(shell_exec('pkg install libwebp'));
	}
	$exiftoolVersion = shell_exec('exiftool -ver');
	if ($exiftoolVersion)							echo '👍 ExifTool version: '.$exiftoolVersion.'<br>';
	else {
		echo '⚠ ExifTool: missing<br>';
		if (php_uname('s') != 'Windows NT')			echo(shell_exec('pkg install exiftool'));
	}
	if (file_exists('assets/NotoColorEmoji.ttf'))	echo '👍 Font NotoColorEmoji.ttf<br>';
	else {
		$fontData = file_get_contents('https://github.com/googlefonts/noto-emoji/raw/main/fonts/NotoColorEmoji.ttf');
		if ($fontData !== false) file_put_contents('assets/NotoColorEmoji.ttf', $fontData);
		if (file_exists('assets/NotoColorEmoji.ttf'))	echo '👍 Font NotoColorEmoji.ttf<br>';
		else											echo '⚠ Font NotoColorEmoji.ttf missing<br>';
	}
	$wamedia = glob('WA-Media/*');
	$usermedia = glob('User-Media/*');
	if ($wamedia || $usermedia)						echo '👍 Media<br>';
	else 											echo '⚠ Media is not accessible. Please map either WA-Media or User-Media<br>';


	function showFavoritesDetails() {
		$fav = glob('Favorites/*.fav');
		if ($fav) {
			usort($fav,'cdate_desc');

			echo 'Favorites Details:<br>';

			echo '<table>';
			echo '<tr>';
				echo '<th>#</th>';
				echo '<th>Filename</th>';
				echo '<th>Sample</th>';
				echo '<th>Emojis</th>';
				echo '<th>File Size</th>';
				echo '<th>Canvas Size</th>';
				echo '<th>Total Duration</th>';
			echo '</tr>';
			foreach($fav as $i => $favor) {
				$file = file_get_contents($favor);
				if (file_exists($file)) {
					$stk = pathinfo($file);
					
					//	Local Emojis DB
					if (file_exists('EmojisDB/'.$stk['filename'].'.emj')) $stk['emojis'] = str_getcsv(file_get_contents('EmojisDB/'.$stk['filename'].'.emj'),",","\"","\\");
					
					//	webpinfo
					$stk['webpinfo'] = shell_exec('webpinfo "'.$file.'"');
					$webpinfo = explode("\n", $stk['webpinfo']);
					$stk['TotalDuration']=0;
					$stk['FrameCount']=0;
					if ($webpinfo) forEach($webpinfo as $line) {
						$pair = explode(":", $line);
						if (!isset($stk['FileSize'])) if ($pair[0]=='  File size') $stk['FileSize']=trim($pair[1]);
						if (!isset($stk['EXIF'])) if ($pair[0]=='  EXIF') $stk['EXIF']=trim($pair[1]);
						if (!isset($stk['CanvasSize'])) {
							if (str_starts_with($pair[0],'  Canvas size ')) {
								$stk['CanvasSize']=substr($pair[0],14);
							} else {
								if (!isset($stk['Width'])) {
									if ($pair[0]=='  Width') $stk['Width']=trim($pair[1]);
								}
								if (!isset($stk['Height'])) {
									if ($pair[0]=='  Height') $stk['Height']=trim($pair[1]);
								}
							}
						}
						if ($pair[0]=='  Duration') {
							$stk['TotalDuration']+=trim($pair[1]);
							$stk['FrameCount']+=1;
						}
					}
					if (!isset($stk['CanvasSize'])) {
						if ( (isset($stk['Width']))&&(isset($stk['Height'])) ) {
							$stk['CanvasSize'] = $stk['Width'].' x '.$stk['Height'];
						}
					}
					echo '<tr>';
						echo '<td>'.$i.'</td>';
						echo '<td>'.$stk['filename'].'</td>';
						echo '<td><img width="64px" src="'.$file.'"></td>';
						$emojis = "";
						if (isset($stk['emojis'])) {
							if (isset($stk['emojis'][0])) $emojis = $stk['emojis'][0];
							if (isset($stk['emojis'][1])) $emojis = $emojis.','.$stk['emojis'][1];
							if (isset($stk['emojis'][2])) $emojis = $emojis.','.$stk['emojis'][2];
						}
						echo '<td>'.$emojis.'</td>';
						if (isset($stk['FileSize'])) {
							if ($stk['FileSize']<=512000)			echo '<td style="background-color:green;">'.$stk['FileSize'].'</td>';
							else									echo '<td style="background-color:red;">'.$stk['FileSize'].'</td>';
						} else										echo '<td style="background-color:red;">Unknown</td>';
						if (isset($stk['CanvasSize'])) {
							if ($stk['CanvasSize']=='512 x 512')	echo '<td style="background-color:green;">'.$stk['CanvasSize'].'</td>';
							else									echo '<td style="background-color:red;">'.$stk['CanvasSize'].'</td>';
						} else										echo '<td style="background-color:red;">Unknown</td>';
						if (isset($stk['TotalDuration'])) {
							if ($stk['TotalDuration'] <= 10000)		echo '<td style="background-color:green;">'.$stk['TotalDuration'].'</td>';
							else									echo '<td style="background-color:red;">'.$stk['TotalDuration'].'</td>';
						} else										echo '<td style="background-color:red;">Unknown</td>';
					echo '</tr>';
				}
				else {
					echo '<tr>';
						echo '<td>'.$i.'</td>';
						echo '<td>X</td>';
						echo '<td>'.$favor.'</td>';
						echo '<td>'.$file.'</td>';
					echo '</tr>';
				}
			}
			echo '</table>';
		}
		else echo 'No favorites yet.<br>';
		echo '<br>';
	}
	function updateFavorites($operation,$reference1,$reference2) {
		$fav = glob('Favorites/*.fav');
		usort($fav,'cdate_desc');
		
		echo 'Favorites count: '.count($fav).'<br>';
		foreach($fav as $favor) {
			$file = file_get_contents($favor);
			if (!file_exists($file)) {
				if ($operation=='check') {
					echo $favor.' is broken as '.$file.' does not exist.<br>';
				} else if ($operation=='rename') {
					$newfile = str_replace($reference1, $reference2, $file);
					file_put_contents($favor, $newfile);
					echo $favor.' renamed from '.$file.' to '.$newfile.'.<br>';
				}
			}
		}
		echo'Favorites check complete.<br>';
	}
	
	//	Process Favorites ( Check / Update Path )
	$processFavorites = 0;
	if ($processFavorites) {
		// updateFavorites('rename','WhatsApp Stickers/','WA-Media/WhatsApp Stickers/');
		// updateFavorites('rename','WhatsApp Sticker Packs/','WA-Media/WhatsApp Sticker Packs/');
		// updateFavorites('rename','2026-05-WAStks','2026-04');
		
		//cleanUpFavorites();
		updateFavorites('check','','');
		showFavoritesDetails();
	}
	
	//	Process translation of emojis json
	$translateEmojis = 0;
	if ($translateEmojis) {
		echo '<br>';
		// load emojis in English
		if (file_exists('emojis-latest.json')) {
			$emojisENcoded = file_get_contents('emojis-latest.json');
			$emojisEN = json_decode($emojisENcoded, true);
			echo 'Loaded emojis in English<br>';
			if (file_exists('ES.annotations.json')) {
				$annotationsEScoded = file_get_contents('ES.annotations.json');
				$annotationsES = json_decode($annotationsEScoded, true);
				echo 'Loaded annotations in Spanish<br>';
			}
			if (file_exists('ES.annotationsDerived.json')) {
				$annotationsESDcoded = file_get_contents('ES.annotationsDerived.json');
				$annotationsESD = json_decode($annotationsESDcoded, true);
				echo 'Loaded annotations Derived in Spanish<br>';
			}
			// print_r($annotationsPT['annotations']['annotations']);
			foreach($emojisEN['emojis'] as $emj) {
				// print_r($emj['sample']);
				$id = $emj['id'];
				$code = $emj['code'];
				$sample = $emj['sample'];
				$cat =  $emj['cat'];
				$subcat =  $emj['subcat'];
				$name = '';
				$keywords = [];
				if (isset($annotationsES['annotations']['annotations'][$sample])) {
					$name = implode(", ", $annotationsES['annotations']['annotations'][$sample]['tts']);
					$keywords = $annotationsES['annotations']['annotations'][$sample]['default'];
				} else {
					if (isset($annotationsESD['annotationsDerived']['annotations'][$sample])) {
						$name = implode(", ", $annotationsESD['annotationsDerived']['annotations'][$sample]['tts']);
						$keywords = $annotationsESD['annotationsDerived']['annotations'][$sample]['default'];
					} else {
						$name = $emj['name'];
						$keywords = $emj['keywords'];
					}
				}
				$emojisES['emojis'][] = ['id' => $id, 'code' => $code, 'sample' => $sample, 'cat' => $cat, 'subcat' => $subcat, 'name' => $name, 'keywords' => $keywords];
			}
			file_put_contents('emojis-latest-ES.json', json_encode($emojisES, JSON_UNESCAPED_UNICODE));
			echo 'Saved emojis-latest-ES.json';
		}
	}
	

echo'</div>';

?>