<?php

$currentCollection = $_GET['collection'] ?? null;
$collectionsFile = 'collections.txt';
$collections = file_exists($collectionsFile) ? file($collectionsFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
if ($_SERVER['HTTP_HOST']==="127.0.0.1:8269")
	$homeLabel = "This Device";
else if ($_SERVER['HTTP_HOST']==="127.0.0.1")
	$homeLabel = "This Device";
else
	$homeLabel = $_SERVER['HTTP_HOST'];
$sourceLabel = 'spuquipcwastks.share.zrok.io';

// echo '<button title="Refresh List" onclick="loadContent(\'collections\', \'collections.php?collection='.urlencode($currentCollection).'\')">♻</button>';

echo '<div id="collections-content">';
    // If we have a collection in the URL and it's not already saved (and isn't the current host)
    if ($currentCollection && $currentCollection !== $homeLabel && $currentCollection !== $sourceLabel && !in_array($currentCollection, $collections)) {
        // Add the current collection to the array
        $collections[] = $currentCollection;
		// Trigger the internal save logic
		file_put_contents($collectionsFile, $currentCollection.PHP_EOL, FILE_APPEND);
    }
    echo '<div class="collection-list">';
        // --- DYNAMIC HOME ENTRY ---
        $isHome = empty($currentCollection);
		if ($homeLabel === $sourceLabel) {
			echo '<div class="collection-item">';
				echo '<span style="cursor:pointer; '.($isHome ? 'color: rgba(var(--custom-green),1); font-weight: bold;' : '').'" 
					  onclick="window.location.href=window.location.origin" title="Load local stickers collection"><span class=shadow>👻</span> '.$homeLabel.'</span>';
			echo '</div>';
		} else {
			echo '<div class="collection-item">';
				echo '<span style="cursor:pointer; '.($isHome ? 'color: rgba(var(--custom-green),1); font-weight: bold;' : '').'" 
					  onclick="window.location.href=window.location.origin" title="Load local stickers collection"><span class=shadow>🏠</span> '.$homeLabel.'</span>';
			echo '</div>';

			$isCurrent = ($sourceLabel === $currentCollection);
			// --- FIXED spuquipcwastks.share.zrok.io ENTRY ---
			echo '<div class="collection-item">';
				echo '<span style="cursor:pointer; '.($isCurrent ? 'color: rgba(var(--custom-green),1); font-weight: bold;' : '').'"
					  onclick="window.location.search=\'?collection='.$sourceLabel.'\'" title="Load Spuqui stickers collection"><span class=shadow>👻</span> '.$sourceLabel.'</span>';
			echo '</div>';
		}
        // --- SAVED COLLECTIONS ---
        foreach ($collections as $srv) {
            $srv = trim($srv);
            if (empty($srv) || $srv === $homeLabel) continue;
            
            $isCurrent = ($srv === $currentCollection);
			$isNumericIP = filter_var($srv, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
            if ($isNumericIP)	$icon = '🛜';
			else				$icon = '🌐';
			
            echo '<div class="collection-item">';
                echo '<span style="cursor:pointer; '.($isCurrent ? 'color: rgba(var(--custom-green),1); font-weight: bold;' : '').'" 
                      onclick="window.location.search=\'?collection='.$srv.'\'"><span class=shadow>'.$icon.'</span> '.$srv.'</span>';
                
                // User can still manually delete
                echo '<button title="Forget" class="smallButton" 
                      onclick="if(confirm(\'Forget '.$srv.'?\')) post(\'RemoveCollection\', \''.$srv.'\')">🗑</button>';
            echo '</div>';
        }
    echo '</div>';
echo '</div>';
?>