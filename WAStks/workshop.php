<?php

function getWorkshopStickerInfo($pathfilename) {
	unset($stk);
	$stk = pathinfo($pathfilename);
	
	$metadata = file_exists('metadata.json') ? json_decode(file_get_contents('metadata.json'), true) : [];
	$stk['EXIF'] = $metadata[$stk['filename']] ?? [];
	$stk['emojis'] = $stk['EXIF']['emojis'] ?? [];

	//	Gets audio file, if there is one
	if ( (str_starts_with($stk['filename'],'STK-')) && (strlen($stk['filename'])==19) ) {
		$stkdate = substr($stk['filename'],4,8);
		$stkindex = substr($stk['filename'],-4,4);
		$audindex = sprintf("%04d", intval($stkindex) + 1);
		$localaudfile = 'Workshop/AUD-'.$stkdate.'-WA'.$audindex.'.aac';
		if (file_exists($localaudfile)) $stk['audioFile'] = $localaudfile;
		$audfile = 'WA-Media/WhatsApp Audio/AUD-'.$stkdate.'-WA'.$audindex.'.aac';
		if (file_exists($audfile)) $stk['audioFile'] = $audfile;
		$audfilesent = 'WA-Media/WhatsApp Audio/Sent/AUD-'.$stkdate.'-WA'.$audindex.'.aac';
		if (file_exists($audfilesent)) $stk['audioFile'] = $audfilesent;
		if (file_exists('Workshop/'.$stk['filename'].'.aac'))
			$stk['audioFile'] = 'Workshop/'.$stk['filename'].'.aac';
	} else {
		if (file_exists($stk['dirname'].'/'.$stk['filename'].'.aac'))
			$stk['audioFile'] = $stk['dirname'].'/'.$stk['filename'].'.aac';
	}
	if (isset($stk['audioFile'])) $stk['audioFileSize'] = filesize($stk['audioFile']);
	
	//	Gets log information if a log exists
	if (file_exists('Logs/'.$stk['filename'].'.log')) {
		$stk['log'] = explode("\n", file_get_contents('Logs/'.$stk['filename'].'.log'));
		foreach ($stk['log'] as $entry) {
			$param = explode(';', $entry);
			if (count($param) > 1) {
				if($param[1] == 'committed') {
					$sticker['committed'] = $param[0];
				}
				if (!isset($sticker['created'])) {
					if ($param[1] == 'created') {
						$stk['created'] = $param[0];
						$stk['fps'] = $param[2];
						$stk['first'] = $param[3];
						$stk['ffd'] = $param[4];
					}
				}
			}
		}
	}
	
	//	webpinfo
	$webpinfoString = shell_exec('webpinfo "'.$pathfilename.'"');
	if ($webpinfoString) $webpinfoArray = explode("\n", $webpinfoString);
	$stk['TotalDuration']=0;
	$stk['FrameCount']=0;
	if (isset($webpinfoArray)) forEach($webpinfoArray as $line) {
		$pair = explode(":", $line);
		if (!isset($stk['FileSize'])) if ($pair[0]=='  File size') $stk['FileSize']=intval(trim($pair[1]));
		if (!isset($stk['hasEXIF'])) if ($pair[0]=='  EXIF') $stk['hasEXIF']=intval(trim($pair[1]));
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
	
	
	/*
	if (isset($stk['hasEXIF'])) if ($stk['hasEXIF']) {
		$EXIFjson = shell_exec('exiftool -u -b -Exif_0x5741 "'.$stk['dirname'].'/'.$stk['basename'].'"');
		if ($EXIFjson) {
			$stk['EXIFjson'] = $EXIFjson;
			$stk['EXIF'] = json_decode($EXIFjson, true);
		}
	}
	*/
	return $stk;
}
function buildWorkshopStickerCard($i,$stickerscount,$sticker) {
	echo PHP_EOL.'<div id=workshop'.$i.'card class="workshopCard" data-filename="'.$sticker['filename'].'">';
		// echo '<input type="hidden" id="workshop'.$i.'" value="'.$sticker['filename'].'" />';
		echo '<input type="hidden" id="workshop'.$sticker['filename'].'" value="'.$i.'" />';
		// Hidden inputs for Disassembling
		if (isset($sticker['first'])) echo '<input id="'.$i.'first" type="hidden" value="'.$sticker['first'].'" />';
		if (isset($sticker['ffd']))	echo '<input id="'.$i.'ffd" type="hidden" value="'.$sticker['ffd'].'" />';
		if (isset($sticker['FrameCount']))	echo '<input id="'.$i.'FrameCount" type="hidden" value="'.$sticker['FrameCount'].'" />';

		echo'<div style="width:100%;" class="flex space-between">';
			echo'<div style="width:calc(100% - 4.4vh);" class="flexcolumn">';
				echo'<div class=workshopEmojis>';
					echo'<span style="gap:0.6vh;">';
					$unofficial = false;
					if (isset($sticker['FileSize'])) {
						if ($sticker['FileSize'] <= 512000) {
							echo '<span class=official style="font-size:2.2vh;gap:0.6vh;">'.number_format($sticker['FileSize']).' <span style="font-size:1.5vh;">bytes</span></span>'.PHP_EOL;
						} else if ($sticker['FileSize'] <= 1048576) {
							echo '<span class=unofficial style="font-size:2.2vh;gap:0.6vh;">'.number_format($sticker['FileSize']).' <span style="font-size:1.5vh;">bytes</span></span>'.PHP_EOL;
							$unofficial = true;
						} else {
							echo '<span class=overlimit style="font-size:2.2vh;gap:0.6vh;">'.number_format($sticker['FileSize']).' <span style="font-size:1.5vh;">bytes</span></span>'.PHP_EOL;
							$unofficial = true;
						}
					}
					if ( isset($sticker['CanvasSize']) ) {
						if ($sticker['CanvasSize'] != "512 x 512") $unofficial = true;
					}
					if (isset($sticker['TotalDuration'])) {
						if ($sticker['TotalDuration'] > 10000) $unofficial = true;
					}
					if ($unofficial) echo '<span title="Unofficial" style="font-size:2.2vh;">🦜</span>';
					echo'</span>';
					
					if (isset($sticker['audioFile']))	echo '<button draggable=true class="audio-draggable sysbutton" title="Play" data-audio-size="'.$sticker['audioFileSize'].'" data-src="'.$sticker['audioFile'].'" onclick="audioPlay(\''.$sticker['audioFile'].'\')">🔊</button>';
					
					echo'<span>';
					if (!empty($sticker['emojis'][0])) echo '<button class=emoji name="emoji1" title="Copy emoji" onclick="emojicopy(\''.$sticker['emojis'][0].'\')">'.$sticker['emojis'][0].'</button>';
					if (!empty($sticker['emojis'][1])) echo '<button class=emoji name="emoji2" title="Copy emoji" onclick="emojicopy(\''.$sticker['emojis'][1].'\')">'.$sticker['emojis'][1].'</button>';
					if (!empty($sticker['emojis'][2])) echo '<button class=emoji name="emoji3" title="Copy emoji" onclick="emojicopy(\''.$sticker['emojis'][2].'\')">'.$sticker['emojis'][2].'</button>';
					echo'</span>';

					echo'<span>';
					if (!empty($sticker['emojis'][0]))	{ echo'<button onclick="callEmojiPicker(\''.$i.'_emoji1\')" id="'.$i.'_emoji1" type="text" size=1 autocomplete=off class="signemojiinput">'.$sticker['emojis'][0].'</button>';	}
					else								{ echo'<button onclick="callEmojiPicker(\''.$i.'_emoji1\')" id="'.$i.'_emoji1" type="text" size=1 autocomplete=off class="signemojiinput"></button>';							}
					if (!empty($sticker['emojis'][1]))	{ echo'<button onclick="callEmojiPicker(\''.$i.'_emoji2\')" id="'.$i.'_emoji2" type="text" size=1 autocomplete=off class="signemojiinput">'.$sticker['emojis'][1].'</button>';	}
					else								{ echo'<button onclick="callEmojiPicker(\''.$i.'_emoji2\')" id="'.$i.'_emoji2" type="text" size=1 autocomplete=off class="signemojiinput"></button>';							}
					if (!empty($sticker['emojis'][2]))	{ echo'<button onclick="callEmojiPicker(\''.$i.'_emoji3\')" id="'.$i.'_emoji3" type="text" size=1 autocomplete=off class="signemojiinput">'.$sticker['emojis'][2].'</button>';	}
					else								{ echo'<button onclick="callEmojiPicker(\''.$i.'_emoji3\')" id="'.$i.'_emoji3" type="text" size=1 autocomplete=off class="signemojiinput"></button>';							}
					echo'<button title="Save Emojis" onclick="post(\'SaveEmojis\', \''.$sticker['filename'].'\')" class="signemoji">💾</button>';
					echo'</span>';
				echo'</div>';

				echo PHP_EOL.'<div class=bothbgs>';
					echo'<div class="background-color">';
						echo'<img title="'.$sticker['filename'].'" id=workshop'.$i.'img src="'.$sticker['dirname'].'/'.$sticker['filename'].'.webp'.'">';
					echo'</div>';
					echo'<div class="background-color-inv">';
						if (isset($sticker['source']))
							echo'<img title="Source file over Opposite Background" id=workshop'.$i.'obg src="'.$sticker['source'].'">';
						else
							echo'<img title="'.$sticker['filename'].'" src="'.$sticker['dirname'].'/'.$sticker['filename'].'.webp'.'">';
					echo'</div>';
				echo'</div>';
				
				echo '<div class=workshopFilename>';
					// Filename
					echo'<span>'.$sticker['filename'].'</span>';
					// Index / Count
					echo'<span>'.($i+1).'/'.$stickerscount.' </span>'.PHP_EOL;
				echo'</div>';
			echo'</div>';
			echo PHP_EOL.'<div id=workshop'.$i.'webpactions class=workshopActions>';
			{	// Workshop Actions

				if (isset($sticker['source'])) {
					echo '<input type=hidden id="'.$sticker['filename'].'source" value="'.$sticker['source'].'">'.PHP_EOL;
					if (file_exists('Favorites/'.$sticker['filename'].'.fav'))	echo '<button onclick="post(\'ToggleFav\', \''.$sticker['filename'].'\')" title="Remove from Favorites">❤</button>';
					else														echo '<button onclick="post(\'ToggleFav\', \''.$sticker['filename'].'\')" title="Add to Favorites" style="opacity:0.33;">❤</button>';
				}
				echo '<button title="Unlink '.$sticker['filename'].'" onclick="post(\'Unlink\', \''.$sticker['filename'].'\')" class="emoji">⛓️‍💥</button>'.PHP_EOL;
				if (isset($sticker['source'])) {
					if (!str_starts_with($sticker['source'], 'WA-Media'))
						echo '<button onclick="post(\'Update\', \''.$sticker['filename'].'\')" title="Update Source">💾</button>';
				}
				echo '<button title="ToggleSign '.$sticker['filename'].'" onclick="toggleSignature(\''.$sticker['filename'].'\')">✒</button>';
				
				echo '<button title="Create Tray Icon" onclick="post(\'CreateTrayIcon\', \''.$sticker['filename'].'\')">🦄</button>';
				
				/*
				$adbAvailable = command_exists('adb');
				if ($adbAvailable) {
					echo '<button title="TogglePhone '.$sticker['filename'].'" onclick="togglePhone(\''.$sticker['filename'].'\')">📱</button>';
				}
				*/
				
				if ($sticker['TotalDuration'] > 0) echo '<button title="Disassemble '.$sticker['filename'].'" onclick="post(\'Disassemble\', \''.$sticker['filename'].'\')" class="emoji">⬅</button>';

			}
			echo'</div>';

		echo'</div>';
	echo'</div>'.PHP_EOL;
}
function command_exists($cmd) {
    if (php_uname('s') == 'Windows NT') {
        // Windows 'where' is usually more reliable than 'command'
        $test = shell_exec("where $cmd 2>NUL");
    } else {
        // Linux/Termux builtin
        $test = shell_exec("command -v $cmd 2>/dev/null");
    }
    return !empty($test);
}

//unset($sourceStickers);
$workshopStickers = glob('Workshop/*.webp', GLOB_NOSORT);

if (count($workshopStickers)>0) {
	echo'<div id=workshop-content>';
	if (!function_exists('cdate_desc')) {
		function cdate_desc($element1, $element2)	{ return filectime($element2) <=> filectime($element1);	}
	}
	usort($workshopStickers, 'cdate_desc');
	
	foreach ($workshopStickers as $i => $file) {
		unset($sticker);
		$sticker = getWorkshopStickerInfo($file);
		
		if (file_exists('Workshop/'.$sticker['filename'].'.src')) {
			$sticker['source'] = file_get_contents('Workshop/'.$sticker['filename'].'.src');
			//$sourceStickers[] = $sticker['source'];
		}
		
		buildWorkshopStickerCard($i,count($workshopStickers),$sticker);
	};
	echo'</div>';
} else { echo'<div id=workshop-content class="hide"></div>'; }
echo'<div id=workshop-title class="server-title">';
	echo'<span><span class=shadow>🔧</span> Workshop</span>';
		echo'<span class="flex row" style="gap:0.5vh;">';
			if (count($workshopStickers)>0) {
				echo'<input type="text" id=PackageName value="'.date("Y-m").'" autocomplete="off" />';
				echo'<button id=packageworkshop class="emoji" title="Move Into Package" onclick="post(\'MoveIntoPackage\', document.getElementById(\'PackageName\').value)">📦</button>';
				echo'<button id=privatepackageworkshop class="emoji" title="Move Into Private Package" onclick="post(\'MoveIntoPrivatePackage\', document.getElementById(\'PackageName\').value)"><span class="subicon">🔒</span>📦</button>';
				echo'<button id=clearworkshop class="emoji" title="Clear Workshop" onclick="post(\'Clear\', \'Workshop\')">🧽</button>';
			}
			echo'<button title="Refresh Workshop" onclick="post(\'Refresh\',\'Workshop\')">♻</button>';
		echo'</span>';
	echo'</span>';
echo'</div>';

?>