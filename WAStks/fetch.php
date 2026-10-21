<?php
$origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
header("Access-Control-Allow-Origin: $origin");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
// Handle the "Pre-flight" OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}
if (isset($_GET['get_manifest'])) {
    header('Content-Type: application/json');
    
    $manifestFile = "manifest.json";
    $tokenPath = ".owner/token.txt";
    if (isset($_COOKIE['owner_auth'])) {
        if (file_exists($tokenPath)) {
            $storedToken = trim(file_get_contents($tokenPath));
            $userCookie = trim($_COOKIE['owner_auth']);
            if ($userCookie === $storedToken) {
                $ownerManifest = "owner-manifest.json";
                if (file_exists($ownerManifest)) {
                    $manifestFile = $ownerManifest;
                }
            }
        }
    }

    $content = json_decode(file_get_contents($manifestFile), true);
    
    echo json_encode($content);
    exit;
}
if (isset($_GET['get_file'])) {
    $filePath = urldecode($_GET['get_file']);
    $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

    // Security: Allow Stickers or Audio directories
    $allowed = (strpos($filePath, 'WA-Media/') === 0 || strpos($filePath, 'User-Media/') === 0 || strpos($filePath, 'Packs/') === 0 || strpos($filePath, 'PrivatePacks/') === 0);

    if ($allowed && file_exists($filePath)) {
        // Map extensions to correct Mime Types
        $mimes = [
            'webp' => 'image/webp',
            'json' => 'application/json',
            'aac'  => 'audio/aac',
            'mp3'  => 'audio/mpeg',
            'm4a'  => 'audio/mp4',
            'ogg'  => 'audio/ogg'
        ];

        $contentType = $mimes[$extension] ?? 'application/octet-stream';
        
        header('Content-Type: ' . $contentType);
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }
    
    http_response_code(404);
    exit;
}

if (php_uname('s') == 'Windows NT')		$foldsep = "\\";
else									$foldsep = "/";

