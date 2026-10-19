<?php
$pngFiles = glob('Temp/png/*.png');
$webpFiles = glob('Temp/webp/*.webp');

{	// ImgSeq page
	if ( count($pngFiles) || count($webpFiles) ) {
		$ImgSeq = array();
		if (count($pngFiles)) {
			foreach($pngFiles as $png) {
				$shortname = basename($png, '.png');
				unset($indexlength);
				
				if (isset($ImgSeq))
					foreach($ImgSeq as $seq) {
						if (str_starts_with($shortname,$seq)) {
							if (isset($ImgSeq[$seq]['indexlength']))
								$indexlength = $ImgSeq[$seq]['indexlength'];
						}
					}
				
				if (!isset($indexlength)) {	// Identifies indexlength for each sequence
					if (!is_numeric(substr($shortname,-1)))
					{
						// Static Sticker
						$indexlength = 0;
					}
					else
					{
						if (!is_numeric(substr($shortname,-2)))
						{
							// Single Numeric Digit
							$candidateSeq = substr($shortname, 0,-1);
							$candidateSeqFiles = glob('Temp/png/'.$candidateSeq.'?.png');
							if (count($candidateSeqFiles)==1)
								$indexlength = 0;
							else if (count($candidateSeqFiles)<=10)
								$indexlength = 1;
						}
						else
						{
							if (!is_numeric(substr($shortname,-3)))
							{
								// 2 numeric digits in the end
								$candidateSeq = substr($shortname, 0,-2);
								$candidateSeqFiles = glob('Temp/png/'.$candidateSeq.'??.png');
								if (count($candidateSeqFiles)==1)
									$indexlength = 0;
								else if (count($candidateSeqFiles)<=10)
									$indexlength = 1;
								else
									$indexlength = 2;

							} else {
								// at least 3 numeric digits
								
								$candidateSeq = substr($shortname, 0,-3);
								$candidateSeqFiles = glob('Temp/png/'.$candidateSeq.'0??.png');
								if (count($candidateSeqFiles)>98) {
									$indexlength = 3;
								} else {
									$candidateSeq = substr($shortname, 0,-2);
									$candidateSeqFiles = glob('Temp/png/'.$candidateSeq.'0?.png');
									if (count($candidateSeqFiles)==10)
										$indexlength = 2;
									else 
										$indexlength = 1;
								}
							}
						}
					}
				}
				
				$sequencename = substr($shortname, 0, (strlen($shortname) - $indexlength) );
				$index = substr($shortname, strlen($shortname) - $indexlength);
				if ( !in_array($sequencename, $ImgSeq) ) {
					array_push($ImgSeq,$sequencename);
					$Sequences[$sequencename]['pngfirst'] = $index;
					$Sequences[$sequencename]['indexlength'] = $indexlength;
				} else {
					$Sequences[$sequencename]['pnglast'] = $index;
				}
				$filesize = filesize($png);
				if (isset($Sequences[$sequencename]['pngsize']))	$Sequences[$sequencename]['pngsize'] += $filesize;
				else												$Sequences[$sequencename]['pngsize'] = $filesize;
			}
		}
		if (count($webpFiles)) {
			foreach($webpFiles as $webp) {
				$shortname = basename($webp, '.webp');
				unset($indexlength);
				
				if (isset($ImgSeq))
					foreach($ImgSeq as $seq) {
						if (str_starts_with($shortname,$seq)) {
							$indexlength = $Sequences[$seq]['indexlength'];
						}
					}

				if (!isset($indexlength)) {
					if (!is_numeric(substr($shortname,-1))) /* No Index > Treat as Static Sticker */
						$indexlength = 0;
					else
					{	// Identifies indexlength for each sequence
						if (!is_numeric(substr($shortname,-2)))
						{	// Single Numeric Digit
							$candidateSeq = substr($shortname, 0,-1);
							$candidateSeqFiles = glob('Temp/webp/'.$candidateSeq.'?.webp');
							if (count($candidateSeqFiles)==1)
								$indexlength = 0;
							else if (count($candidateSeqFiles)<=10)
								$indexlength = 1;
						}
						else
						{	// At least 2 numeric digits in the end
							if (!is_numeric(substr($shortname,-3)))
							{
								// 2 numeric digits in the end
								$candidateSeq = substr($shortname, 0,-2);
								$candidateSeqFiles = glob('Temp/webp/'.$candidateSeq.'??.webp');
								if (count($candidateSeqFiles)==1)
									$indexlength = 0;
								else if (count($candidateSeqFiles)<=10)
									$indexlength = 1;
								else
									$indexlength = 2;

							} else {
								// at least 3 numeric digits
								
								$candidateSeq = substr($shortname, 0,-3);
								$candidateSeqFiles = glob('Temp/webp/'.$candidateSeq.'0??.webp');
								if (count($candidateSeqFiles)>98) {
									$indexlength = 3;
								} else {
									$candidateSeq = substr($shortname, 0,-2);
									$candidateSeqFiles = glob('Temp/webp/'.$candidateSeq.'0?.webp');
									if ((count($candidateSeqFiles)==10)||(count($candidateSeqFiles)==9))
										$indexlength = 2;
									else 
										$indexlength = 1;
								}
							}
						}
					}
				}
				
				$sequencename = substr($shortname, 0, (strlen($shortname) - $indexlength) );
				$index = substr($shortname, strlen($shortname) - $indexlength);
				if ( !in_array($sequencename, $ImgSeq) ) {
					array_push($ImgSeq,$sequencename);
					$Sequences[$sequencename]['webpfirst'] = $index;
					$Sequences[$sequencename]['indexlength'] = $indexlength;
				} else {
					$Sequences[$sequencename]['webplast'] = $index;
				}
				$filesize = filesize($webp);
				if (isset($Sequences[$sequencename]['webpsize']))
					$Sequences[$sequencename]['webpsize'] += $filesize;
				else
					$Sequences[$sequencename]['webpsize'] = $filesize;
			}
		}
		if (isset($ImgSeq)) {
			echo'<div id=imgseq-content>';
			rsort($ImgSeq);
			foreach($ImgSeq as $i => $sequence) {
				unset($Seq);
				// Gets log information if a log exists
				if (file_exists('Logs/'.$sequence.'.log')) {
					$log = explode("\n", file_get_contents('Logs/'.$sequence.'.log'));
					foreach ($log as $entry) {
						$param = explode(';', $entry);
						if (count($param) > 1) 
							if ($param[1] == 'created') {
								$Seq['fps'] = $param[2];
								$Seq['first'] = $param[3];
								if (!empty($param[4])) $Seq['ffd'] = $param[4];
							}
					}
				}
				echo'<div id="'.$i.'ImgSeq" style="scroll-snap-align:start;">';
					echo'<input type=hidden id="ImgSeq'.$sequence.'" value="'.$i.'" />';
					
					echo'<div class="flex space-between" style="height:5vh;">';
						echo'<div class="flex aligncenter space-between" style="width:calc(100% - 5vh); gap:1vh;">';
							// Image Sequence Name
							echo '<span class="imgseqname">'.$sequence.'</span>';

							// Image Count
							if (isset($Sequences[$sequence]['indexlength'])) {
								if ($Sequences[$sequence]['indexlength']==0) {
									$Seq['pngcount'] = count(glob('Temp/png/'.$sequence.'.png'));
								}
								else if ($Sequences[$sequence]['indexlength']==1) {
									$Seq['pngcount'] = count(glob('Temp/png/'.$sequence.'?.png'));
									$Seq['webpcount'] = count(glob('Temp/webp/'.$sequence.'?.webp'));
								}
								else if ($Sequences[$sequence]['indexlength']==2) {
									$Seq['pngcount'] = count(glob('Temp/png/'.$sequence.'??.png'));
									$Seq['webpcount'] = count(glob('Temp/webp/'.$sequence.'??.webp'));
								}
								else if ($Sequences[$sequence]['indexlength']==3) {
									$Seq['pngcount'] = count(glob('Temp/png/'.$sequence.'???.png'));
									$Seq['webpcount'] = count(glob('Temp/webp/'.$sequence.'???.webp'));
								}
							}
							if (isset($Sequences[$sequence]['pngsize'])) {
								echo '<span>('.$Seq['pngcount'].' images)</span>';
							} else if (isset($Sequences[$sequence]['webpsize'])) {
								echo '<span>('.$Seq['webpcount'].' images)</span>';
							} else {
								echo'Something wrong here';
							}

							echo'<span>'.($i+1).'/'.count($ImgSeq).'</span>'.PHP_EOL;

						echo'</div>';
						
					echo'</div>';
					
					echo'<div class="flex space-evenly">';
					
						// First Image
						echo'<div>';
							if (isset($Sequences[$sequence]['webpsize'])) {
								if (!isset($Seq['first'])) {
									echo'<input id="first'.$i.'" style="cursor:pointer;" title="Use \'First\' as first frame" type=radio name="first'.$i.'" checked />';
								} else {
									if ($Seq['first'] == "first") {
										echo'<input style="cursor:pointer;" type=radio title="Use (FaF) \'First as First\' frame" id="first'.$i.'" name="first'.$i.'" checked />';
									} else if ($Seq['first'] == "last") {
										echo'<input style="cursor:pointer;" type=radio title="Use (FaF) \'First as First\' frame" id="first'.$i.'" name="first'.$i.'" />';
									} else {
										echo'<div id=serverAlert>Bug is in the air...</div>';
									}
								}
								echo'<label style="cursor:pointer;" for="first'.$i.'">';
							}
							if (isset($Sequences[$sequence]['pngfirst'])) {
								echo '<img title="First" class=imgseqimg src="Temp/png/'.$sequence.$Sequences[$sequence]['pngfirst'].'.png">';
							} else if (isset($Sequences[$sequence]['webpfirst'])) {
								echo '<img title="First" class=imgseqimg src="Temp/webp/'.$sequence.$Sequences[$sequence]['webpfirst'].'.webp">';
							}
							if (isset($Sequences[$sequence]['webpsize'])) {echo'</label>'.PHP_EOL;}
						echo'</div>';
						
						// Last Image
						echo'<div>';
						if ( (isset($Sequences[$sequence]['pnglast'])) || (isset($Sequences[$sequence]['webplast'])) )
						{
							if (isset($Sequences[$sequence]['webpsize'])) {
								if (!isset($Seq['first'])) {
									echo'<input style="cursor:pointer;" title="Use \'Last\' as first frame" type="radio" id="last'.$i.'" name="first'.$i.'" />';
								} else {
									if ($Seq['first'] == "first") {
										echo'<input style="cursor:pointer;" type=radio title="Use (LaF) \'Last as First\' frame" id="last'.$i.'" name="first'.$i.'" />';
									} else if ($Seq['first'] == "last") {
										echo'<input style="cursor:pointer;" type=radio title="Use (LaF) \'Last as First\' frame" id="last'.$i.'" name="first'.$i.'" checked />';
									} else {
										echo'<div id=serverAlert>Bug is in the air...</div>';
									}
								}
								echo'<label style="cursor:pointer;" for="last'.$i.'">';
							}
							if (isset($Sequences[$sequence]['pnglast'])) {
								echo '<img title="Last" class=imgseqimg src="Temp/png/'.$sequence.$Sequences[$sequence]['pnglast'].'.png">';
							} else if (isset($Sequences[$sequence]['webplast'])) {
								echo '<img title="Last" class=imgseqimg src="Temp/webp/'.$sequence.$Sequences[$sequence]['webplast'].'.webp">';
							}
							if (isset($Sequences[$sequence]['webpsize'])) {echo'</label>'.PHP_EOL;}
						}
						echo'</div>';

					echo'</div>';
					
					echo'<div class="flex aligncenter">';
						// PNG section
						if (isset($Sequences[$sequence]['pngsize'])) {
							echo'<button title="Delete PNGs" onclick="post(\'DeletePNGSequence\', \''.$sequence.'\')">🗑</button>';
							if ( (isset($Sequences[$sequence]['pnglast'])) || (isset($Sequences[$sequence]['webplast'])) ) {
								if (isset($_COOKIE['Quality'])) {
									echo'<input id="Quality_'.$i.'" title="Quality" type="number" class="biginput" min="0" max="100" value="'.$_COOKIE['Quality'].'" />'.PHP_EOL;
								} else {
									echo'<input id="Quality_'.$i.'" title="Quality" type="number" class="biginput" min="0" max="100" value="20" />'.PHP_EOL;
								}
								echo'<button class="bigbutton" title="PNGs to WebPs" onclick="post(\'Convert2WebP\', \''.$sequence.'\')">➡</button>'.PHP_EOL;
							} else {
								if (isset($_COOKIE['Quality'])) {
									echo'<input id="Quality_'.$i.'" title="Quality" type="number" class="biginput" min="0" max="100" value="'.$_COOKIE['Quality'].'" />'.PHP_EOL;
								} else {
									echo'<input id="Quality_'.$i.'" title="Quality" type="number" class="biginput" min="0" max="100" value="20" />'.PHP_EOL;
								}
								echo'<button class="bigbutton" title="Create Static Sticker" onclick="post(\'Convert2WebP\', \''.$sequence.'\')">➡</button>'.PHP_EOL;
							}
						}
						// WebP section
						if (isset($Sequences[$sequence]['webpsize'])) {
							echo'<button title="Delete WebPs" onclick="post(\'DeleteWebPSequence\', \''.$sequence.'\')">🗑</button>';
							
							if ($Sequences[$sequence]['webpsize'] <= 512000) {
								echo '<span class=official style="font-size:2.2vh;">'.number_format($Sequences[$sequence]['webpsize']).' bytes</span>'.PHP_EOL;
							} else if ($Sequences[$sequence]['webpsize'] <= 1048576) {
								echo '<span class=unofficial style="font-size:2.2vh;">'.number_format($Sequences[$sequence]['webpsize']).' bytes</span>'.PHP_EOL;
							} else {
								echo '<span class=overlimit style="font-size:2.2vh;">'.number_format($Sequences[$sequence]['webpsize']).' bytes</span>'.PHP_EOL;
							}

							// FFD
							if (!isset($Seq['ffd'])) {	echo'<input title="Sticky?" id="FirstFrameDuration_'.$i.'" type="number" class="biginput" autocomplete="off" min=8 max=5000 value="" />';					}
							else {				echo'<input title="Sticky?" id="FirstFrameDuration_'.$i.'" type="number" class="biginput" autocomplete="off" min=8 max=5000 value="'.$Seq['ffd'].'" />';		}
							
							// FPS
							echo'<select title="FPS" id="FPS_'.$i.'" name="FPS">';
							if (isset($Seq['fps'])) {
								if ($Seq['fps']==0.5) {	echo'<option value="0.5" selected>0.5</option>';
								} else {		echo'<option value="0.5">0.5</option>';				}
								if ($Seq['fps']==1) {	echo'<option value="1" selected>1</option>';
								} else {		echo'<option value="1">1</option>';					}
								if ($Seq['fps']==2) {	echo'<option value="2" selected>2</option>';
								} else {		echo'<option value="2">2</option>';					}
								if ($Seq['fps']==3) {	echo'<option value="3" selected>3</option>';
								} else {		echo'<option value="3">3</option>';					}
								if ($Seq['fps']==4) {	echo'<option value="4" selected>4</option>';
								} else {		echo'<option value="4">4</option>';					}
								if ($Seq['fps']==5) {	echo'<option value="5" selected>5</option>';
								} else {		echo'<option value="5">5</option>';					}
								if ($Seq['fps']==6) {	echo'<option value="6" selected>6</option>';
								} else {		echo'<option value="6">6</option>';					}
								if ($Seq['fps']==7.5) {	echo'<option value="7.5" selected>7.5</option>';
								} else {		echo'<option value="7.5">7.5</option>';				}
								if ($Seq['fps']==8) {	echo'<option value="8" selected>8</option>';
								} else {		echo'<option value="8">8</option>';					}
								if ($Seq['fps']==10) {	echo'<option value="10" selected>10</option>';
								} else {		echo'<option value="10">10</option>';				}
								if ($Seq['fps']==12) {	echo'<option value="12" selected>12</option>';
								} else {		echo'<option value="12">12</option>';				}
								if ($Seq['fps']==12.5) {echo'<option value="12.5" selected>12.5</option>';
								} else {		echo'<option value="12.5">12.5</option>';			}
								if ($Seq['fps']==15) {	echo'<option value="15" selected>15</option>';
								} else {		echo'<option value="15">15</option>';				}
								if ($Seq['fps']==16) {	echo'<option value="16" selected>16</option>';
								} else {		echo'<option value="16">16</option>';				}
								if ($Seq['fps']==20) {	echo'<option value="20" selected>20</option>';
								} else {		echo'<option value="20">20</option>';				}
								if ($Seq['fps']==24) {	echo'<option value="24" selected>24</option>';
								} else {		echo'<option value="24">24</option>';				}
								if ($Seq['fps']==25) {	echo'<option value="25" selected>25</option>';
								} else {		echo'<option value="25">25</option>';				}
								if ($Seq['fps']==30) {	echo'<option value="30" selected>30</option>';
								} else {		echo'<option value="30">30</option>';				}
								if ($Seq['fps']==50) {	echo'<option value="50" selected>50</option>';
								} else {		echo'<option value="50">50</option>';				}
								if ($Seq['fps']==60) {	echo'<option value="60" selected>60</option>';
								} else {		echo'<option value="60">60</option>';				}
							} else {
								echo'<option value="0.5">0.5</option>';
								echo'<option value="1">1</option>';
								echo'<option value="2">2</option>';
								echo'<option value="3">3</option>';
								echo'<option value="4">4</option>';
								echo'<option value="5">5</option>';
								echo'<option value="6">6</option>';
								echo'<option value="7.5">7.5</option>';
								echo'<option value="8">8</option>';
								echo'<option value="10">10</option>';
								echo'<option value="12">12</option>';
								echo'<option value="12.5">12.5</option>';
								echo'<option value="15">15</option>';
								echo'<option value="16">16</option>';
								echo'<option value="20">20</option>';
								echo'<option value="24" selected>24</option>';
								echo'<option value="25">25</option>';
								echo'<option value="30">30</option>';
								echo'<option value="50">50</option>';
								echo'<option value="60">60</option>';
							}
							echo'</select>';
							
							echo'<input id=showcmd_'.$i.' type=checkbox title="Show Command" style="width:2.2vh;height:2.2vh;" />';

							// Create Sticker Button
							echo'<button title="Assemble" onclick="post(\'Assemble\', \''.$sequence.'\')">➡</button>'.PHP_EOL;
						}
					echo '</div>';
				echo'</div>';
			}
			echo'</div>';
		} else {
			echo'<div id=imgseq-content class="hide"></div>';
		}
	}
}

echo'<div id=imgseq-title class="server-title">';
	echo'<span><span class=shadow>🎞</span> Image Sequences</span>';
	echo'<span class="flex row" style="gap:1.5vh;">';
		if (isset($ImgSeq)) echo'<button title="Clear Image Sequences" onclick="post(\'Clear\', \'ImgSeq\')">🧽</button>';
		echo'<button title="Refresh Image Sequences" onclick="post(\'Refresh\',\'ImgSeq\')">♻</button>';
	echo'</span>';
echo'</div>';

?>