//	Functions
function checkStickerCompliance($pathfilename) {
	unset($stk);
	$webpinfoString = shell_exec('webpinfo "'.$pathfilename.'"');
	$webpinfoArray = explode("\n", $webpinfoString);
	$stk['TotalDuration']=0;
	$stk['FrameCount']=0;
	if ($webpinfoArray) forEach($webpinfoArray as $line) {
		$pair = explode(":", $line);
		if (!isset($stk['FileSize'])) if ($pair[0]=='  File size') $stk['FileSize']=intval(trim($pair[1]));
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
	if ( ($stk['CanvasSize']=="512 x 512")&&($stk['FileSize']<="512000")&&($stk['TotalDuration']<="10000") ) {
		return 1;
	} else {
		return 0;
	}
}
function getStickerInfo($pathfilename) {
	unset($stk);
	//	PathInfo
	$stk = pathinfo($pathfilename);
	
	$jsonPath = 'metadata.json';
	$metadata = file_exists($jsonPath) ? json_decode(file_get_contents($jsonPath), true) : [];
	$meta = $metadata[$stk['filename']] ?? [];
	$stk['emojis'] = $meta['emojis'] ?? [];

	//	Gets audio file, if there is one
	if ( (str_starts_with($stk['filename'],'STK-')) && (strlen($stk['filename'])==19) ) {
		$stkdate = substr($stk['filename'],4,8);
		$stkindex = substr($stk['filename'],-4,4);
		$audindex = sprintf("%04d", intval($stkindex) + 1);
		$audfile = 'WA-Media/WhatsApp Audio/AUD-'.$stkdate.'-WA'.$audindex.'.aac';
		if (file_exists($audfile)) $stk['audioFile'] = $audfile;
		$audfilesent = 'WA-Media/WhatsApp Audio/Sent/AUD-'.$stkdate.'-WA'.$audindex.'.aac';
		if (file_exists($audfilesent)) $stk['audioFile'] = $audfilesent;
	} else {
		if (file_exists('WA-Media/WhatsApp Audio/'.$stk['filename'].'.aac'))
			$stk['audioFile'] = 'WA-Media/WhatsApp Audio/'.$stk['filename'].'.aac';
	}
	
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
	$webpinfoArray = explode("\n", $webpinfoString);
	$stk['TotalDuration']=0;
	$stk['FrameCount']=0;
	if ($webpinfoArray) forEach($webpinfoArray as $line) {
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
	
	if (isset($stk['hasEXIF'])) if ($stk['hasEXIF']) {
		$EXIFjson = shell_exec('exiftool -u -b -Exif_0x5741 "'.$stk['dirname'].'/'.$stk['basename'].'"');
		if ($EXIFjson) {
			$stk['EXIFjson'] = $EXIFjson;
			$stk['EXIF'] = json_decode($EXIFjson, true);
		}
	}
	return $stk;
}
function buildStickerInfo($sticker) {
	echo '<div class=stickerInfo>';
	foreach($sticker as $key => $value) {
		if (gettype($key)=="string") {		// key is string
			if ($key!="log") {
				if (gettype($value)!="array") {		// value is NOT array
					echo 'sticker["'.$key.'"] = '; var_dump($value); echo '<br>';
				}
				else {								// value is array
					foreach($value as $key2 => $value2) {
						if (gettype($key2)=="string") {	// key2 is string
							if (gettype($value2)!="array") {	// value2 is NOT array
								echo 'sticker["'.$key.'"]["'.$key2.'"] = '; var_dump($value2); echo '<br>';
							}
							else {								// value2 is array
								foreach($value2 as $key3 => $value3) {
									if (gettype($key3)=="string") {		// key3 is string
										echo 'sticker["'.$key.'"]["'.$key2.'"]["'.$key3.'"] = '; var_dump($value3); echo'<br>';
									}
									else {								// key3 is NOT string
										echo 'sticker["'.$key.'"]["'.$key2.'"]['.$key3.'] = '; var_dump($value3); echo'<br>';
									}
								}
							}
						}
						else {							// key2 is NOT string
							if (gettype($value2)!="array") {	// value2 is NOT array
								echo 'sticker["'.$key.'"]['.$key2.'] = '; var_dump($value2); echo '<br>';
							}
							else {								// value2 is array
								foreach($value2 as $key3 => $value3) {
									if (gettype($key3)=="string") {		// key3 is string
										echo 'sticker["'.$key.'"]['.$key2.']["'.$key3.'"] = '; var_dump($value3); echo'<br>';
									}
									else {								// key3 is NOT string
										echo 'sticker["'.$key.'"]['.$key2.']['.$key3.'] = '; var_dump($value3); echo'<br>';
									}
								}
							}
						}
					}
				}
			}	
		}
		else {								// key is NOT string
			if (gettype($value)!="array") {		// value is NOT array
				echo 'sticker['.$key.'] = '; var_dump($value); echo '<br>';
			}
			else {								// value is array
				foreach($value as $key2 => $value2) {
					if (gettype($key2)=="string") {	// key2 is string
						if (gettype($value2)!="array") {	// value2 is NOT array
							echo 'sticker['.$key.']["'.$key2.'"] = '; var_dump($value2); echo '<br>';
						}
						else {								// value2 is array
							foreach($value2 as $key3 => $value3) {
								if (gettype($key3)=="string") {		// key3 is string
									echo 'sticker['.$key.']["'.$key2.'"]["'.$key3.'"] = '; var_dump($value3); echo'<br>';
								}
								else {								// key3 is NOT string
									echo 'sticker['.$key.']["'.$key2.'"]['.$key3.'] = '; var_dump($value3); echo'<br>';
								}
							}
						}
					}
					else {							// key2 is NOT string
						if (gettype($value2)!="array") {	// value2 is NOT array
							echo 'sticker['.$key.']['.$key2.'] = '; var_dump($value2); echo '<br>';
						}
						else {								// value2 is array
							foreach($value2 as $key3 => $value3) {
								if (gettype($key3)=="string") {		// key3 is string
									echo 'sticker['.$key.']['.$key2.']["'.$key3.'"] = '; var_dump($value3); echo'<br>';
								}
								else {								// key3 is NOT string
									echo 'sticker['.$key.']['.$key2.']['.$key3.'] = '; var_dump($value3); echo'<br>';
								}
							}
						}
					}
				}
			}
		}
	}
	echo '</div>'.PHP_EOL;
}
function renderExifForm($filename) {
    $path = 'Workshop/'.$filename.'.webp';
	$srcPath = 'Workshop/'.$filename.'.src';
	$pubPath = '.owner/publisher.txt';
	
	$originPath = file_exists($srcPath) ? trim(file_get_contents($srcPath)) : '';
	$defaultPublisher = file_exists($pubPath) ? trim(file_get_contents($pubPath)) : 'WAStks';
	
    $sticker = getStickerInfo($path);
    $exif = $sticker['EXIF'] ?? [];

    $packName = $exif['sticker-pack-name'] ?? '';
    $publisher = $exif['sticker-pack-publisher'] ?? '';
    
    /* 1. The "Core 6": Always show these, even if empty */
    $standardKeys = [
        'accessibility-text',
        'sticker-pack-name',
        'sticker-pack-publisher',
        'sticker-pack-id',
        'android-app-store-link',
        'ios-app-store-link'
    ];

    /* 2. Keys handled elsewhere in the UI (Hero section) */
    $uiHandledKeys = ['emojis'];

    echo '<div id="signForm" style="position: relative;">';
        echo '<form id="form-'.$filename.'">';
            echo '<input type="hidden" id="SignatureFor" value="'.$filename.'">';
			echo '<input type="hidden" id="origin-'.$filename.'" value="'.htmlspecialchars($originPath).'">';
			echo '<input type="hidden" id="default-pub-'.$filename.'" value="'.htmlspecialchars($defaultPublisher).'">';
            echo '<button type="button" class="sign-close-btn" title="Close Editor" onclick="toggleSignature(\'\')">✕</button>';

            /* HERO SECTION (Image + Emoji Slots) */
            echo '<div id="sign-hero">';
                echo '<div class="signEmojis">';
                    $currentEmojis = $exif['emojis'] ?? [];
                    for ($k = 0; $k < 3; $k++) {
                        $val = $currentEmojis[$k] ?? '';
                        if (!empty($val)) echo '<button type="button" class="emoji" title="Copy emoji" onclick="emojicopy(\''.htmlspecialchars($val).'\')">'.htmlspecialchars($val).'</button>';
                        else echo '<div style="height:4vh; width:4vh;"></div>';
                    }
                echo '</div>';
                
                echo '<div id="sign-hero-image" style="text-align:center;">';
                    echo '<img src="'.$path.'" id="sign-sticker-preview" style="height: calc(1.8 * var(--cardHeight)); width: auto; max-width: 50vw; object-fit: contain;">';
                echo '</div>';

                echo '<div class="signEmojis">';
                    for ($k = 1; $k <= 3; $k++) {
                        $val = $currentEmojis[$k-1] ?? '';
                        $targetID = "workshop_emoji_".$k; 
                        echo '<input type="text" id="'.$targetID.'" name="emoji_slots[]" class="signemojiinput" style="font-family:custom;" autocomplete="off" onclick="callEmojiPicker(\''.$targetID.'\')" value="'.htmlspecialchars($val).'">';
                    }
                echo '</div>';
            echo '</div>';
			echo '<div class="wa-metadata-title" style="text-align:center;">';
				echo '<span id="preview-pack">'.htmlspecialchars($packName).'</span>';
				if (!empty($packName) && !empty($publisher)) { echo '<span> • </span>'; }
				echo '<span id="preview-pub">'.htmlspecialchars($publisher).'</span>';
			echo '</div>';

            echo '<hr class="workshop-divider" style="opacity:0.1; margin: 1vh 0;">';

            /* ACTIONS */
            echo '<div class="wa-action-list">';
                echo '<div class="wa-action-row" onclick="applySignatureTemplate()"><span class="icon">✒</span><span class="label">Apply Signature Template</span></div>';
                echo '<div class="wa-action-row" onclick="updateSignature(\''.$filename.'\')"><span class="icon">💾</span><span class="label">Update Signature</span></div>';
            echo '</div>';

            /* TECHNICAL FIELDS */
            echo '<div id="sign-technical-fields" style="padding: 0 4vw; text-align: left;">';
                
                // A. Render the Standard WhatsApp Keys (Always present)
                foreach ($standardKeys as $key) {
                    $val = $exif[$key] ?? '';
                    echo '<div class="EXIF-key">';
                        echo '<label>'.strtoupper(str_replace('-', ' ', $key)).'</label>';
                        echo '<input type="text" name="exif_data['.$key.']" value="'.htmlspecialchars($val).'">';
                    echo '</div>';
                }

                // B. Render ANY OTHER key currently present in EXIF (Dynamic)
                foreach ($exif as $key => $value) {
                    // Skip if already rendered in Standard list or UI section
                    if (in_array($key, $standardKeys) || in_array($key, $uiHandledKeys)) continue;
                    
                    // Skip if value is empty/null (to prevent ghost custom fields)
                    if (empty($value) && $value !== "0") continue;

                    echo '<div class="EXIF-key">';
                        echo '<label>'.strtoupper(str_replace('-', ' ', $key)).'</label>';
                        $displayVal = is_array($value) ? implode(',', $value) : $value;
                        echo '<input type="text" name="exif_data['.$key.']" value="'.htmlspecialchars($displayVal).'">';
                    echo '</div>';
                }

            echo '</div>';
        echo '</form>';
    echo '</div>';
}
function renderPhoneForm($filename) {
	$wasp='/data/data/com.whatsapp/files/Stickers/';
	$ando='/storage/emulated/0/Download/';

	$lastSticker = trim(basename(shell_exec('adb -s emulator-5554 shell "su -c ls -crt /data/data/com.whatsapp/files/Stickers/*.webp | tail -n 1"')));
	if ($lastSticker) {
		$copy = shell_exec('adb -s emulator-5554 shell "su -c cp -v -f '.$wasp.$lastSticker.' '.$ando.$lastSticker.'"');
		if (str_starts_with($copy,'cp ')) {
			shell_exec('adb -s emulator-5554 pull '.$ando.$lastSticker.' Temp/'.$lastSticker);
			shell_exec('adb -s emulator-5554 shell "su -c rm '.$ando.$lastSticker.'"');
			copy('Temp/'.$lastSticker, 'Temp/lastSticker.webp');
			unlink('Temp/'.$lastSticker);
		}
		echo '<div id="phone-form">';
			echo '<input type="hidden" id="PhoneFor" value="'.$filename.'">';
			echo '<img src="Workshop/'.$filename.'.webp">';
			echo '<button title="Update Phone\'s Last Sticker" class="topBarButton" onclick="updateLastSticker(\''.$filename.'\', \''.$lastSticker.'\')">📲</button>';
			echo '<img title="Last Sticker" src="Temp/lastSticker.webp?v='.time().'">';
		echo '</div>';
	} else {
		echo '<div id="phone-form">';
			echo '<input type="hidden" id="PhoneFor" value="'.$filename.'">';
			echo '<img title="Workshop Sticker" src="Workshop/'.$filename.'.webp">';
			echo '<button disabled title="DISABLED - Update Phone\'s Last Sticker" class="topBarButton" onclick="">📲</button>';
			echo '<img title="Last Sticker Unavailable" src="Temp/lastSticker.webp?v='.time().'">';
		echo '</div>';
	}
}
function copyNewSticker($filename) {
    $workshopPath = 'Workshop/'.$filename.'.webp';
    $srcFile  = 'Workshop/'.$filename.'.src';
    
    if (!file_exists($srcFile)) {
		$destDir = 'WA-Media/WhatsApp Stickers';
		$destPath = $destDir.'/'.$filename.'.webp';

		// If filename exists in target, user chose NOT to overwrite, so we version it
		if (file_exists($destPath)) {
			$count = 1;
			while (file_exists($destDir.'/'.$filename.'_'.$count.'.webp')) {
				$count++;
			}
			$destPath = $destDir.'/'.$filename.'_'.$count.'.webp';
			$filename = $filename.'_'.$count; // Update for the serverMsg
		}

		if (copy($workshopPath, $destPath)) {
			serverMsg($filename.' was copied to "WA-Media/WhatsApp Stickers" folder.', '');
			return true;
		}
    }

    return false;
}
function serverMsg($msg,$reqID) {
	if ($reqID)	echo '<div class="serverMsgDiv" id="'.$reqID.'">';
	else		echo '<div class="serverMsgDiv">';
		echo '<img width="40px" src="assets/ghost_1f47b_Spuqui.webp">';
		echo '<div class="serverMsg">';
			echo '<div class="timestamp">'.date("H:i:s").' </div>';
			
			print_r($msg);
		echo '</div>';
	echo '</div>'.PHP_EOL;
}
function replyUp($reply,$reqID) {
	serverMsg('<span title="Success" class="reply up">👍</span> '.$reply,$reqID);
}
function replyDown($reply,$reqID) {
	serverMsg('<span title="Error" class="reply down">👎</span> '.$reply,$reqID);
}
function systemMsg($msg) {
	echo '<div class="systemMsgDiv">';
		echo '<div class="systemMsg">';
			echo($msg);
		echo '</div>';
	echo '</div>';
}
if (!function_exists('cdate_asc')) {function cdate_asc($element1, $element2)	{ return filectime($element1) <=> filectime($element2);	}	}
if (!function_exists('cdate_desc')) {function cdate_desc($element1, $element2)	{ return filectime($element2) <=> filectime($element1);	}	}
if (!function_exists('mdate_asc')) {function mdate_asc($element1, $element2)	{ return filemtime($element1) <=> filemtime($element2);	}	}
if (!function_exists('mdate_desc')) {function mdate_desc($element1, $element2)	{ return filemtime($element2) <=> filemtime($element1);	}	}

if (file_exists('owner-manifest.json')) {
    $manifestData = json_decode(file_get_contents('owner-manifest.json'), true);
    $allFolders = $manifestData['allFolders'] ?? [];
    $allStickers = $manifestData['allStickers'] ?? [];
}

echo'<div id=serverChat>';
	//	GUEST POSTS
	if (isset($_POST['Authentication'])) {
		$providedToken = $_REQUEST['passwd'] ?? '';
		$tokenFile = '.owner/token.txt';
		if (file_exists($tokenFile)) {
			$storedToken = trim(file_get_contents($tokenFile));
			if ($providedToken === $storedToken) {
				// Set a secure, long-lived cookie for the owner
				serverMsg('Owner logged in', '');
			} else {
				replyDown('Wrong password. Access denied.', '');
			}
		}
	}
	if (isset($_POST['RemoveCollection'])) {
		$collectionsFile = 'collections.txt';
		$targetCollection = $_POST['RemoveCollection'];
		
		if (file_exists($collectionsFile)) {
			$collections = file($collectionsFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
			$newCollections = array_filter($collections, function($s) use ($targetCollection) {
				return trim($s) !== trim($targetCollection);
			});
			file_put_contents($collectionsFile, implode(PHP_EOL, $newCollections).PHP_EOL);
			replyUp("Removed $targetCollection.", $_POST['reqID']);
		}
		include 'collections.php';
	}
	
	//	OWNER POSTS
	if (isset($_COOKIE['owner_auth'])) {
		if (isset($_POST['Link'])) {
			$source = $_POST['Link'];
			$src = pathinfo($source);
			if (file_exists($source)) {
				$copyresult = copy($source, 'Workshop/'.$src['filename'].'.webp');
				if ($copyresult) {
					replyUp('Linked '.$src['filename'],$_POST['reqID']);
					// touch('Workshop/'.$filename.'.webp', filemtime($source), fileatime($source));
					$audioFile = $src['dirname'].'/'.$src['filename'].'.aac';
					if (file_exists($audioFile)) {
						copy($audioFile, 'Workshop/'.$src['filename'].'.aac');
					}
					file_put_contents('Workshop/'.$src['filename'].'.src',$source);
				} else
					replyDown('Failed to link '.$source,$_POST['reqID']);
			} else {
				replyDown('Failed to find source file '.$source,$_POST['reqID']);
			}
		}
		if (isset($_POST['LinkRemote'])) {
			$remoteUrl = $_POST['LinkRemote']; 

			// Extract basename from the reference URL
			$urlPath = parse_url($remoteUrl, PHP_URL_PATH);
			$basename = basename($urlPath);
			$filename = basename($urlPath, '.webp');

			if (isset($_FILES['sticker_blob'])) {
				$destPath = "Workshop/" . $basename;
				
				if (move_uploaded_file($_FILES['sticker_blob']['tmp_name'], $destPath)) {
					$msg = "Linked $basename";

					// Check for the accompanying audio blob
					if (isset($_FILES['audio_blob'])) {
						$audioBasename = $filename.'.aac';
						$audioDest = "Workshop/" . $audioBasename;

						if (move_uploaded_file($_FILES['audio_blob']['tmp_name'], $audioDest)) {
							$msg .= " (with audio $audioBasename)";
						} else {
							$msg .= " (ERROR: Audio upload failed)";
						}
					}
					replyUp($msg, $_POST['reqID']);
				} else {
					replyDown("ERROR: Failed to save $basename to Workshop", $_POST['reqID']);
				}
			} else {
				replyDown("ERROR: No binary data received for $basename", $_POST['reqID']);
			}
		}
		if (isset($_POST['Clear'])) {
			if ($_POST['Clear']=='Workshop') {
				$workshop_files = glob('Workshop/*.*');
				$count = 0;
				foreach ($workshop_files as $victim) {
					if (unlink($victim)) {
						$count++;
					} else {
						replyDown('Failed to delete '.$victim,$_POST['reqID']);
					}
				}
				if ($count==count($workshop_files)) {
					replyUp('Cleared Workshop by deleting '.$count.' files',$_POST['reqID']);
				} else {
					if ($count>0) {
						serverMsg('Partially cleared workshop by deleting '.$count.' files',$_POST['reqID']);
					} else {
						replyDown('Failed to clear workshop',$_POST['reqID']);
					}
				}
			}
			if ($_POST['Clear']=='ImgSeq') {
				$png = glob('Temp/png/*.png');
				$count = 0;
				foreach ($png as $file) {
					if (unlink($file)) {
						$count++;
					} else {
						replyDown('Failed to delete '.$file, $_POST['reqID'] ?? '');
					}
				}
				$webp = glob('Temp/webp/*.webp');
				foreach ($webp as $file) {
					if (unlink($file)) {
						$count++;
					} else {
						replyDown('Failed to delete '.$file, $_POST['reqID'] ?? '');
					}
				}
				if ($count>0) {
					replyUp('Cleared Image Sequences by deleting '.$count.' files', $_POST['reqID'] ?? '');
				}
			}
		}
		if (isset($_POST['indexMetadata'])) {
			$starttime = time();
			if (is_numeric($_POST['indexMetadata'])) {
				$startfrom = $_POST['indexMetadata'];
			} else {
				$startfrom = 0;
			}
			$checked=0;
			$updated=0;
			
			$metadata = file_exists('metadata.json') ? json_decode(file_get_contents('metadata.json'), true) : [];
			
			if ($startfrom >= count($allStickers)) $startfrom=0;
			if (count($allStickers)>$startfrom) {
				unset($resumeFrom);
				for ($i=$startfrom; $i<count($allStickers); $i++) {
					$candidate = $allStickers[$i];
					unset($stk);
					if ( !isset($metadata[$candidate['filename']]) || ( !isset($metadata[$candidate['filename']]['emojis']) || $metadata[$candidate['filename']]['emojis']===[] || !isset($metadata[$candidate['filename']]['compliance']) ) ) {

						//	webpinfo
						$webpinfoString = shell_exec('webpinfo "'.$candidate['dirname'].'/'.$candidate['filename'].'.webp"');
						$webpinfoArray = explode("\n", $webpinfoString);
						$stk['TotalDuration']=0;
						$stk['FrameCount']=0;
						if ($webpinfoArray) forEach($webpinfoArray as $line) {
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


						if (isset($stk['hasEXIF'])) if ($stk['hasEXIF'] == 1) {
							$RawSign = shell_exec('exiftool -u -b -Exif_0x5741 "'.$candidate['dirname'].'/'.$candidate['filename'].'.webp"');
							$Signature = json_decode($RawSign, true);
							
							// 1. Get the raw emoji data
							$rawEmojis = $Signature['emojis'] ?? [];

							// 2. Sanitize: If it's a string looking like "[☕, ☕]", turn it into a real array
							if (is_string($rawEmojis) && str_starts_with($rawEmojis, '[')) {
								// Remove the brackets and split by the comma-space separator
								$clean = trim($rawEmojis, "[] ");
								$emojiArray = ($clean !== "") ? explode(', ', $clean) : [];
							} else {
								// If it's already an array or a different format, handle it normally
								$emojiArray = is_array($rawEmojis) ? $rawEmojis : [];
							}
							
							unset($candidate['audioFile']);
							unset($audioFileSize);
							if ( (str_starts_with($candidate['filename'],'STK-')) && (strlen($candidate['filename'])==19) ) {
								$stkdate = substr($candidate['filename'],4,8);
								$stkindex = substr($candidate['filename'],-4,4);
								$audindex = sprintf("%04d", intval($stkindex) + 1);
								$audfile = 'WA-Media/WhatsApp Audio/AUD-'.$stkdate.'-WA'.$audindex.'.aac';
								if (file_exists($audfile)) $candidate['audioFile'] = $audfile;
								$audfilesent = 'WA-Media/WhatsApp Audio/Sent/AUD-'.$stkdate.'-WA'.$audindex.'.aac';
								if (file_exists($audfilesent)) $candidate['audioFile'] = $audfilesent;
								if (file_exists($candidate['dirname'].'/'.$candidate['filename'].'.aac'))
									$candidate['audioFile'] = $candidate['dirname'].'/'.$candidate['filename'].'.aac';
							} else {
								if (file_exists($candidate['dirname'].'/'.$candidate['filename'].'.aac'))
									$candidate['audioFile'] = $candidate['dirname'].'/'.$candidate['filename'].'.aac';
							}
							if (isset($candidate['audioFile'])) $audioFileSize = filesize($candidate['audioFile']);
							
							if (!str_starts_with($candidate['dirname'],'WA-Media')) {
								if (str_starts_with($candidate['dirname'],'Packs/')) {
									$dirname = substr($candidate['dirname'], 6);
								} else if (str_starts_with($candidate['dirname'],'PrivatePacks/')) {
									$dirname = substr($candidate['dirname'], 13);
								} else if (str_starts_with($candidate['dirname'],'User-Media/Stickers/')) {
									$dirname = substr($candidate['dirname'], 20);
								} else {
									$dirname = $candidate['dirname'];
								}
								if (str_starts_with($dirname,"_")) $dirname = substr($dirname, 1);
								if ($dirname) {
									if (!isset($audioFileSize)) 	$accessText = $candidate['filename'].' in '.$dirname;
									else							$accessText = $candidate['filename'].' in '.$dirname.' with audio ('.$audioFileSize.')';
								} else {
									if (!isset($audioFileSize)) 	$accessText = $candidate['filename'];
									else							$accessText = $candidate['filename'].' with audio ('.$audioFileSize.')';
								}
							} else {
								if (!isset($audioFileSize)) 		$accessText = $Signature['accessibility-text'] ?? $candidate['filename'].' in '.$Signature['sticker-pack-name'];
								else								$accessText = $Signature['accessibility-text'] ?? $candidate['filename'].' in '.$Signature['sticker-pack-name'].' with audio ('.$audioFileSize.')';
							}
							
							unset($compliance);
							unset($noncompliancereason);
							
							if ( ($stk['FileSize']<=512000)&&($stk['CanvasSize']=="512 x 512")&&($stk['TotalDuration']<=10000) ) {
								// Sticker is compliant
								$compliance = true;
								$noncompliancereason = "";
							} else {
								// Sticker is non compliant
								$compliance = false;
								if ($stk['FileSize']>512000) $noncompliancereason = "File size over 512000: ".$stk['FileSize']." ";
								if ($stk['CanvasSize']!="512 x 512") $noncompliancereason .= "Canvas size different than 512 x 512: ".$stk['CanvasSize']." ";
								if ($stk['TotalDuration']>10000) $noncompliancereason .= "Total Duration over 10000: ".$stk['TotalDuration']." ";
							}
							
							$entry = [
								'accessibility-text'		=> $accessText,
								'sticker-pack-name'			=> $Signature['sticker-pack-name'],
								'sticker-pack-publisher'	=> $Signature['sticker-pack-publisher'],
								'emojis'					=> $emojiArray,
								'compliance'				=> $compliance,
								'noncompliancereason'		=> $noncompliancereason
							];

							$metadata[$candidate['filename']] = $entry;
							
							$updated++;
							if ($accessText)
								if ($emojiArray)
									serverMsg('Updated '.$accessText.' with emojis '.implode(",",$emojiArray), $_POST['reqID']);
						}
					}
					$checked++;
					if ( time() >= ($starttime+5) ) {
						// Save the updated array
						file_put_contents('metadata.json', json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
						$resumeFrom = $i;
						break;
					}
				}
				// Save the updated array
				file_put_contents('metadata.json', json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
			}
			if (isset($resumeFrom)) serverMsg('Started at '.$startfrom.', checked '.$checked.' and updated '.$updated.'. Resume from index  '.$resumeFrom, $_POST['reqID']);
			else if ( $checked + $startfrom == count($allStickers) ) replyUp('Completed!', $_POST['reqID']);
		}
		if (isset($_POST['Disassemble'])) {
			$filename = $_POST['Disassemble'];
			$FrameCount = $_REQUEST['FrameCount'];
			$first = $_REQUEST['first'];
			$FFD = $_REQUEST['FFD'];
			if (!empty($FFD)) $FrameCount--;
			if ($first=='last') {
				for($j=2;$j<=$FrameCount;$j++) {
					$currIndex = ($j-1);
					if ( strlen($currIndex) < strlen($FrameCount) ) {
						$diffIndex = strlen($FrameCount) - strlen($currIndex);
						for ($k=1;$k<=$diffIndex;$k++) {
							$currIndex = "0".$currIndex;
						}
					}
					shell_exec('webpmux -get frame '.$j.' "Workshop/'.$filename.'.webp" -o "Temp/webp/'.$filename.$currIndex.'.webp"');
				}
				shell_exec('webpmux -get frame 1 "Workshop/'.$filename.'.webp" -o "Temp/webp/'.$filename.$FrameCount.'.webp"');
				$cmdout = 'Disassembled '.$filename.' into '.$FrameCount.' frames';
			} else {
				$webpinfoString = shell_exec('webpinfo "Workshop/'.$filename.'.webp"');
				$webpinfo = explode("\n", $webpinfoString);
				unset($framemap);
				$counter=0;
				forEach($webpinfo as $line) {
					$pair = explode(":", $line);
					if ($pair[0]=='  Offset_X') {
						$counter+=1;
						$framemap[$counter] = trim($pair[1]);
					}
					if ($pair[0]=='  Offset_Y') {
						$framemap[$counter] = $framemap[$counter].'+'.trim($pair[1]);
					}
					if ($pair[0]=='  Dispose') {
						$framemap[$counter] = $framemap[$counter].'+'.trim($pair[1]);
					}
					if ($pair[0]=='  Blend') {
						if (trim($pair[1])==1) {
							$framemap[$counter] = $framemap[$counter].'-b'.PHP_EOL;
						} else {
							$framemap[$counter] = $framemap[$counter].PHP_EOL;
						}
					}
				}
				
				for($j=1;$j<=$FrameCount;$j++) {
					$currIndex = ($j);
					if ( strlen($currIndex) < strlen($FrameCount) ) {
						$diffIndex = strlen($FrameCount) - strlen($currIndex);
						for ($k=1;$k<=$diffIndex;$k++) {
							$currIndex = "0".$currIndex;
						}
					}
					shell_exec('webpmux -get frame '.$j.' "Workshop/'.$filename.'.webp" -o "Temp/webp/'.$filename.$currIndex.'.webp"');
				}
				$cmdout = 'Disassembled '.$filename.' into '.$FrameCount.' frames';
				
				if (isset($framemap)) {
					$handle = fopen('Workshop/'.$filename.'_framemap.txt','w');
					forEach ($framemap as $fields) {
						if ($fields!='0+0+1'.PHP_EOL) {
							fwrite($handle,$fields);
						}
					}
					fclose($handle);

					if (filesize('Workshop/'.$filename.'_framemap.txt')>0) {
						$cmdout = 'Disassembled '.$filename.' into '.$FrameCount.' frames and saved framemap';
					} else {
						unlink('Workshop/'.$filename.'_framemap.txt');
					}
				}
			}
			replyUp($cmdout,$_POST['reqID']);
			echo'<div id=imgseq>';
				include 'imgseq.php';
			echo'</div>';
		}
		if (isset($_POST['Unlink'])) {
			if (unlink('Workshop/'.$_POST['Unlink'].'.webp')) {
				replyUp('Unlinked '.$_POST['Unlink'],$_POST['reqID']);
				if (file_exists('Workshop/'.$_POST['Unlink'].'.aac')) unlink('Workshop/'.$_POST['Unlink'].'.aac');
				if (file_exists('Workshop/'.$_POST['Unlink'].'.src')) unlink('Workshop/'.$_POST['Unlink'].'.src');
				if (file_exists('Workshop/'.$_POST['Unlink'].'_framemap.txt')) unlink('Workshop/'.$_POST['Unlink'].'_framemap.txt');
			} else {
				replyDown('Failed to unlink '.$_POST['Unlink'],$_POST['reqID']);
			}
		}
		if (isset($_POST['ToggleFav'])) {
			$filename = $_POST['ToggleFav'];
			$source = $_POST['src'];
			
			if (str_starts_with($source,'Workshop')) {
				if (file_exists('Workshop/'.$filename.'.src')) {
					$SRC = file_get_contents('Workshop/'.$filename.'.src');
					if ( str_starts_with($SRC,'phone') )
						serverMsg('Phone files are temporary and should not be favorited', $_POST['reqID']);
					else
						$source = $SRC;
				}
			}
			if (file_exists('Favorites/'.$filename.'.fav')) {
				// Remove from Favorites
				if (unlink('Favorites/'.$filename.'.fav')) {
					replyUp($filename.' removed from favorites',$_POST['reqID']);
				} else
					replyDown('Failed to remove '.$filename.' from favorites',$_POST['reqID']);
			} else {
				// Add to Favorites
				if (file_exists($source)) {
					file_put_contents('Favorites/'.$filename.'.fav',$source);
					replyUp($filename.' added to favorites',$_POST['reqID']);
				} else {
					replyDown('Source does not exist',$_POST['reqID']);
				}
			}
		}
		if (isset($_POST['SaveEmojis'])) {
			$filename = $_POST['SaveEmojis'];
			$emojis=[];
			if (isset($_REQUEST['emoji1'])) {
				$emoji1 = trim($_REQUEST['emoji1'],' ');
				if (!empty($emoji1)) $emojis[]=$emoji1;
			}
			if (isset($_REQUEST['emoji2'])) {
				$emoji2 = trim($_REQUEST['emoji2'],' ');
				if (!empty($emoji2)) $emojis[]=$emoji2;
			}
			if (isset($_REQUEST['emoji3'])) {
				$emoji3 = trim($_REQUEST['emoji3'],' ');
				if (!empty($emoji3)) $emojis[]=$emoji3;
			}
			// $timestamp = ltrim(date("h:i"), "0") .' '. strtoupper(date("a"));
			if (!empty($emojis)) {
				// 1. Load existing metadata
				$metadataFile = 'metadata.json';
				$metadata = [];
				if (file_exists($metadataFile)) {
					$metadata = json_decode(file_get_contents($metadataFile), true);
				}

				// 2. Update the specific sticker entry
				// Assumes metadata is keyed by filename or contains an array of sticker objects
				$metadata[$filename]['emojis'] = $emojis;
				$emojisString="";
				foreach($emojis as $emj) {
					if ($emojisString=="")	$emojisString = $emj;
					else					$emojisString = $emojisString.','.$emj;
				}

				// 3. Save back to metadata.json
				if (file_put_contents($metadataFile, json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT))) {
					replyUp('Updated metadata.json with emojis '.$emojisString.' for '.$filename, $_POST['reqID']);
				} else {
					replyDown('Failed to update metadata.json for '.$filename, $_POST['reqID']);
				}
			} else {
				// Logic for clearing emojis
				if (file_exists('metadata.json')) {
					$metadata = json_decode(file_get_contents('metadata.json'), true);
					if (isset($metadata[$filename]['emojis'])) {
						unset($metadata[$filename]['emojis']);
						file_put_contents('metadata.json', json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
						replyUp('Cleared emojis from metadata for '.$filename, $_POST['reqID']);
					}
				}
			}		
		}
		if (isset($_POST['UpdateEXIF'])) {
			$filename = $_POST['UpdateEXIF'];
			$pathfilename = 'Workshop/'.$filename.'.webp';

			// 1. RE-READ the file fresh to preserve any data not in the form
			$sticker = getStickerInfo($pathfilename);
			$currentExif = $sticker['EXIF'] ?? [];

			// 2. PROCESS the data from the form
			$formExif = $_POST['exif_data'] ?? [];

			foreach ($formExif as $key => $value) {
				$trimmedValue = trim($value);
				
				if ($trimmedValue === "") {
					// If the user cleared the field in the UI, remove the key entirely
					unset($currentExif[$key]);
				} else {
					// Otherwise, update or add the value
					$currentExif[$key] = $trimmedValue;
				}
			}

			// 3. HANDLE Emojis (existing logic)
			$currentExif['emojis'] = array_values(array_filter($_POST['emoji_slots'], 'trim'));
			
			// Safety check: if emojis is empty, you might want to unset it too
			if (empty($currentExif['emojis'])) unset($currentExif['emojis']);

			// 4. Proceed with Binary Assembly
			$newsignature = json_encode($currentExif, JSON_UNESCAPED_SLASHES);
			$len = strlen($newsignature);
			
			// Tiff Header and WebP EXIF structure
			$bin = pack('C14', 0x49, 0x49, 0x2A, 0x00, 0x08, 0x00, 0x00, 0x00, 0x01, 0x00, 0x41, 0x57, 0x07, 0x00);
			$bin .= pack('V', $len);
			$bin .= pack('V', 0x16);
			$bin .= $newsignature;
			
			$exifPath = 'Temp/'.$filename.'.exif';
			file_put_contents($exifPath, $bin);

			$cmd = "webpmux -set exif \"$exifPath\" \"$pathfilename\" -o \"$pathfilename\"";
			exec($cmd, $output, $returnVar);

			if ($returnVar === 0) {
				// Clean up the temp file
				if (file_exists($exifPath)) unlink($exifPath);
				replyUp("Signature updated for ".$filename, $_POST['reqID'] ?? '');
			} else {
				replyDown("Failed to update signature for ".$filename, $_POST['reqID'] ?? '');
			}
		}
		if (isset($_POST['DeletePNGSequence'])) {
			$SeqName = $_POST['DeletePNGSequence'];
			$SeqFiles = glob('Temp/png/'.$SeqName.'*.png');
			$count = 0;
			foreach($SeqFiles as $file) {
				if (unlink($file)) $count += 1;
				else replyDown('Failed to delete '.$file);
			}
			replyUp('Deleted PNG sequence '.$SeqName.' ('.$count.' files)', $_POST['reqID'] ?? '');
			echo'<div id=imgseq>';
				include 'imgseq.php';
			echo'</div>';
		}
		if (isset($_POST['DeleteWebPSequence'])) {
			$SeqName = $_POST['DeleteWebPSequence'];
			$SeqFiles = glob('Temp/webp/'.$SeqName.'*.webp');
			$count = 0;
			foreach($SeqFiles as $file) {
				if (unlink($file)) {
					$count += 1;
				} else {
					replyDown('Failed to delete '.$file, $_POST['reqID'] ?? '');
				}
			}
			replyUp('Deleted WebP sequence '.$SeqName.' ('.$count.' files)', $_POST['reqID'] ?? '');
			echo'<div id=imgseq>';
				include 'imgseq.php';
			echo'</div>';
		}
		if (isset($_POST['Convert2WebP'])) {
			$SeqName = $_POST['Convert2WebP'];
			$Quality = $_REQUEST['Quality'];
			
			$SeqFiles = glob('Temp/png/'.$SeqName.'*.png');
			if (count($SeqFiles)==1) {
				// Static Sticker
				$png = pathinfo($SeqFiles[0]);
				$cmd = shell_exec('cwebp -q '.$Quality.' -alpha_q '.$Quality.' -m 6 -mt -quiet "Temp/png/'.$png['basename'].'" -o "Workshop/'.$png['filename'].'.webp"');
				replyUp('Success!', $_POST['reqID'] ?? '');
				serverMsg('Created Static Sticker '.$cmd, '');
			} else {
				// Animated Sticker
				forEach($SeqFiles as $png) {
					$pngbase = basename($png);
					$pngfile = basename($pngbase, '.png');
					shell_exec('cwebp -q '.$Quality.' -alpha_q '.$Quality.' -m 6 -mt -quiet "Temp/png/'.$pngbase.'" -o "Temp/webp/'.$pngfile.'.webp"');
				}
				replyUp('Success!', $_POST['reqID'] ?? '');
				serverMsg(count($SeqFiles).' PNG images were converted to WebP at '.$Quality.' quality'.$cmd, '');
			}
			echo'<div id=imgseq>';
				include 'imgseq.php';
			echo'</div>';
		}
		if (isset($_POST['Assemble'])) {
			$sequence = $_POST['Assemble'];
			$FPS = $_REQUEST['FPS'];
			$FirstFrame = $_REQUEST['FirstFrame'];
			$FirstFrameDuration = $_REQUEST['FirstFrameDuration'];
			$showcmd = $_REQUEST['ShowCommand'];
			
			$duration = (1000 / $FPS);
			if (round($duration)==$duration) {
				// doesnt need sync adjustment
				$sync_every = 0;
			} else {
				// needs sync adjustment
				if ( round(($duration - floor($duration)),6) == round(1/3,6) ) {
					//serverMsg('Sync of 1/3');
					$duration = round($duration);
					$sync_every = 3;
					$duration_adjust = $duration + 1;
				} else if ( round(($duration - floor($duration)),6) == round(2/3,6) ) {
					//serverMsg('Sync of 2/3');
					$duration = round($duration);
					$sync_every = 3;
					$duration_adjust = $duration - 1;
				} else if ( round(($duration - floor($duration)),6) == round(1/2,6) ) {
					$duration = floor($duration);
					$sync_every = 2;
					$duration_adjust = $duration + 1;
				} else if ( round(($duration - floor($duration)),6) == round(1/9,6) ) {
					$duration = round($duration);
					$sync_every = 9;
					$duration_adjust = $duration + 1;
				} else {
					// still to code other sync values...
					serverMsg('Sync settings not coded yet', '');
					
					$duration = round($duration);
					$sync_every = 0;
				}
			}
			
			if (isset($FirstFrameDuration)&&(!empty($FirstFrameDuration))) {
				$duration1st = $FirstFrameDuration;
			} else {
				$duration1st = $duration;
			}
			
			$frames = glob('Temp/webp/'.$sequence.'*.webp');
			$sync=$sync_every;
			$frames2process = count($frames);

			if (file_exists('Workshop/'.$sequence.'_framemap.txt')) {
				$framemapstring = file_get_contents('Workshop/'.$sequence.'_framemap.txt');
				$framemap = explode("\n",$framemapstring);
				unset($framemap[count($framemap)-1]);
			}

			$muxcmd='';
			if ($FirstFrame == 'last') {
				if (!isset($framemap)) {
					$muxcmd = $muxcmd.'-frame "'.$frames[(count($frames)-1)].'" +'.$duration1st.'+0+0+1 ';
				} else {
					$muxcmd = $muxcmd.'-frame "'.$frames[(count($frames)-1)].'" +'.$duration1st.'+'.$framemap[(count($frames)-1)].' ';
				}
				$frames2process--;
				$sync--;
			}
			for ($i=0;$i<$frames2process;$i++) {
				if ( ($i==0)&&($FirstFrame != 'last') ) {
					if (!isset($framemap)) {
						$muxcmd = $muxcmd.'-frame "'.$frames[$i].'" +'.$duration1st.'+0+0+1 ';
					} else {
						$muxcmd = $muxcmd.'-frame "'.$frames[$i].'" +'.$duration1st.'+'.$framemap[$i].' ';
					}
				}
				else if (($i+1)==$sync) {
					if (!isset($framemap)) {
						$muxcmd = $muxcmd.'-frame "'.$frames[$i].'" +'.$duration_adjust.'+0+0+1 ';
					} else {
						$muxcmd = $muxcmd.'-frame "'.$frames[$i].'" +'.$duration_adjust.'+'.$framemap[$i].' ';
					}
					$sync=$sync+$sync_every;
				} else {
					if (!isset($framemap)) {
						$muxcmd = $muxcmd.'-frame "'.$frames[$i].'" +'.$duration.'+0+0+1 ';
					} else {
						$muxcmd = $muxcmd.'-frame "'.$frames[$i].'" +'.$duration.'+'.$framemap[$i].' ';
					}
				}
			}
			if (isset($FirstFrameDuration)&&(!empty($FirstFrameDuration))) {
				if ($FirstFrame == 'last') {
					if (!isset($framemap)) {
						$muxcmd = $muxcmd.'-frame "'.$frames[(count($frames)-1)].'" +'.$duration.'+0+0+1 ';
					} else {
						$muxcmd = $muxcmd.'-frame "'.$frames[(count($frames)-1)].'" +'.$duration.'+'.$framemap[(count($frames)-1)].' ';
					}
				} else {
					if (!isset($framemap)) {
						$muxcmd = $muxcmd.'-frame "'.$frames[0].'" +'.$duration.'+0+0+1 ';
					} else {
						$muxcmd = $muxcmd.'-frame "'.$frames[0].'" +'.$duration.'+'.$framemap[0].' ';
					}
				}
			}
			
			$fullmuxcmd = 'webpmux '.$muxcmd.' -o "Workshop'.$foldsep.$sequence.'.webp"';
			shell_exec($fullmuxcmd);
			$timestamp = date("j/m/Y H:i:s");
			$logentry = $timestamp.';created;'.$FPS.';'.$FirstFrame.';'.$FirstFrameDuration.';';
			file_put_contents('Logs/'.$sequence.'.log', $logentry);
			replyUp($sequence.' assembled with '.$FPS.'fps, first frame '.$FirstFrame.' lasting "'.$FirstFrameDuration.'"', $_POST['reqID'] ?? '');

			if ($showcmd=="true") {	serverMsg('Full mux command: '.$fullmuxcmd, ''); }
		}	
		if (isset($_POST['updateLastSticker'])) {
			$filename = $_POST['updateLastSticker'];
			$lastSticker = $_POST['lastSticker'];
			
			$wasp='/data/data/com.whatsapp/files/Stickers/';
			$ando='/storage/emulated/0/Download/';
			
			// file_put_contents('debug.txt', "Received: " . $_POST['lastSticker'] . PHP_EOL, FILE_APPEND);
			$push = 'adb -s emulator-5554 push "Workshop/' . $filename . '.webp" "' . $ando . $lastSticker . '"';
			$pushResult = shell_exec($push);
			// serverMsg('Push:'.$push.'<br>Result:'.$pushResult, '');
			$move = 'adb -s emulator-5554 shell su -c "mv -v -f \"'.$ando.$lastSticker.'\" \"'.$wasp.$lastSticker.'\""';
			$moveResult = shell_exec($move);
			// serverMsg('Move:'.$move.'<br>Result:'.$moveResult, '');
			if (str_starts_with($moveResult,'mv ')) {
				$chmod = 'adb -s emulator-5554 shell su -c "chmod a+r \"'.$wasp.$lastSticker.'\""';
				$chmodResult = shell_exec($chmod);
				if (!empty($chmodResult)) replyDown('Issue updating file permissions: '.$chmodResult, $_POST['reqID']);
				else {
					replyUp('Sticker updated on phone.', $_POST['reqID']);
					copyNewSticker($filename);
				}
			} else {
				// shell_exec('adb -s emulator-5554 shell su -c rm '.$ando.$lastSticker);
				replyDown('Failed to update Sticker on phone: '.$moveResult, $_POST['reqID']);
			}
			
		}
		if (isset($_POST['CreateTrayIcon'])) {
			$filename = $_POST['CreateTrayIcon'];
			$sourcePath = 'Workshop/'.$filename.'.src';
			if (file_exists($sourcePath)) {
				$source = trim(file_get_contents($sourcePath));
			}
			$trayPathTmp = 'Workshop/_tray.webp';
			$trayPath = 'Workshop/_tray.png';
			shell_exec("webpmux -get frame 1 \"Workshop/$filename.webp\" -o $trayPathTmp 2>&1");
			$trayCreation = shell_exec("dwebp $trayPathTmp -resize 96 96 -o $trayPath 2>&1");
			replyUp($trayCreation, $_POST['reqID']);
			if (file_exists($trayPath)) {
				if (file_exists($trayPathTmp)) unlink($trayPathTmp);
			}
			if ($source) {
				$src = pathinfo($source);
				$folder = $src['dirname'];
				if (rename('Workshop/_tray.png', $folder.'/_tray.png')) {
					$image_data_versionFile = $folder.'/image_data_version.txt';
					if (file_exists($image_data_versionFile)) {
						$image_data_version = file_get_contents($image_data_versionFile);
						$image_data_version++;
						file_put_contents($image_data_versionFile, $image_data_version);
					}
					serverMsg('Tray icon moved to package '.$folder, $_POST['reqID']);
				}
			}
		}
		if (isset($_POST['MoveIntoPackage'])) {
			$packName = $_POST['MoveIntoPackage'] ?? '';
			$stickers = glob('Workshop/*.webp');
			// sort by modified date ascending
			usort($stickers,'mdate_asc');
			
			if ($packName === '') {
				serverMsg("New package name cannot be empty, using default name", '');
				$packName == date("Y-m");
			}
			
			$targetDir = 'Packs/'.$packName;
			if (!is_dir($targetDir)) mkdir($targetDir);

			// Move .webp files and sanitize Workshop
			foreach ($stickers as $stk) {
				$fn = basename($stk, '.webp');
				if ( (str_starts_with($fn,'STK-')) && (strlen($fn)==19) ) {
					$stkdate = substr($fn,4,8);
					$stkindex = substr($fn,-4,4);
					$audindex = sprintf("%04d", intval($stkindex) + 1);
					$audfile = 'WA-Media/WhatsApp Audio/AUD-'.$stkdate.'-WA'.$audindex.'.aac';
					if (file_exists($audfile)) $audioFile = $audfile;
					$audfilesent = 'WA-Media/WhatsApp Audio/Sent/AUD-'.$stkdate.'-WA'.$audindex.'.aac';
					if (file_exists($audfilesent)) $audioFile = $audfilesent;
					$localaudfile = 'Workshop/AUD-'.$stkdate.'-WA'.$audindex.'.aac';
					if (file_exists($localaudfile)) $audioFile = $localaudfile;
				} else {
					$audfile = 'WA-Media/WhatsApp Audio/'.$fn.'.aac';
					if (file_exists($audfile)) $audioFile = $audfile;
					$localaudfile = 'Workshop/'.$fn.'.aac';
					if (file_exists($localaudfile)) $audioFile = $localaudfile;
				}
				
				// 1. Move the sticker and audio to the new pack folder
				rename($stk, $targetDir.'/'.$fn.'.webp');
				if (isset($audioFile)) if (file_exists($audioFile)) {
					if (str_starts_with($audioFile, 'Workshop/'))
						rename($audioFile, $targetDir.'/'.$fn.'.aac');
					else
						copy($audioFile, $targetDir.'/'.$fn.'.aac');
				}
				if (file_exists('Workshop/_tray.png'))
					rename('Workshop/_tray.png', $targetDir.'/_tray.png');
				
				// 2. Clean up
				if (file_exists('Workshop/'.$fn.'.src')) unlink('Workshop/'.$fn.'.src');
				$frameMap = 'Workshop/'.$fn.'_framemap.txt';
				if (file_exists($frameMap)) unlink($frameMap);
			}

			$image_data_versionFile = $targetDir.'/image_data_version.txt';
			if (file_exists($image_data_versionFile)) {
				$image_data_version = file_get_contents($image_data_versionFile);
				$image_data_version++;
				file_put_contents($image_data_versionFile, $image_data_version);
			} else {
				$image_data_version = 0;
				file_put_contents($image_data_versionFile, $image_data_version);
			}
			
			$trayPath = $targetDir.'/_tray.png';
			if (!file_exists($trayPath)) {
				$firstSticker = $targetDir.'/'.basename($stickers[0]);
				$trayPathTmp = $targetDir.'/_tray.webp';
				shell_exec("webpmux -get frame 1 $firstSticker -o $trayPathTmp 2>&1");
				$trayCreation = shell_exec("dwebp $trayPathTmp -resize 96 96 -o $trayPath 2>&1");
				// serverMsg($trayCreation, $_POST['reqID']);
				if (file_exists($trayPath)) {
					if (file_exists($trayPathTmp)) {
						unlink($trayPathTmp);
					}
				}
			}
			replyUp("Stickers moved into package $targetDir", $_POST['reqID']);
		}	
		if (isset($_POST['MoveIntoPrivatePackage'])) {
			$packName = $_POST['MoveIntoPrivatePackage'] ?? '';
			$stickers = glob('Workshop/*.webp');
			// sort by modified date ascending
			usort($stickers,'mdate_asc');
			
			if ($packName === '') {
				serverMsg("New package name cannot be empty, using default name", '');
				$packName == date("Y-m");
			}
			
			$targetDir = 'PrivatePacks/'.$packName;
			if (!is_dir($targetDir)) mkdir($targetDir);

			// Move .webp files and sanitize Workshop
			foreach ($stickers as $stk) {
				$fn = basename($stk, '.webp');
				if ( (str_starts_with($fn,'STK-')) && (strlen($fn)==19) ) {
					$stkdate = substr($fn,4,8);
					$stkindex = substr($fn,-4,4);
					$audindex = sprintf("%04d", intval($stkindex) + 1);
					$audfile = 'WA-Media/WhatsApp Audio/AUD-'.$stkdate.'-WA'.$audindex.'.aac';
					if (file_exists($audfile)) $audioFile = $audfile;
					$audfilesent = 'WA-Media/WhatsApp Audio/Sent/AUD-'.$stkdate.'-WA'.$audindex.'.aac';
					if (file_exists($audfilesent)) $audioFile = $audfilesent;
					$localaudfile = 'Workshop/AUD-'.$stkdate.'-WA'.$audindex.'.aac';
					if (file_exists($localaudfile)) $audioFile = $localaudfile;
				} else {
					$audfile = 'WA-Media/WhatsApp Audio/'.$fn.'.aac';
					if (file_exists($audfile)) $audioFile = $audfile;
					$localaudfile = 'Workshop/'.$fn.'.aac';
					if (file_exists($localaudfile)) $audioFile = $localaudfile;
				}
				
				// 1. Move the sticker and audio to the new pack folder
				rename($stk, $targetDir.'/'.$fn.'.webp');
				if (isset($audioFile)) if (file_exists($audioFile)) {
					if (str_starts_with($audioFile, 'Workshop/'))
						rename($audioFile, $targetDir.'/'.$fn.'.aac');
					else
						copy($audioFile, $targetDir.'/'.$fn.'.aac');
				}
				if (file_exists('Workshop/_tray.png'))
					rename('Workshop/_tray.png', $targetDir.'/_tray.png');
				
				// 2. Clean up
				if (file_exists('Workshop/'.$fn.'.src')) unlink('Workshop/'.$fn.'.src');
				$frameMap = 'Workshop/'.$fn.'_framemap.txt';
				if (file_exists($frameMap)) unlink($frameMap);
			}

			$image_data_versionFile = $targetDir.'/image_data_version.txt';
			if (file_exists($image_data_versionFile)) {
				$image_data_version = file_get_contents($image_data_versionFile);
				$image_data_version++;
				file_put_contents($image_data_versionFile, $image_data_version);
			} else {
				$image_data_version = 0;
				file_put_contents($image_data_versionFile, $image_data_version);
			}
			
			$trayPath = $targetDir.'/_tray.png';
			if (!file_exists($trayPath)) {
				$firstSticker = $targetDir.'/'.basename($stickers[0]);
				$trayPathTmp = $targetDir.'/_tray.webp';
				shell_exec("webpmux -get frame 1 $firstSticker -o $trayPathTmp 2>&1");
				$trayCreation = shell_exec("dwebp $trayPathTmp -resize 96 96 -o $trayPath 2>&1");
				// serverMsg($trayCreation, $_POST['reqID']);
				if (file_exists($trayPath)) {
					if (file_exists($trayPathTmp)) {
						unlink($trayPathTmp);
					}
				}
			}
			replyUp("Stickers moved into package $targetDir", $_POST['reqID']);
		}	
		if (isset($_POST['Update'])) {
			if ($_POST['Update']=="Server") {
				$command = "zrok copy --sync https://wastkslatest.share.zrok.io .. 2>&1";

				$output = shell_exec($command);
				
				$lines = array_filter(explode("\n", trim($output)));
				$lastLine = end($lines);

				// Check if the final line indicates success
				if (stripos($lastLine, "copy complete!") !== false) {
					replyUp('Updated server: '.$lastLine, $_POST['reqID']);
				} else {
					replyDown('Update failed: '.$output, $_POST['reqID']);
				}
			}
			else {
				$filename = $_POST['Update'];
				$sourcePath = 'Workshop/'.$filename.'.src';
				if (file_exists($sourcePath)) $source = trim(file_get_contents($sourcePath));
				if (file_exists($source)) {
					if (copy('Workshop/'.$filename.'.webp', $source))
						replyUp('Updated '.$source, $_POST['reqID']);
					else
						replyDown('Failed to update '.$source, $_POST['reqID']);
				}
			}
		}
		if (isset($_POST['Refresh'])) {
			if ( $_POST['Refresh']=='Manifest' || $_POST['Refresh']=='Stickers') {
				include 'owner-server.php';
			}
		}
		if (isset($_POST['Prepare']) && $_POST['Prepare'] == 'StickerPacks') {
			$meta = json_decode(file_get_contents('metadata.json'), true);
			
			$ownerNameFile = '.owner/name.txt';
			if (file_exists($ownerNameFile)) $ownerName = file_get_contents($ownerNameFile);
			$deviceNameFile = '.owner/device.txt';
			if (file_exists($deviceNameFile)) $deviceName = file_get_contents($deviceNameFile);
			$publisherFile = '.owner/publisher.txt';
			if (file_exists($publisherFile)) $publisher = trim(file_get_contents($publisherFile));
			else $publisher = $ownerName.$deviceName.'WAStks';
			
			unset($package);
			$PublicPacks = glob('Packs/*', GLOB_ONLYDIR);
			rsort($PublicPacks);
			if (count($PublicPacks)) {
				foreach($PublicPacks as $i => $stickerpack) {
					$folder = basename($stickerpack);
					$image_data_versionFile = $stickerpack.'/image_data_version.txt';
					if (file_exists($image_data_versionFile)) {
						$image_data_version = file_get_contents($image_data_versionFile);
					} else {
						$image_data_version = 0;
						file_put_contents($image_data_versionFile, $image_data_version);
					}
					$package[$i]=array();
					$package[$i]['identifier']=$folder;
					$package[$i]['name']=$folder;
					$package[$i]['publisher']=$publisher;
					$package[$i]['tray_image_file']='_tray.png';
					$package[$i]['image_data_version']=$image_data_version;
					if ($publisher=="👻") {
						$package[$i]['publisher_email']="edu.spuqui@gmail.com";
						$package[$i]['publisher_website']="https://www.youtube.com/@SpuquiWAStickers";
					}
					$package[$i]['animated_sticker_pack']=true;
					$package[$i]['stickers']=array();

					$publicfiles = glob('Packs/'.$folder.'/*.webp');
					sort($publicfiles);
					if (!file_exists($stickerpack.'/_tray.png')) {
						$firstSticker = $publicfiles[0];
						$trayPathTmp = $stickerpack.'/_tray.webp';
						$trayPath = $stickerpack.'/_tray.png';
						
						// Extract first frame using your existing libwebp requirement
						shell_exec("webpmux -get frame 1 $firstSticker -o $trayPathTmp 2>&1");
						$trayCreation = shell_exec("dwebp $trayPathTmp -resize 96 96 -o $trayPath 2>&1");
						serverMsg($trayCreation, $_POST['reqID']);

						if (file_exists($trayPath)) {
							if (file_exists($trayPathTmp)) unlink($trayPathTmp);
						}
					}

					forEach($publicfiles as $stk) {
						$stkbasename = basename($stk);
						$stkfilename = basename($stk, '.webp');
						$emojis = $meta[$stkfilename]['emojis'] ?? [];
						// Check if sticker is compliant
						if (isset($meta[$stkfilename]['compliance'])) {
							if ($meta[$stkfilename]['compliance']) {
								$package[$i]['stickers'][] = array(
									'image_file' => $stkbasename,
									'accessibility_text' => $stkfilename.' in '.$folder,
									'emojis' => $emojis
								);
							} else {
								serverMsg($folder."/".$stkbasename." excluded due to non-compliance: ".$meta[$stkfilename]['noncompliancereason'], "");
							}
						} else {
							if (checkStickerCompliance($stk)) {
								$package[$i]['stickers'][] = array(
									'image_file' => $stkbasename,
									'accessibility_text' => $stkfilename.' in '.$folder,
									'emojis' => $emojis
								);
							} else {
								$stkinfo = getStickerInfo($stk);
								if ($stkinfo['CanvasSize']!="512 x 512")
									serverMsg($folder."/".$stkbasename." excluded due to non-compliance in Canvas Size: ".$stkinfo['CanvasSize'], "");
								if ($stkinfo['FileSize']>"512000")
									serverMsg($folder."/".$stkbasename." excluded due to non-compliance in File Size: ".$stkinfo['FileSize'], "");
								if ($stkinfo['TotalDuration']>"10000")
									serverMsg($folder."/".$stkbasename." excluded due to non-compliance in Total Duration: ".$stkinfo['TotalDuration'], "");
							}
						}
					}
				}
			}
			$androidMediaBase = '/storage/emulated/0/Android/media';
			if (isset($package)) {
				$output=array();
				$output['sticker_packs']=$package;
				$outputJSON = json_encode($output, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
				if ($f=fopen('Packs/contents.json', 'w')) {
					fwrite($f, $outputJSON);
					fclose($f);
					if (!is_dir($androidMediaBase)) {
						serverMsg('Public Packs processed.', $_POST['reqID']);
					}
				}
			} else {
				if (file_exists('Packs/contents.json')) {
					unlink('Packs/contents.json');
				}
				replyUp('No Public Packs to process.', $_POST['reqID']);
			}

			$appFolder = $androidMediaBase . '/app.spuqui.wastks';
			$targetPacks = $appFolder . '/packs';
			$sourcePacks = 'Packs/';				// Local folder in ~/WAStks/

			// Check if we are on an Android device with Media storage access
			if (is_dir($androidMediaBase)) {
				if (!is_dir($appFolder)) mkdir($appFolder, 0755, true);
				if (!is_dir($targetPacks)) mkdir($targetPacks, 0755, true);

				// Synchronize Files
				// Using rsync if available is faster, otherwise fallback to shell copy
				// -a: archive, -u: update (only newer), -r: recursive
				$cmd = "rsync -a --delete \"$sourcePacks\" \"$targetPacks/\" 2>&1";
				exec($cmd, $output, $returnVar);

				if ($returnVar !== 0) {
					// Fallback: If rsync isn't in the environment, use standard cp
					$cmd = "rm -rf \"$targetPacks/\"* && cp -r \"$sourcePacks\"* \"$targetPacks/\" 2>&1";
					exec($cmd, $output, $returnVar);
				}

				if ($returnVar === 0) {
					// Success logging
					serverMsg('Public packs processed and deployed.', $_POST['reqID']);
				}
			}
			
			unset($package);
			$PrivatePacks = glob('PrivatePacks/*', GLOB_ONLYDIR);
			rsort($PrivatePacks);
			if (count($PrivatePacks)) {
				foreach($PrivatePacks as $i => $stickerpack) {
					$folder = basename($stickerpack);
					$image_data_versionFile = $stickerpack.'/image_data_version.txt';
					if (file_exists($image_data_versionFile)) {
						$image_data_version = file_get_contents($image_data_versionFile);
					} else {
						$image_data_version = 0;
						file_put_contents($image_data_versionFile, $image_data_version);
					}
					$package[$i]=array();
					$package[$i]['identifier']=$folder;
					$package[$i]['name']=$folder;
					$package[$i]['publisher']=$publisher;
					$package[$i]['tray_image_file']='_tray.png';
					$package[$i]['image_data_version']=$image_data_version;
					if ($publisher=="👻") {
						$package[$i]['publisher_email']="edu.spuqui@gmail.com";
						$package[$i]['publisher_website']="https://www.youtube.com/@SpuquiWAStickers";
					}
					$package[$i]['animated_sticker_pack']=true;
					$package[$i]['stickers']=array();

					$privatefiles = glob('PrivatePacks/'.$folder.'/*.webp');
					sort($privatefiles);
					if (!file_exists($stickerpack.'/_tray.png')) {
						$firstSticker = $privatefiles[0];
						$trayPathTmp = $stickerpack.'/_tray.webp';
						$trayPath = $stickerpack.'/_tray.png';
						
						// Extract first frame using your existing libwebp requirement
						shell_exec("webpmux -get frame 1 $firstSticker -o $trayPathTmp 2>&1");
						$trayCreation = shell_exec("dwebp $trayPathTmp -resize 96 96 -o $trayPath 2>&1");
						serverMsg($trayCreation, $_POST['reqID']);

						if (file_exists($trayPath)) {
							if (file_exists($trayPathTmp)) unlink($trayPathTmp);
						}
					}

					forEach($privatefiles as $stk) {
						$stkbasename = basename($stk);
						$stkfilename = basename($stk, '.webp');
						$emojis = $meta[$stkfilename]['emojis'] ?? [];
						// Check if sticker is compliant
						if (isset($meta[$stkfilename]['compliance'])) {
							if ($meta[$stkfilename]['compliance']) {
								$package[$i]['stickers'][] = array(
									'image_file' => $stkbasename,
									'accessibility_text' => $stkfilename.' in '.$folder,
									'emojis' => $emojis
								);
							} else {
								serverMsg($folder."/".$stkbasename." excluded due to non-compliance: ".$meta[$stkfilename]['noncompliancereason'], "");
							}
						} else {
							if (checkStickerCompliance($stk)) {
								$package[$i]['stickers'][] = array(
									'image_file' => $stkbasename,
									'accessibility_text' => $stkfilename.' in '.$folder,
									'emojis' => $emojis
								);
							} else {
								$stkinfo = getStickerInfo($stk);
								if ($stkinfo['CanvasSize']!="512 x 512")
									serverMsg($folder."/".$stkbasename." excluded due to non-compliance in Canvas Size: ".$stkinfo['CanvasSize'], "");
								if ($stkinfo['FileSize']>"512000")
									serverMsg($folder."/".$stkbasename." excluded due to non-compliance in File Size: ".$stkinfo['FileSize'], "");
								if ($stkinfo['TotalDuration']>"10000")
									serverMsg($folder."/".$stkbasename." excluded due to non-compliance in Total Duration: ".$stkinfo['TotalDuration'], "");
							}
						}
					}
				}
			}
			
			$androidMediaBase = '/storage/emulated/0/Android/media';
			if (isset($package)) {
				$output=array();
				$output['sticker_packs']=$package;
				$outputJSON = json_encode($output, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

				if ($f=fopen('PrivatePacks/contents.json', 'w')) {
					fwrite($f, $outputJSON);
					fclose($f);
					if (!is_dir($androidMediaBase)) {
						replyUp('Private Packs processed.', $_POST['reqID']);
					}
				}
			} else {
				if (file_exists('PrivatePacks/contents.json')) {
					unlink('PrivatePacks/contents.json');
				}
				replyUp('No Private Packs to process.', $_POST['reqID']);
			}
			$appFolder = $androidMediaBase . '/app.spuqui.wastks';
			$targetPrivatePacks = $appFolder . '/privatepacks';
			$sourcePrivatePacks = 'PrivatePacks/';	// Local folder in ~/WAStks/

			// Check if we are on an Android device with Media storage access
			if (is_dir($androidMediaBase)) {
				if (!is_dir($appFolder)) mkdir($appFolder, 0755, true);
				if (!is_dir($targetPrivatePacks)) mkdir($targetPrivatePacks, 0755, true);

				// Synchronize Files
				// Using rsync if available is faster, otherwise fallback to shell copy
				// -a: archive, -u: update (only newer), -r: recursive
				$cmd = "rsync -a --delete \"$sourcePrivatePacks\" \"$targetPrivatePacks/\" 2>&1";
				exec($cmd, $output, $returnVar);

				if ($returnVar !== 0) {
					// Fallback: If rsync isn't in the environment, use standard cp
					$cmd = "rm -rf \"$targetPrivatePacks/\"* && cp -r \"$sourcePrivatePacks\"* \"$targetPrivatePacks/\" 2>&1";
					exec($cmd, $output, $returnVar);
				}

				if ($returnVar === 0) {
					// Success logging
					replyUp('Private packs processed and deployed.', $_POST['reqID']);
				}

			}

		}
		if (isset($_POST['zrokShare'])) {
			$zrokalias = $_POST['zrokShare'];
			
			// 1. Get the current overview for the SYSTEM account
			$overview_json = shell_exec("zrok overview");
			// file_put_contents('zrokdebug.txt',$overview_json);
			$data = json_decode($overview_json, true);
			
			$reservationExists = false;
			
			// 2. Check if the alias is already reserved in any environment visible to this user
			if (isset($data['environments'])) {
				foreach ($data['environments'] as $env) {
					if (isset($env['shares'])) {
						foreach ($env['shares'] as $share) {
							// If the shareToken matches our alias, it's already reserved
							if ($share['shareToken'] === $zrokalias) {
								$reservationExists = true;
								break 2;
							}
						}
					}
				}
			}

			// 3. Only attempt to reserve if it doesn't exist yet
			if (!$reservationExists) {
				$reserveOutput = [];
				$reserveResult = -1;
				// Capture stderr to see real errors if this fails
				exec("zrok reserve public http://127.0.0.1:8269 --unique-name $zrokalias 2>&1", $reserveOutput, $reserveResult);
				
				if ($reserveResult !== 0) {
					replyDown("Reservation failed: " . implode("\n", $reserveOutput), $_POST['reqID'] ?? '');
					exit;
				}
			}

			// 4. Execution: Launch the share window
			if (php_uname('s') == 'Windows NT') {
				$startShare = "zrok share reserved $zrokalias --headless";
				$WshShell = new COM("WScript.Shell");
				$WshShell->Run("cmd /c $startShare", 0, false);
			} else {
				// Termux/Linux
				$logFile = "zrok_error.log";
				$startShare = "zrok share reserved $zrokalias --headless > $logFile 2>&1 &";
				shell_exec($startShare);
			}
			
			replyUp("zrok share successful", $_POST['reqID'] ?? '');
			include 'owner-server.php';
		}
		if (isset($_POST['zrokStop'])) {
			$alias = $_POST['zrokStop'];
			
			if (php_uname('s') == 'Windows NT') {
				exec('taskkill /F /FI "SESSION eq 0" /IM zrok.exe');
			} else {
				shell_exec("pkill -f \"zrok share reserved $alias\"");
			}
			
			// Give zrok a moment to breathe, then release the name
			usleep(500000);
			shell_exec("zrok release $alias");

			replyUp("Success: zrok share terminated", $_POST['reqID'] ?? '');
			include 'owner-server.php';
		}
		if (isset($_POST['Save'])) {
			if ($_POST['Save']=='OwnerName') {
				if (isset($_POST['OwnerName'])) {
					if (file_put_contents('.owner/name.txt', $_POST['OwnerName']))
						replyUp("OwnerName updated to ".$_POST['OwnerName'], $_POST['reqID'] ?? '');
				}
			}
			else if ($_POST['Save']=='DeviceName') {
				if (isset($_POST['DeviceName'])) {
					if (file_put_contents('.owner/device.txt', $_POST['DeviceName']))
						replyUp("DeviceName updated to ".$_POST['DeviceName'], $_POST['reqID'] ?? '');
				}
			}
			else if ($_POST['Save']=='Publisher') {
				if (isset($_POST['Publisher'])) {
					if (file_put_contents('.owner/publisher.txt', $_POST['Publisher']))
						replyUp("Publisher updated to ".$_POST['Publisher'], $_POST['reqID'] ?? '');
				}
			}
		}
		if (isset($_POST['Clean'])) {
			if ($_POST['Clean']=="Favorites") {
				$fav = glob('Favorites/*.fav');
				usort($fav,'cdate_desc');
				$count=0;
				foreach($fav as $favor) {
					$file = file_get_contents($favor);
					if (!file_exists($file)) {
						if (unlink($favor)) { 
							serverMsg("Unlinked ".$favor, "");
							$count++;
						}
					}
				}
				replyUp($count." broken favorites removed.", $_POST['reqID'] ?? '');
			}
		}
	}
echo'</div>';

if (isset($_COOKIE['owner_auth'])) {
	if (isset($_POST['GetExifForm']))	{ renderExifForm($_POST['GetExifForm']); }
	if (isset($_POST['GetPhoneForm']))	{ renderPhoneForm($_POST['GetPhoneForm']); }

	echo'<div id=workshop>';
		include 'workshop.php';
	echo'</div>';
}

?>