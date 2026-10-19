//	App Variables
let selectedTheme;
let filteredFolder;
let caller;
let previouscaller = document.getElementById('clipBoard');
let catsToSearch = [];
let videoStream = null;
let scanning = false;
let isOwner = false;

//	System Functions
function getTimestamp() {
	const now = new Date();
	let hour = now.getHours();
	let minute = now.getMinutes();
	let second = now.getSeconds();
	if ( hour   < 10 ) {hour   = '0'+hour;}
	if ( minute < 10 ) {minute = '0'+minute;}
	if ( second < 10 ) {second = '0'+second;}
	return hour+':'+minute+':'+second;
}
function getSimpleTimestamp() {
	const now = new Date();
	let hour = now.getHours();
	let minute = now.getMinutes();
	let ampm;
	if ( hour > 12 ) {
		hour = hour - 12;
		ampm = 'PM';
	} else {
		ampm = 'AM';
	}
	if ( minute < 10 ) {minute = '0'+minute;}
	return hour+':'+minute+' '+ampm;
}
function getFullDate() {
    const now = new Date();
    const options = { month: 'long', day: 'numeric', year: 'numeric' };
    return new Intl.DateTimeFormat('en-US', options).format(now);
}
function getCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for(var i=0;i < ca.length;i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1,c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
    }
    return null;
}
function setCookie(name,value,days) {
    var expires = "";
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days*24*60*60*1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "")  + expires + "; path=/";
}
function audioPlay(audioSource) {
  const audioPlayer = document.querySelector('#audioPlayer');
  audioPlayer.src = audioSource;
  audioPlayer.play();
}
function themeSet(kind) {
	setCookie('selectedTheme',kind,1);
	
	const b = document.querySelector('body');
	
	if (kind=="light") {
		b.style.setProperty('--color',					'var(--light-color)');
		b.style.setProperty('--color-title',			'var(--light-color-title)');
		b.style.setProperty('--background-color',		'var(--light-background-color)');
		b.style.setProperty('--background-title',		'var(--light-background-title)');
		b.style.setProperty('--background-img',			'var(--light-background-img)');
		b.style.setProperty('--background-input',		'var(--light-background-input)');
		b.style.setProperty('--background-myinput',		'var(--light-background-myinput)');
		b.style.setProperty('--background-sysinput',	'var(--light-background-sysinput)');
		b.style.setProperty('--background-sys',			'var(--light-background-sys)');
		b.style.setProperty('--custom-green',			'var(--light-custom-green)');
		b.style.setProperty('--background-color-inv',	'var(--dark-background-color)');
		b.style.setProperty('--background-img-inv',		'var(--dark-background-img)');

		b.style.setProperty('--background-position',	'var(--real-background-position)');
		b.style.setProperty('--background-size',		'var(--real-background-size)');
	}
	if (kind=="dark") {
		b.style.setProperty('--color',					'var(--dark-color)');
		b.style.setProperty('--color-title',			'var(--dark-color-title)');
		b.style.setProperty('--background-color',		'var(--dark-background-color)');
		b.style.setProperty('--background-title',		'var(--dark-background-title)');
		b.style.setProperty('--background-img',			'var(--dark-background-img)');
		b.style.setProperty('--background-input',		'var(--dark-background-input)');
		b.style.setProperty('--background-myinput',		'var(--dark-background-myinput)');
		b.style.setProperty('--background-sysinput',	'var(--dark-background-sysinput)');
		b.style.setProperty('--background-sys',			'var(--dark-background-sys)');
		b.style.setProperty('--custom-green',			'var(--dark-custom-green)');
		b.style.setProperty('--background-color-inv',	'var(--light-background-color)');
		b.style.setProperty('--background-img-inv',		'var(--light-background-img)');
							 
		b.style.setProperty('--background-position',	'var(--real-background-position)');
		b.style.setProperty('--background-size',		'var(--real-background-size)');
	}
	if (kind=="avdlight") {
		b.style.setProperty('--color',					'var(--avd-light-color)');
		b.style.setProperty('--color-title',			'var(--avd-light-color-title)');
		b.style.setProperty('--background-color',		'var(--avd-light-background-color)');
		b.style.setProperty('--background-title',		'var(--avd-light-background-title)');
		b.style.setProperty('--background-img',			'var(--avd-light-background-img)');
		b.style.setProperty('--background-input',		'var(--avd-light-background-input)');
		b.style.setProperty('--background-myinput',		'var(--avd-light-background-myinput)');
		b.style.setProperty('--background-sysinput',	'var(--avd-light-background-sysinput)');
		b.style.setProperty('--background-sys',			'var(--avd-light-background-sys)');
		b.style.setProperty('--custom-green',			'var(--avd-light-custom-green)');
		b.style.setProperty('--background-color-inv',	'var(--avd-dark-background-color)');
		b.style.setProperty('--background-img-inv',		'var(--avd-dark-background-img)');

		b.style.setProperty('--background-position',	'var(--avd-background-position)');
		b.style.setProperty('--background-size',		'var(--avd-background-size)');
	}
	if (kind=="avddark") {
		b.style.setProperty('--color',					'var(--avd-dark-color)');
		b.style.setProperty('--color-title',			'var(--avd-dark-color-title)');
		b.style.setProperty('--background-color',		'var(--avd-dark-background-color)');
		b.style.setProperty('--background-title',		'var(--avd-dark-background-title)');
		b.style.setProperty('--background-img',			'var(--avd-dark-background-img)');
		b.style.setProperty('--background-input',		'var(--avd-dark-background-input)');
		b.style.setProperty('--background-myinput',		'var(--avd-dark-background-myinput)');
		b.style.setProperty('--background-sysinput',	'var(--avd-dark-background-sysinput)');
		b.style.setProperty('--background-sys',			'var(--avd-dark-background-sys)');
		b.style.setProperty('--custom-green',			'var(--avd-dark-custom-green)');
		b.style.setProperty('--background-color-inv',	'var(--avd-light-background-color)');
		b.style.setProperty('--background-img-inv',		'var(--avd-light-background-img)');
							 
		b.style.setProperty('--background-position',	'var(--avd-background-position)');
		b.style.setProperty('--background-size',		'var(--avd-background-size)');
	}
	if (kind=="system") {
		b.style.setProperty('--color',					'var(--system-color)');
		b.style.setProperty('--color-title',			'var(--system-color-title)');
		b.style.setProperty('--background-color',		'var(--system-background-color)');
		b.style.setProperty('--background-title',		'var(--system-background-title)');
		b.style.setProperty('--background-img',			'var(--system-background-img)');
		b.style.setProperty('--background-input',		'var(--system-background-input)');
		b.style.setProperty('--background-myinput',		'var(--system-background-myinput)');
		b.style.setProperty('--background-sysinput',	'var(--system-background-sysinput)');
		b.style.setProperty('--background-sys',			'var(--system-background-sys)');
		b.style.setProperty('--custom-green',			'var(--system-custom-green)');
		b.style.setProperty('--background-color-inv',	'var(--system-background-color-inv)');
		b.style.setProperty('--background-img-inv',		'var(--system-background-img-inv)');

		b.style.setProperty('--background-position',	'var(--real-background-position)');
		b.style.setProperty('--background-size',		'var(--real-background-size)');
	}
	document.getElementById('themePickerTog').classList.remove('toggled');
	if (!document.getElementById('themePicker').classList.contains('softhide')) document.getElementById('themePicker').classList.add('softhide');
}
function show(e,manysecs) {
	document.getElementById(e).style.opacity = '1';
	document.getElementById(e).style.visibility = 'visible';
	const TimeoutID = setTimeout( function () {
		if (document.getElementById(e).lastTimeoutID == TimeoutID) {
			document.getElementById(e).style.opacity = '0';
			document.getElementById(e).style.visibility = 'hidden';
		}
	}, (manysecs * 1000));
	document.getElementById(e).lastTimeoutID = TimeoutID;
}
function toggle(elem) {
	const target = document.getElementById(elem);
	if (target) {
		target.classList.toggle('softhide');
		const targetTog = document.getElementById(elem+'Tog');
		if (targetTog) targetTog.classList.toggle('toggled');
	}
}
function loadContent(elementID, phpFile) {
    const target = document.getElementById(elementID);
    if (!target) return;

    return fetch(phpFile)
	.then(response => {
		if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
		return response.text();
	})
	.then(html => {
		target.innerHTML = html;
		// Re-scan for emoji tooltips in the new content
		if (typeof updateEmojiTitles === 'function') updateEmojiTitles(elementID);
	})
	.catch(err => console.error(`Failed to load ${phpFile}:`, err));
}
function updateDiv(parsedResponse, divID) {
    return new Promise((resolve) => {
        let divtoupdate = parsedResponse.getElementById(divID);
        if (divtoupdate) {
            let update = divtoupdate.innerHTML;
            let target = document.getElementById(divID);
            if (target) {
                target.innerHTML = update;
                updateEmojiTitles(divID);
            } else {
                console.log('Warning: Could not find ' + divID + ' in current document');
            }
        } else {
            console.log('Warning: Could not find ' + divID + ' in server response');
        }
        // Signal that the DOM manipulation is finished
        resolve();
    });
}
async function post(command, reference) {
    let data = new FormData();
    let reqID = Date.now();
	data = await preparePost(data, command, reference, reqID);
	
	fetch('fetch.php', { method: "POST", body: data })
    .then(response => { return response.text(); })
    .then(htmlString => {
        const parser = new DOMParser();
        const parsedResponse = parser.parseFromString(htmlString, "text/html");
		
		postSuccessful(parsedResponse, command, reference, reqID);
    })
    .catch(error => { console.error(error); });
}
async function preparePost(data, command, reference, reqID) {
	data.append(command, reference);
	data.append('reqID', reqID);
	if (command=='Assemble') {
		const seqnum = document.getElementById('ImgSeq'+reference).value;
		const FPS = document.getElementById('FPS_'+seqnum).value;
		const FirstFrameDuration = document.getElementById('FirstFrameDuration_'+seqnum).value;
		const showcmd = document.getElementById('showcmd_'+seqnum).checked;
		let FirstFrame = 'first';
		if (document.getElementById('last'+seqnum).checked == true) {
			FirstFrame = 'last';
		}
		data.append('FPS', FPS);
		data.append('FirstFrame', FirstFrame);
		data.append('FirstFrameDuration', FirstFrameDuration);
		data.append('ShowCommand', showcmd);
	}
	else if (command=='Convert2WebP') {
		const seqnum = document.getElementById('ImgSeq'+reference).value;
		let QualityValue = document.getElementById('Quality_'+seqnum);
		if (QualityValue) {
			data.append('Quality', QualityValue.value);
			setCookie('Quality', QualityValue.value, 7);
		}
	}
	else if (command=='Disassemble') {
		const index = document.getElementById('workshop'+reference);
		if (index) {
			const FrameCount = document.getElementById(index.value+'FrameCount');
			if (FrameCount) {	data.append('FrameCount', FrameCount.value);}
			else {				console.log(getTimestamp()+' Error: '+reference+' has NO FrameCount');}
			const first = document.getElementById(index.value+'first');
			if (first) {		data.append('first', first.value);	}
			const FFD = document.getElementById(index.value+'ffd');
			if (FFD) {			data.append('FFD', FFD.value);}
		} else {				console.log(getTimestamp()+' Error: Index not found');	}
	}
	else if (command=='ToggleFav') {
		const filename = reference;
		const i = document.getElementById('workshop'+filename).value;
		data.append('workshopindex', i);
		
		let source;
		sourceElem = document.getElementById('workshop'+i+'obg');
		if (sourceElem) source = sourceElem.getAttribute('src');
		if (source) data.append('src', source);

	}
	else if (command=='SaveEmojis') {
		const filename = reference;
		const i = document.getElementById('workshop'+filename).value;
		data.append('workshopindex', i);
		
		const emoji1 = document.getElementById(i+'_emoji1');
		const emoji2 = document.getElementById(i+'_emoji2');
		const emoji3 = document.getElementById(i+'_emoji3');
		
		if (emoji1)	data.append('emoji1', emoji1.value);
		if (emoji2)	data.append('emoji2', emoji2.value);
		if (emoji3)	data.append('emoji3', emoji3.value);

	}
	else if (command=='Refresh') { if (reference=='Manifest') goToPage('serverChatPage'); }
	else if (command=='Update') goToPage('serverChatPage');
	else if (command=='Prepare') { goToPage('serverChatPage'); }
	else if (command=='Clean') { goToPage('serverChatPage'); }
	else if (command == 'LinkRemote') {
		try {
			const urlObj = new URL(reference);
			const basename = reference.split('/').pop(); 
			const filename = basename.replace(/\.[^/.]+$/, ""); 
			
			let response;
			try {
				response = await fetch(reference); 
			} catch (corsErr) {
				const path = urlObj.pathname.startsWith('/') ? urlObj.pathname.substring(1) : urlObj.pathname;
				const fetchUrl = `${urlObj.origin}/fetch.php?get_file=${encodeURIComponent(path)}`;
				response = await fetch(fetchUrl);
			}

			const blob = await response.blob();
			data.append('sticker_blob', blob, basename);

			// Find the card to check for audio
			const card = document.querySelector(`.stickercardholder[data-filename="${filename}"]`);
			const audioBtn = card?.querySelector('button[title="Audio"]');

			if (audioBtn) {
				const audioUrl = audioBtn.getAttribute('data-src');
				const audioBasename = audioUrl.split('/').pop();
				const aUrlObj = new URL(audioUrl);
				
				let aResponse;
				try {
					aResponse = await fetch(audioUrl);
				} catch (e) {
					// Use the same gate; the PHP now handles the audio mime-type
					const aPath = aUrlObj.pathname.startsWith('/') ? aUrlObj.pathname.substring(1) : aUrlObj.pathname;
					const aFetchUrl = `${aUrlObj.origin}/fetch.php?get_file=${encodeURIComponent(aPath)}`;
					aResponse = await fetch(aFetchUrl);
				}

				if (aResponse.ok) {
					const aBlob = await aResponse.blob();
					data.append('audio_blob', aBlob, audioBasename);
					console.log(`${getTimestamp()} Audio blob prepared: ${audioBasename}`);
				}
			}

		} catch (err) {
			console.error("LinkRemote preparation failed:", err);
		}
	}
	else if (command == 'Save') {
		if (reference == 'OwnerName') {
			const OwnerNameInput = document.getElementById('ownerName');
			if (OwnerNameInput) {
				let OwnerName = OwnerNameInput.value;
				data.append('OwnerName', OwnerName);
			}
		}
		else if (reference == 'DeviceName') {
			const DeviceNameInput = document.getElementById('deviceName');
			if (DeviceNameInput) {
				let DeviceName = DeviceNameInput.value;
				data.append('DeviceName', DeviceName);
			}
		}
		else if (reference == 'Publisher') {
			const PublisherInput = document.getElementById('publisher');
			if (PublisherInput) {
				let Publisher = PublisherInput.value;
				data.append('Publisher', Publisher);
			}
		}
	}
	
	if ( (command=='Link') || (command=='LinkRemote') ) {
		const filename = reference.split('/').pop();
		clientRequest('Link '+filename, reqID);
		console.log(getTimestamp()+' Link '+filename+' from '+reference);
	} else {
		clientRequest(command+' '+reference, reqID);
		console.log(getTimestamp()+' '+command+' '+reference);
	}
	
	return data;
}
async function postSuccessful(parsedResponse, command, reference, reqID) {
	let serverResponse = parsedResponse.querySelectorAll('div.serverMsgDiv');
	serverResponse.forEach(msg => {
		console.log(msg.textContent.trim());
		serverMsg(msg.outerHTML, reqID);
		if (command=='ToggleFav') {
			const favCountElem = document.getElementById('favCount');
			if (favCountElem) {
				let favCount = favCountElem.innerText;
				if (msg.textContent.includes('added')) {
					favCount = parseInt(favCount) + 1;
				} else if (msg.textContent.includes('removed')) {
					favCount = parseInt(favCount) - 1;
				}
				favCountElem.innerText = favCount;
			}
		}
	});
	
	if (command=='Clear') {
		if (reference=='ImgSeq') { loadContent('imgseq', 'imgseq.php'); document.getElementById('stickers').style.display="block"; }
		else if (reference=='Workshop') { loadContent('workshop', 'workshop.php');	}
	}
	else if (command=='ToggleFav') {
		const filename = reference;
		const stkcard = document.querySelector(`span.stickercardholder[data-filename="${filename}"]`);
		if (stkcard) { stkcard.classList.toggle('favorites'); }
		//updateDiv(parsedResponse, index+'webpactions');
		const workshopcardindex = document.getElementById('workshop'+filename);
		if (workshopcardindex) {
			const workshopcard = document.getElementById('workshop'+workshopcardindex.value+'card');
			if (workshopcard) updateDiv(parsedResponse, 'workshop'+workshopcardindex.value+'card');
		}
	}
	else if (command=='MoveIntoPackage') {	updateDiv(parsedResponse, 'workshop');	}
	else if (command=='MoveIntoPrivatePackage') {	updateDiv(parsedResponse, 'workshop');	}
	else if (command=='RemoveCollection') {	updateDiv(parsedResponse, 'collections-content');	}
	else if (command=='Link') {
		await updateDiv(parsedResponse, 'workshop');
		const PackageNameInput = document.getElementById("PackageName");
		const stickerPackName = document.getElementById("stickerPackName");
		if (stickerPackName.value) PackageNameInput.value = stickerPackName.value;
		document.getElementById('workshopPage').scrollIntoView({ behavior: 'smooth' });
	}
	else if (command=='LinkRemote') {
		await updateDiv(parsedResponse, 'workshop');
		const PackageNameInput = document.getElementById(PackageName);
		const stickerPackName = document.getElementById(stickerPackName);
		if (stickerPackName.value) PackageNameInput.value = stickerPackName.value;
		document.getElementById('workshopPage').scrollIntoView({ behavior: 'smooth' });
		const basename = reference.split('/').pop();
		const filename = basename.replace('.webp', '');
		const stkcard = document.querySelector(`span.stickercardholder[data-filename="${filename}"]`);
		const stkcardEmojis = stkcard.querySelectorAll('button.emoji');
		// console.log(stkcardEmojis);
		const workshopcard = document.querySelector(`div.workshopCard[data-filename="${filename}"]`);
		const wscardEmojis = workshopcard.querySelectorAll('input.signemojiinput');
		let emojisUpdated = false;
		// console.log(wscardEmojis);
		if (stkcardEmojis.length && wscardEmojis.length) {
			stkcardEmojis.forEach((btn, index) => {
				if (wscardEmojis[index].value == "") {
					wscardEmojis[index].value = btn.innerText.trim();
					emojisUpdated = true;
					console.log('Updated emoji value of '+wscardEmojis[index].id);
				}
			});
			if (emojisUpdated) post('SaveEmojis', filename);
		}
	}
	else if (command=='Unlink') {		updateDiv(parsedResponse, 'workshop'); }
	else if (command=='Disassemble') {	updateDiv(parsedResponse, 'imgseq'); document.getElementById('serverChatPage').scrollIntoView({ behavior: 'smooth' }); }
	else if (command=='Assemble') {		updateDiv(parsedResponse, 'workshop'); document.getElementById('workshopPage').scrollIntoView({ behavior: 'smooth' }); }
	else if (command=='Convert2WebP') {
		const seqnum = document.getElementById('ImgSeq'+reference).value;
		updateDiv(parsedResponse, seqnum+'ImgSeq');
		updateDiv(parsedResponse, 'workshop');
	}
	else if ((command=='DeleteWebPSequence') || (command=='DeletePNGSequence')) {
		updateDiv(parsedResponse, 'imgseq');
	}
	else if (command=='SaveEmojis') {
		const filename = reference;
		const i = document.getElementById('workshop'+filename).value;
		console.log('Updating only div "workshop'+i+'card"');
		updateDiv(parsedResponse, 'workshop'+i+'card');
		document.getElementById('searchInput').value="";
		callEmojiPicker('clipBoard');
	}
	else if (command=='Refresh') {
		if (reference=='Manifest') {
			updateDiv(parsedResponse, 'owner-server-content');
			await buildStickersFromManifest();
			filteredFolder = getCookie('filteredFolder');
			if (filteredFolder) {
				const filterButton = document.querySelector('button[title="filteredFolder"]');
				if (filterButton) toggleFolderFilter(filteredFolder);
			}
		}
		else if (reference=='Stickers') {
			isOwner = getCookie('owner_auth');
			if (isOwner) updateDiv(parsedResponse, 'owner-server-content');
			await buildStickersFromManifest();
			filteredFolder = getCookie('filteredFolder');
			if (filteredFolder) {
				const filterButton = document.querySelector('button[title="filteredFolder"]');
				if (filterButton) toggleFolderFilter(filteredFolder);
			}
		}
		else if (reference=='ImgSeq') {	loadContent('imgseq', 'imgseq.php'); }
		else if (reference=='Workshop') { loadContent('workshop', 'workshop.php'); }
		const request = document.getElementById('cr'+reqID);
		if (request) {
			react = request.querySelector('span.react');
			request.classList.add('success');
			react.innerHTML = '👍';
			react.style.display = 'flex';
			scrollToBottomOfDiv('serverChat');
		}
	}
	else if ((command=='zrokShare')||(command=='zrokStop')) {
		updateDiv(parsedResponse, 'owner-server-content');
	}
	scrollToBottomOfDiv('serverChat');
}
function dragOverHandler(ev) {
	ev.preventDefault();
	ev.dataTransfer.dropEffect = "link";
}
function dropHandlerWorkshop(ev) {
    ev.preventDefault();

    // 1. Check for Internal/Remote URL (The "String" kind)
    const urlData = ev.dataTransfer.getData("text/uri-list") || ev.dataTransfer.getData("text/plain");
    
    if (urlData) {
        //console.log("DRAGGED URL/PATH:", urlData);
		try {
			const url = new URL(urlData);
			const isLocal = (url.origin === window.location.origin);

			if (isLocal) {
				console.log("Verdict: LOCAL - Path:", url.pathname);
				localPath = decodeURIComponent(url.pathname);
				post('Link', localPath.substring(1));
			} else {
				console.log("Verdict: REMOTE - Full URL:", urlData);
				linkSticker(urlData);
			}
		} catch (e) {
			console.error("Invalid URL dropped:", urlData);
		}
		return; // Exit early if we found a URL
	}

    // 2. Check for Local Files (The "File" kind)
    if (ev.dataTransfer.items) {
        [...ev.dataTransfer.items].forEach((item) => {
            if (item.kind === "file") {
                const file = item.getAsFile();
                console.log("LOCAL FILE:", file.name);
                // processFile(file);
            }
        });
    }
}
//	Dynamic Height for accounting the Onscreen Keyboard
const adjustDynamicHeight = () => {
    const currentHeight = window.visualViewport ? window.visualViewport.height : window.innerHeight;
    document.querySelector('html').style.setProperty('--v-height', `${currentHeight}px`);
};
if (window.visualViewport) {
    window.visualViewport.addEventListener('resize', adjustDynamicHeight);
    window.visualViewport.addEventListener('scroll', adjustDynamicHeight);
}

//	Server Chat Functions
function systemMsg(msg) {
	const msgHTML = `<div class="systemMsgDiv">
						<div class="systemMsg">
							${msg}
						</div>
					</div>`;
	serverMsg(msgHTML,'');
}
function clientRequest(msg,reqID) {
	const message = '<div class=clientRequestDiv id="cr'+reqID+'"><div class=clientRequest>'+msg+' <div class=timestamp>'+getTimestamp()+' <img height=14 src="assets/tick.webp"></div></div><span class=react></span></div>';
	document.getElementById('serverChat').innerHTML += message;
	scrollToBottomOfDiv('serverChat');
}
function fullServerMsg(msg) {
	const msgHTML = `<div class="serverMsgDiv">
						<img width="40px" src="assets/ghost_1f47b_Spuqui.webp">
						<div class="serverMsg">
							<div class="timestamp">${getTimestamp()}</div>
								${msg}
							</div>
					</div>`;
	serverMsg(msgHTML,'');
}
function serverMsg(msg,reqID) {
	const request = document.getElementById('cr'+reqID);
	if (request) {
		react = request.querySelector('span.react');
		if (msg.includes('reply up')) {
			request.classList.add('success');
			react.innerHTML = '👍';
			react.style.display = 'flex';
			if (msg) document.getElementById('serverChat').innerHTML += msg;
			scrollToBottomOfDiv('serverChat');
		} else if (msg.includes('reply down')) {
			request.classList.add('failed');
			react.innerHTML = '👎';
			react.style.display = 'flex';
			document.getElementById('serverChat').innerHTML += msg;
			scrollToBottomOfDiv('serverChat');
		} else {
			document.getElementById('serverChat').innerHTML += msg;
			scrollToBottomOfDiv('serverChat');
		}
	} else {
		document.getElementById('serverChat').innerHTML += msg;
		scrollToBottomOfDiv('serverChat');
	}
}
function scrollToBottomOfDiv(divId) {
	const divElement = document.getElementById(divId);
	divElement.scrollTop = divElement.scrollHeight;
}
function toggleChatMode(isChat) {
	const searchInput = document.getElementById('searchInput');
	const sendBtn = document.getElementById('sendBtn');
	const docsMenu = document.getElementById('docsMenu');
	const mediaMenu = document.getElementById('mediaMenu');

	if (isChat) {
		searchInput.placeholder = "Message";
		sendBtn.style.display = "flex";
		// Hide menus to make room for the message
		if (docsMenu) docsMenu.style.display = "none";
		if (mediaMenu) mediaMenu.style.display = "none";
	} else {
		searchInput.placeholder = "Search";
		sendBtn.style.display = "none";
		if (docsMenu) docsMenu.style.display = "flex";
		if (mediaMenu) mediaMenu.style.display = "flex";
	}
}
function sendMessage() {
	const input = document.getElementById('searchInput');
	const msg = input.value.trim();
	if (msg != '') {
		// post is your existing function to talk to server-chat.php
		post('ClientMessage', msg);
		input.value = '';
		toggleChatMode(false);
	}
}

//	Navigation Functions
function hardtoggleflex(elem) {
	const target = document.getElementById(elem);
	if (target) {
		if (target.style.display != 'flex') {
			target.style.display = 'flex';
		} else {
			target.style.display = 'none';
		}
	}
}
function toggleStickers() {
	if (stickers.style.display!='none') {
		stickers.style.display='none';
		scrollToBottomOfDiv('serverChat');
	} else {
		stickers.style.display='block';
		scrollToBottomOfDiv('serverChat');
	}
}
function showStickers() {
	if (stickers.style.display=='none') {
		stickers.style.display='block';
		scrollToBottomOfDiv('serverChat');
	}
}
function hideStickers() {
	if (stickers.style.display=='block') {
		stickers.style.display='none';
		scrollToBottomOfDiv('serverChat');
	}
}
function toggleSearch() {
	const allStickers = document.getElementById("allStickers");
	const stickerSearchTog = document.getElementById("stickerSearchTog");
	if (stickerSearchTog) if (stickerSearchTog.classList.contains('toggled')) {
		stickerSearchTog.classList.remove('toggled');
		stickerSearchTog.value="";
		notsearchablestkcards = allStickers.querySelectorAll('span.stickercardholder:not(.searchable)');
		if (notsearchablestkcards) notsearchablestkcards.forEach( function (stkcard) {
			if (stkcard.classList.contains('hide')) stkcard.classList.remove('hide');
		});
		updateVisibleStickersCount();
	} else {
		toggleFolderFilter('');
		stickerSearchTog.classList.add('toggled');
		notsearchablestkcards = allStickers.querySelectorAll('span.stickercardholder:not(.searchable)');
		if (notsearchablestkcards) notsearchablestkcards.forEach( function (stkcard) {
			if (!stkcard.classList.contains('hide')) stkcard.classList.add('hide');
		});
		updateVisibleStickers(searchInput.value);
		updateVisibleStickersCount();
		searchInput.focus();
	}
}
function toggleFolderFilter(folder) {
	clearAllFilterVisuals(folder);
	const stickerSearchTog = document.getElementById("stickerSearchTog");
	if (stickerSearchTog) if (stickerSearchTog.classList.contains('toggled')) {
		stickerSearchTog.classList.remove('toggled');
		stickerSearchTog.value="";
		notsearchablestkcards = allStickers.querySelectorAll('span.stickercardholder:not(.searchable)');
		if (notsearchablestkcards) notsearchablestkcards.forEach( function (stkcard) {
			if (stkcard.classList.contains('hide')) stkcard.classList.remove('hide');
		});
		updateVisibleStickersCount();
	}
	const StickerCards = document.getElementById('allStickers').querySelectorAll("span.stickercardholder");
	const allFolders = document.getElementById('allFolders');
	let searchMode = stickerSearchTog.classList.contains('toggled');
	if (folder) {
		// Apply a folder filter
		const folderToggles = allFolders.querySelectorAll('button.toggled');
		folderToggles.forEach( function (togs) {
			togs.classList.remove('toggled');
		} );
		const folderToggle = allFolders.querySelector(`button[title='${folder}']`);
		if (folderToggle) {
			if (!folderToggle.classList.contains('toggled')) folderToggle.classList.add('toggled');
			setCookie('filteredFolder',folder,1);
			let packName=folder;
			if (folder.startsWith("Packs/")) {
				packName = folder.slice(6);
				const packNameInput = document.getElementById('PackageName');
				if (packNameInput) packNameInput.value = packName;
			} else if (folder.startsWith("PrivatePacks/")) {
				packName = folder.slice(13);
				const packNameInput = document.getElementById('PackageName');
				if (packNameInput) packNameInput.value = packName;
			} else if (folder.startsWith("User-Media/Stickers/")) {
				packName = folder.slice(20);
				const packNameInput = document.getElementById('PackageName');
				if (packNameInput) packNameInput.value = packName;
			} else {
				const packNameInput = document.getElementById('PackageName');
				if (packNameInput) packNameInput.value = packName;
			}
			const stickerPackNameInput = document.getElementById('stickerPackName');
			if (stickerPackNameInput) stickerPackNameInput.value=packName;
			if (StickerCards) StickerCards.forEach( function (stkcard) {
				let show = false;
				if (folder=='Favorites') {
					if (stkcard.classList.contains('favorites')) show = true;
				} else {
					const cardfolder = stkcard.getAttribute("data-folder");
					if (cardfolder) if (cardfolder == folder) show = true;
				}
				
				if (!show) {
					if (!stkcard.classList.contains('hide')) stkcard.classList.add('hide');
					if (!stkcard.classList.contains('folderfiltered')) stkcard.classList.add('folderfiltered');
				} else {
					if (searchMode) {
						if (stkcard.classList.contains('searchable')) {
							if (stkcard.classList.contains('hide')) stkcard.classList.remove('hide');
						}
						if (stkcard.classList.contains('folderfiltered')) stkcard.classList.remove('folderfiltered');
					} else {
						if (stkcard.classList.contains('hide')) stkcard.classList.remove('hide');
						if (stkcard.classList.contains('folderfiltered')) stkcard.classList.remove('folderfiltered');
					}
				}
			} );
		} else {
			const favCount = document.getElementById('favCount');
			if (favCount) if ( Number(favCount.innerText) > 0 ) toggleFolderFilter('Favorites');
			else {
				const WAStickers = document.querySelector('button[title="WA-Media/WhatsApp Stickers"]');
				if (WAStickers) toggleFolderFilter('WA-Media/WhatsApp Stickers');
				else {
					const UserStickers = document.querySelector('button[title="User-Media/Stickers"]');
					if (UserStickers) toggleFolderFilter('User-Media/Stickers');
				}
			}
		}
	} else {
		// Clear all filters
		setCookie('filteredFolder','',1);
		if (StickerCards) StickerCards.forEach( function (stkcard) {
			// Remove folder-specific tracking
			stkcard.classList.remove('folderfiltered');
			
			// Nuclear reset: If we aren't in search mode, nothing should be hidden
			if (!searchMode) {
				stkcard.classList.remove('hide');
			} else {
				// If searching, only show searchable items
				if (stkcard.classList.contains('searchable')) {
					stkcard.classList.remove('hide');
				} else {
					stkcard.classList.add('hide');
				}
			}
		} );
		
		const stickerPackNameInput = document.getElementById('stickerPackName');
		if (stickerPackNameInput) stickerPackNameInput.value="";

		const folderToggles = allFolders.querySelectorAll('button.toggled');
		folderToggles.forEach( function (togs) {
			togs.classList.remove('toggled');
		} );
	}
	
	if (searchMode) {
		updateVisibleStickers(searchInput.value);
	}
	updateVisibleStickersCount();
}
function clearAllFilterVisuals() {
    // 1. Target every possible button in the filter bar
    const filterButtons = document.querySelectorAll('.folderButton button');
    filterButtons.forEach(btn => {
        btn.classList.remove('toggled');
        btn.classList.remove('active');
    });

    // 2. Clear Search UI (Optional but recommended for a 'Total Reset')
    const stickerSearchTog = document.getElementById("stickerSearchTog");
    if (stickerSearchTog) {
        stickerSearchTog.classList.remove('toggled');
        stickerSearchTog.value = "";
    }
	const stickerPackNameInput = document.getElementById('stickerPackName');
	if (stickerPackNameInput) stickerPackNameInput.value="";
}
function stkzoom(index) {
	const img = document.getElementById(index+'img');
	const StickerCard = document.getElementById(index+'card');
	const audioButton = StickerCard.querySelector('button[title=Audio]');
	let audioSource = "";
	if (audioButton) audioSource = audioButton.getAttribute('data-src');
	
	const container = document.getElementById('allStickers');
	
	const stkcardbox = StickerCard.getBoundingClientRect();
	const containerbox = container.getBoundingClientRect();
	//console.log('StkCardWidth:'+stkcardbox.width+' StkCardHeight:'+stkcardbox.height);
	//console.log('ContainerWidth:'+containerbox.width+' ContainerHeight:'+containerbox.height);

	let scale;
	const maxscaleX = containerbox.width / stkcardbox.width;
	const maxscaleY = containerbox.height / stkcardbox.height;
	if (maxscaleY > maxscaleX)	scale = maxscaleX;
	else						scale = maxscaleY;
	//console.log('Scale:'+scale+' MaxScaleX:'+maxscaleX+' MaxScaleY:'+maxscaleY);

	const from_x = stkcardbox.left + stkcardbox.width/2;
	let to_x = from_x;
	if (from_x < (containerbox.left + 3*stkcardbox.width))
		to_x = containerbox.left + stkcardbox.width/2*(scale + 1);
	if (from_x > (containerbox.right - 3*stkcardbox.width))
		to_x = containerbox.right - stkcardbox.width/2*(scale + 1);
	//console.log('from_x:'+from_x+' to_x:'+to_x);

	const from_y = stkcardbox.top + stkcardbox.height/2;
	const to_y = containerbox.top + containerbox.height/2;
	//console.log('from_y:'+from_y+' to_y:'+to_y);
	
	const offset_x = ( to_x - from_x ) / scale;
	const offset_y = ( to_y - from_y ) / scale;
	//console.log('offset_x:'+offset_x+' offset_y:'+offset_y);

	img.setAttribute( 'onclick', 'stkzoomOut(\''+index+'\');');
	StickerCard.style.position = 'absolute';
	StickerCard.style.zIndex = 10;
	StickerCard.style.backgroundColor = 'rgba(var(--background-input),0.9)';
	StickerCard.style.transform = 'scale('+scale+') translateX('+offset_x+'px) translateY('+offset_y+'px)';
	if (audioSource) { audioPlay(audioSource); }
	const webpActionsTop = StickerCard.querySelector('span.webpactions-top');
	if (webpActionsTop) webpActionsTop.style.opacity = 1;
}
function stkzoomOut(index) {
	const img = document.getElementById(index+'img');
	const StickerCard = document.getElementById(index+'card');
	img.setAttribute( 'onclick', 'stkzoom(\''+index+'\');');
	img.setAttribute( 'style', 'cursor:pointer;');
	StickerCard.style.transform = 'scale(1)';
	StickerCard.style.backgroundColor = '';
	const webpActionsTop = StickerCard.querySelector('span.webpactions-top');
	if (webpActionsTop) webpActionsTop.style.opacity = 0.2;
	setTimeout( function () {
		StickerCard.style.zIndex = '';
		StickerCard.style.position = 'static';
	}, 500 );
}
function goToPage(pageId) {
    document.getElementById(pageId).scrollIntoView({ behavior: 'smooth' });
}
function toggleCollections() {
	const overlay = document.getElementById('workshopOverlay');
	const container = document.getElementById('collections');
	if (container.classList.contains('hide')) {
		container.classList.remove('hide');
		const urlParams = new URLSearchParams(window.location.search);
		const collection = urlParams.get('collection') || '';
		loadContent('collections', `collections.php?collection=${encodeURIComponent(collection)}`);
		overlay.classList.add('active');
	} else {
		container.classList.add('hide');
		overlay.classList.remove('active');
	}
}
function refreshStickers() {
	const stickersSource = document.getElementById('stickersSource');
	if (stickersSource) {
		if (stickersSource.title=="Home Collection") {
			post('Refresh', 'Stickers');
		} else {
			buildStickersFromManifest();
		}
		goToPage('serverChatPage');
	}
}
function howtoinstall() {
	document.getElementById('ownerLoginPage').scrollIntoView({ behavior: 'smooth'});
	hideStickers();
}

//	Stickers
async function buildStickersFromManifest() {
    const urlParams = new URLSearchParams(window.location.search);
    const collection = urlParams.get('collection');
    let manifestUrl;
	let remoteBaseUrl = "";

    if (collection) {
		// Regex to check for a valid IPv4 address (e.g., 192.168.1.100)
        const ipRegex = /^(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)(?:\.(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)){3}$/;
        const isNumericIP = ipRegex.test(collection);
        
        // Logic: 
        // 1. If it's a numeric IP, use HTTP and port 8269.
        // 2. If it's a domain (zrok, no-ip, etc.), use HTTPS and no port.
        const protocol = isNumericIP ? 'http:' : 'https:';
        const portSuffix = isNumericIP ? ':8269' : '';
		remoteBaseUrl = `${protocol}//${collection}${portSuffix}/`;
		manifestUrl = `proxy-fetch.php?get_remote_manifest=${encodeURIComponent(collection)}`;
		const stickersSource = document.getElementById('stickersSource');
		if (isNumericIP) {
			stickersSource.innerText = '🛜';
			stickersSource.title = 'LAN Collection: '+collection;
		} else {
			stickersSource.innerText = '🌐';
			stickersSource.title = 'WAN Collection: '+collection;
		}
    } else {
		const protocol = window.location.protocol;
		const host = window.location.hostname;
        const port = window.location.port;
        const isOwner = getCookie('owner_auth');
		if (isOwner) {
			const privateVisibility = getCookie('privateVisibility');
			if (privateVisibility=='show')
				manifestUrl = `${protocol}//${host}:${port}/owner-manifest.json?v=${Date.now()}`;
			else
				manifestUrl = `${protocol}//${host}:${port}/manifest.json?v=${Date.now()}`;
		} else {
			manifestUrl = `${protocol}//${host}:${port}/manifest.json?v=${Date.now()}`;
		}
		stickersSource.innerText = '🏠';
		stickersSource.title = 'Home Collection';
    }

    try {
        const response = await fetch(manifestUrl, { credentials: 'include' });
        if (!response.ok) {
			if (collection) {
				fullServerMsg('Collection '+collection+' is offline. Please try again later.');
			} else {
				fullServerMsg('<span>Home Collection is not available. If you just installed the server, <button onclick="login()">login</button> with your password to create the manifest.</span>');
			}
			return;
		}
		const manifest = await response.json();

        if (manifest && Array.isArray(manifest.allStickers)) {
			// 1. Update Count
			const countSpan = document.getElementById('allStickersCount');
			if (countSpan) countSpan.innerText = manifest.allStickers.length;

			// 2. Render Sticker Grid
			const baseUrl = remoteBaseUrl;
			const grid = document.getElementById('allStickers');
			const stickerHtmlArray = manifest.allStickers.map((stk) => {
				const i = stk.id;
				let classList = "stickercardholder";
				if (stk.emojis && stk.emojis.length > 0) classList += " searchable";
				if (stk.isFavorite) classList += " favorites";

				const placeholderBtn = `<button style="opacity:0;pointer-events:none;" disabled>⚠</button>`;
				
				// Inside your loop
				if (!stk.emojis || typeof stk.emojis.map !== 'function') {
					console.error("Found a broken sticker entry:", stk);
				}
				
				const emojiButtons = stk.emojis.map(e => 
					`<button title="Copy emoji" class="emoji Language" onclick="emojicopy('${e}')">${e}</button>`
				).join('');

				const audioBtn = stk.audioFile 
					? `<button title="Audio" draggable=true class="audio-draggable" data-src="${baseUrl}${stk.audioFile}" onclick="audioPlay('${baseUrl}${stk.audioFile}')">🔊</button>`
					: placeholderBtn;

				const favIcon = stk.isFavorite 
					? `<button title="Favorite">❤</button>`
					: placeholderBtn;

				const fullPath = `${stk.dirname}/${stk.filename}.webp`;
				const linkAction = collection 
					? `post('LinkRemote', '${baseUrl}${fullPath}')` 
					: `post('Link', '${fullPath}')`;
				const linkBtn = `<button title="Link" style="display:none;" onclick="${linkAction}">🔗</button>`;

				return `
					<span id="${i}cardholder" class="${classList}"
									data-id="${i}" 
									data-filename="${stk.filename}" 
									data-folder="${stk.dirname}" 
									data-pack-name="${stk['sticker-pack-name']}" 
									data-publisher="${stk['sticker-pack-publisher']}">
						<span id="${i}card" class="stickercard"> 
							<span class="webpactions" id="${i}webpactions">
								<span class="webpactions-top">
									<span>${favIcon}</span>
									<span>${audioBtn}</span>
									<span>${linkBtn}</span>
								</span>
								<span class="webpactions-bottom">
									<span></span>
									<span>${emojiButtons}</span>
									<span></span>
								</span>
							</span>
							<span class="webpimage">
								<img id="${i}img" class="imgtoload" 
									 title="${stk.dirname}/${stk.filename}" 
									 name="${stk.filename}" 
									 onclick="stkzoom(${i})"
									 src="assets/wastk.webp" 
									 data-src="${baseUrl}${stk.dirname}/${stk.filename}.webp">
							</span>
						</span>
					</span>`;
			});
			grid.innerHTML = stickerHtmlArray.join('');

			// 3. Render allFolders Bar
			const folderContainer = document.getElementById('allFolders');
			if (folderContainer) {
				let barHtml = "";

				// 1. MAIN BUTTONS: Favorites
				if (manifest.allFolders['Favorites'] !== undefined) {
					barHtml += buildFolderButtonJS('Favorites', manifest.allFolders['Favorites'], 'assets/favorites.webp');
				}

				// 2. STICKER PACKS: Individual buttons for each pack
				const packs = Object.keys(manifest.allFolders);
				const packSubfolders = packs.filter(f => f.startsWith('Packs/') && f !== 'Packs');
				packSubfolders.forEach(f => {
					const trayPath = `${baseUrl}${f}/_tray.png`;
					barHtml += buildStickerPackButtonJS(f, manifest.allFolders[f], trayPath);
				});

				// 3. PRIVATE STICKER PACKS: Individual buttons for each pack
				const privatePackSubfolders = packs.filter(f => f.startsWith('PrivatePacks/') && f !== 'PrivatePacks');
				privatePackSubfolders.forEach(f => {
					const trayPath = `${baseUrl}${f}/_tray.png`;
					barHtml += buildStickerPackButtonJS(f, manifest.allFolders[f], trayPath);
				});

				// 4. RECURSIVE FOLDERS (Stickers Branch)
				const folders = Object.keys(manifest.allFolders);
				const mSep = folders.find(f => f.includes('\\')) ? '\\' : '/';
				const WAlibRoot = 'WA-Media' + mSep + 'WhatsApp Stickers';
				const libRoot = 'User-Media' + mSep + 'Stickers';

				// Filter the manifest keys directly to ensure we have the exact strings for lookup
				const libraryFolderKeys = folders.filter(f => {
					return f === 'ComfyUI' || f === WAlibRoot || f === libRoot || f.startsWith(libRoot + mSep) || f.startsWith(libRoot + '/');
				});

				libraryFolderKeys.forEach(f => {
					// 1. DEFINE LOGIC FIRST (Fixes the ReferenceError)
					const normalize = (path) => path.replace(/\\+|\//g, '/');
					const fNorm = normalize(f);
					const comfyNorm = normalize('ComfyUI')
					const libRootNorm = normalize(libRoot);
					const WAlibRootNorm = normalize(WAlibRoot);

					let parentPathNorm = "";
					if (fNorm !== libRootNorm) {
						const parts = fNorm.split('/');
						parts.pop();
						parentPathNorm = parts.join('/');
					}

					const isRoot = (fNorm === libRootNorm)||(fNorm === WAlibRootNorm);
					const isImmediateChild = (parentPathNorm === libRootNorm);
					const isExtra = (fNorm === comfyNorm);
					const isVisibleInitially = (isRoot || isImmediateChild || isExtra);

					// 2. PERFORMANCE: Lazy-Loading Image Logic
					// We use a static placeholder for hidden subfolders to save CPU/RAM
					const staticPlaceholder = 'assets/wastk.webp'; 
					let currentThumb = isRoot ? 'assets/wastk.webp' : null;
					if (f==='ComfyUI') currentThumb = 'assets/comfyui.png';
					let pendingThumb = "";
					
					if (!currentThumb) {
						const firstStk = manifest.allStickers.find(s => s.dirname === f);
						const fullUrl = firstStk ? `${baseUrl}${firstStk.dirname}${mSep}${firstStk.filename}.webp` : staticPlaceholder;
						
						if (isVisibleInitially) {
							currentThumb = fullUrl; // Load animated icon now
						} else {
							currentThumb = staticPlaceholder; // Use static icon for now
							pendingThumb = fullUrl; // Save animated icon for when clicked
						}
					}

					// 3. BUILD BUTTON
					const count = manifest.allFolders[f];
					let btnHtml = buildFolderButtonJS(f, count, currentThumb);

					// 4. VISIBILITY & ATTRIBUTES
					const hiddenClass = isVisibleInitially ? '' : ' folder-hidden';
					
					// Inject data-pending if we have a deferred animation
					const pendingAttr = pendingThumb ? ` data-pending="${pendingThumb}"` : "";

					const hasSubfolders = libraryFolderKeys.some(fSub => {
						const fSubNorm = normalize(fSub);
						return fSubNorm.startsWith(fNorm + '/') && fSubNorm !== fNorm;
					});

					if (hasSubfolders && !isRoot) {
						btnHtml = btnHtml.replace('<button', '<button class="has-children-indicator"');
					}

					btnHtml = btnHtml.replace('class="folderButton"', 
						`class="folderButton${hiddenClass}" data-lib="true" data-norm-path="${fNorm}" data-norm-parent="${parentPathNorm}"${pendingAttr}`);
					
					barHtml += btnHtml;
				});

				// FINAL ASSEMBLED RENDER
				folderContainer.innerHTML = barHtml;
			}

			// --- Place at the end of buildStickersFromManifest ---
			document.querySelectorAll('.folderButton[data-lib="true"] button').forEach(btn => {
				const wrapper = btn.closest('.folderButton');
				const myPath = wrapper.getAttribute('data-norm-path');
				const myParentPath = wrapper.getAttribute('data-norm-parent');
				
				const originalFilter = btn.onclick;
				
				btn.onclick = (e) => {
					if (e) e.stopPropagation();
					
					// 1. Run the original gallery filter
					if (originalFilter) originalFilter.apply(btn, [e]);

					// 2. Visual state update
					wrapper.classList.add('is-expanded');

					// 3. HIDE siblings' branches (Accordion)
					const siblings = document.querySelectorAll(`.folderButton[data-norm-parent="${CSS.escape(myParentPath)}"]`);
					siblings.forEach(sib => {
						const sibPath = sib.getAttribute('data-norm-path');
						if (sibPath !== myPath) {
							// Find all descendants and hide them
							document.querySelectorAll(`.folderButton[data-norm-path^="${CSS.escape(sibPath)}/"]`).forEach(desc => {
								desc.classList.add('folder-hidden'); 
								desc.classList.remove('is-expanded');
							});
						}
					});

					// 4. SHOW my direct children and ACTIVATE their animations
					const myChildren = document.querySelectorAll(`.folderButton[data-norm-parent="${CSS.escape(myPath)}"]`);
					myChildren.forEach(child => {
						// Make them visible
						child.classList.remove('folder-hidden'); 

						// PERFORMANCE: Check for pending animated icon
						const pendingUrl = child.getAttribute('data-pending');
						if (pendingUrl) {
							const img = child.querySelector('img');
							if (img) {
								img.src = pendingUrl; // Swap placeholder for real animation
							}
							// Remove attribute so we don't force a reload on every click
							child.removeAttribute('data-pending'); 
						}
					});
				};
			});	

			// 4. Post-Render Logic
			if (typeof initStickerObserver === 'function') { initStickerObserver(); }
			requestAnimationFrame(() => {
				updateVisibleStickersCount();
				updateEmojiTitles('stickers');
				unlockUI();
				filteredFolder = getCookie('filteredFolder');
				if (filteredFolder) toggleFolderFilter(filteredFolder);
				
			});
		}
    } catch (e) {
        if (collection) {
			console.error('Collection '+collection+' is offline:', e);
			fullServerMsg('Collection '+collection+' is offline. Please try again later.');
		} else {
			console.error('Home Collection is offline:', e);
			fullServerMsg('Home Collection is offline. Please start your http server.');
		}
    }
}
function buildFolderButtonJS(folder, count, img) {
    const title = folder;

    // Equivalent to PHP's basename(folder)
    const baseName = folder ? folder.split('/').filter(Boolean).pop() : '';
    const isPrivate = baseName.startsWith('_');

    // If img exists, use an <img> tag. Otherwise, use the emoji as text.
    const isUrl = img && ( img.startsWith('http') || img.startsWith('WA-Media') || img.startsWith('User-Media') || img.startsWith('assets') );
    let imgHtml = isUrl ? `<img src="${img}">` : `📁`;

    // Prepend lock subicon if the folder name starts with '_'
    if (isPrivate) {
        imgHtml = imgHtml + `<span class="foldersubicon">🔒</span>`;
    }

    const spanId = folder === 'Favorites' ? 'id="favCount"' : '';

    return `
        <span class="folderButton">
            <button title="${title}" onclick="toggleFolderFilter('${folder}');">
                ${imgHtml}
            </button>
            <span ${spanId}>${count}</span>
        </span>`;
}
function buildStickerPackButtonJS(folder, count, img) {
    const isPrivate = (img && img.startsWith('PrivatePacks')) || (folder && folder.startsWith('PrivatePacks'));
    const isUrl = img && ( img.startsWith('http') || img.startsWith('Packs') || img.startsWith('PrivatePacks') || img.startsWith('assets') );
    
    let imgHtml = isUrl ? `<img src="${img}">` : `📦`;
    
    // Prepend the lock subicon if this is a private pack
    if (isPrivate) {
        imgHtml = imgHtml + `<span class="foldersubicon">🔒</span>`;
    }
    
    const countStyle = (count >= 3 && count <= 30) 
        ? 'style="color:rgba(var(--custom-green),1);text-shadow:0.1vh 0.1vh black;"' 
        : 'style="color:red;text-shadow:0.1vh 0.1vh black;"';
        
    return `
        <span class="folderButton">
            <button title="${folder}" onclick="toggleFolderFilter('${folder}');">
                ${imgHtml}
            </button>
            <span ${countStyle}>${count}</span>
        </span>`;
}
function buildSubfolderTogButtonJS(folder, count, img) {
    let divID = '';
    if (folder === 'WA-Media/WhatsApp Stickers') divID = 'stickersSubfolders';
    else if (folder === 'Packs') divID = 'stickerPacksSubfolders';
    
    if (!divID) return '';

    // Check if img is a URL or an emoji
    const isUrl = img && ( img.startsWith('http') || img.startsWith('WA-Media') || img.startsWith('assets') );
    const imgHtml = isUrl 
        ? `<img src="${img}" onerror="this.src='assets/wastk.webp'">` 
        : `📁`;

    return `
        <span class="folderButton">
            <button id="${divID}Tog" title="Toggle ${folder} subfolders" onclick="hardtoggleflex('${divID}');">
                ${imgHtml}
            </button>
            <span>${count}</span>
        </span>`;
}
function initStickerObserver() {
    const stickersToLoad = document.querySelectorAll('.imgtoload');
    const options = { root: document.getElementById('allStickers'), threshold: 0.1 };

    const observer = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('imgtoload');
                img.classList.add('imgloaded');
                observer.unobserve(img);
            }
        });
    }, options);

    stickersToLoad.forEach(img => observer.observe(img));
}
function enableHorizontalDrag(selector) {
    const slider = document.querySelector(selector);
    if (!slider) return;

    let isDown = false;
    let startX;
    let scrollLeft;

    slider.addEventListener('mousedown', (e) => {
        isDown = true;
        startX = e.pageX - slider.offsetLeft;
        scrollLeft = slider.scrollLeft;
		e.preventDefault(); // Stop text selection immediately
    });

    slider.addEventListener('mouseleave', () => {
        isDown = false;
    });

    slider.addEventListener('mouseup', () => {
        isDown = false;
    });

    slider.addEventListener('mousemove', (e) => {
        if (!isDown) return;
        e.preventDefault();
        const x = e.pageX - slider.offsetLeft;
        const walk = (x - startX) * 1.333; // Feel free to adjust multiplier
        slider.scrollLeft = scrollLeft - walk;
    });

    // Prevent image/button ghosting
    slider.addEventListener('dragstart', (e) => e.preventDefault());
}

//	Emojis / Search
async function buildEmojisFrom(pathfile) {
    try {
        // 1. Fetch and parse the JSON file
        const response = await fetch(pathfile);
        const decoded = await response.json();
        
        // 2. Access the 'emojis' array
        const emojis = decoded.emojis;

        // 3. Find your container (make sure this ID exists in your HTML)
        const container = document.getElementById('emojis');
        
        // Clear previous content if necessary
        container.innerHTML = '';

        // 4. Generate the HTML buttons
        const html = emojis.map(emj => {
            // Join keywords array into a space-separated string (like PHP implode)
            const keywords = emj.keywords.join(" ");
            
            // Build the button string using Template Literals
            return `
                <button 
                    id="emj${emj.id}" 
                    code="${emj.code}" 
                    class="emoji shadow" 
                    onclick="emojicopy('${emj.sample}')" 
                    title="${emj.name}" 
                    cat="${emj.cat}" 
                    subcat="${emj.subcat}" 
                    keywords="${keywords}" 
                    sample="${emj.sample}">
                    ${emj.sample}
                </button>
            `.trim();
        }).join('\n');

        // 5. Inject into the DOM
        container.insertAdjacentHTML('beforeend', html);

    } catch (error) {
        console.error("Error loading emojis:", error);
    }
}
function clearClipboard() {
	if (document.getElementById('clipBoardPlaceholder').classList.contains('softhide')) document.getElementById('clipBoardPlaceholder').classList.remove('softhide');
	document.getElementById('clipBoard').value = "";
	document.getElementById('emojiDetails').innerHTML = '';
	document.getElementById('emojiDetails').style.opacity = 0;
	if (caller) caller.value = "";
	const cleanClipBoard = document.getElementById("cleanClipBoard");
	if (cleanClipBoard) cleanClipBoard.style.display="none";

	navigator.clipboard.writeText('');
}
function updateEmojiDetails(sample) {
	emojiBtn = document.querySelector('button[sample="'+sample+'"]');
	if (emojiBtn) {
		const kwString = emojiBtn.getAttribute('keywords') || "";
		const kwHtml = kwString.split(' ')
			.map(word => `<span class="kw-item">${word}</span>`)
			.join('');
		const title = '<span class=emojiTitle>'+emojiBtn.title+'</span>';
		const keywords = '<span class=emojiKeywords>'+kwHtml+'</span>';
		document.getElementById('emojiDetails').innerHTML = title+keywords;
		document.getElementById('emojiDetails').style.opacity = 1;
	}
}
function emojicopy(text) {
	const searchInput = document.getElementById('searchInput');
	const clipBoard = document.getElementById('clipBoard');
	const stickerSearchTog = document.getElementById("stickerSearchTog");
	const clipBoardPlaceholder = document.getElementById("clipBoardPlaceholder");
	const cleanClipBoard = document.getElementById("cleanClipBoard");
	
	if (cleanClipBoard) cleanClipBoard.style.display="flex";
	updateEmojiDetails(text);
	show('emojiDetails',7);
	if (stickerSearchTog) {
		if (stickerSearchTog.classList.contains('toggled')) {
			stickerSearchTog.value = text;
			searchInput.value = text;
			updateVisibleStickers(text);
		}
	}
	if (caller) {
		caller.value = text;
		if (caller == clipBoard) {
			clipBoard.value = text;
			if (!clipBoardPlaceholder.classList.contains('softhide')) clipBoardPlaceholder.classList.add('softhide');
			navigator.clipboard.writeText(text);
		}
	}
	else {
		clipBoard.value = text;
		if (!clipBoardPlaceholder.classList.contains('softhide')) clipBoardPlaceholder.classList.add('softhide');
		navigator.clipboard.writeText(text);
	}
}
function callEmojiPicker(callerID) {
	const clipBoard = document.getElementById('clipBoard');
	const searchInput = document.getElementById('searchInput');
	if (callerID) {
		caller = document.getElementById(callerID);
		if (caller!=previouscaller) {
			// Switches to new caller
			previouscaller.classList.remove('toggled');
			caller.classList.add('toggled');
			//if (caller.classList.contains('signemojiinput')) {
			//	if (window.innerWidth < window.innerHeight) {
			//		document.getElementById('stickers').style.display='none';
			//	}
			//}
			searchInput.focus();
			previouscaller = caller;
		}
		else {
			previouscaller.classList.remove('toggled');
			previouscaller = clipBoard;
			caller = clipBoard;
			caller.classList.add('toggled');
		}
	}
}
function toggleCatFilter(cat) {
	if (catsToSearch.includes(cat)) {
		// Deactivates category filter
		setCatFilterState(cat, 0);
	} else {
		// Activates category filter
		setCatFilterState(cat, 1);
	}
}
function setCatFilterState(cat, newState) {
	const emojis = document.getElementById('emojis');
	document.getElementById(cat).filterState = newState;
	if (newState=="0") {
		// Category filter has been deactivated
		const thisCategory = emojis.querySelectorAll("[cat='"+cat+"']");
		(Object.keys(thisCategory)).forEach(key => {
			thisCategory[key].classList.toggle("hide", 1);
		})
		document.getElementById(cat).style.opacity = 0.6;
		document.getElementById(cat).classList.remove('toggled');
		const i = catsToSearch.indexOf(cat);
		catsToSearch.splice(i, 1);
		//console.log("Category filter deactivated for "+cat);
	} else {
		// Category filter has been activated
		const thisCategory = emojis.querySelectorAll("[cat='"+cat+"']");
		(Object.keys(thisCategory)).forEach(key => {
			thisCategory[key].classList.toggle("hide", 0);
		})
		document.getElementById(cat).style.opacity = 1;
		document.getElementById(cat).classList.add('toggled');
		catsToSearch.push(cat);
		//console.log("Category filter activated for "+cat);
	}
	// console.log("Current category filters: ", catsToSearch);
	updateVisible();
}
function searchAll() {
	const searchInput = document.getElementById('searchInput');
	const categoryButtons = document.getElementById("emojiCategs").querySelectorAll("button.cat");
	(Object.keys(categoryButtons)).forEach(key => {
		categoryButtons[key].style.opacity = 0.6;
		categoryButtons[key].classList.remove('toggled');
	})
	catsToSearch = [];
	// console.log("Category filters deactivated for all categories");
	updateVisible();
	searchInput.focus();
}
function getEmojiName(sample) {
	const allEmojis = document.getElementById('emojis');
	if (allEmojis) {
		const match = allEmojis.querySelector('button[sample="'+sample+'"]');
		if (match) return match.title;
	}
}
function updateEmojiTitles(container) {
	const cont = document.getElementById(container);
	if (cont) {
		const emojis = cont.querySelectorAll('button.emoji.Language');
		emojis.forEach( function (e){
			if (getEmojiName(e.innerHTML)) e.title = getEmojiName(e.innerHTML);
		} );
	} else {
		console.log('Could not find element of ID '+container);
	}
}
function updateVisible() {
	const emojis = document.getElementById('emojis');
	const value = searchInput.value.toLowerCase();
	const allEmojis = emojis.querySelectorAll("button");
	(Object.keys(allEmojis)).forEach(key => {
		if (typeof catsToSearch !== 'undefined') {
			if (catsToSearch.length == 0) {
				const name = allEmojis[key].getAttribute('title').toLowerCase();
				const keywords = allEmojis[key].getAttribute('keywords').toLowerCase();
				const sample = allEmojis[key].getAttribute('sample');
				const isVisible = name.includes(value) || keywords.includes(value) || value.includes(sample);
				allEmojis[key].classList.toggle("hide", !isVisible);
			} else {
				const name = allEmojis[key].getAttribute('title').toLowerCase();
				const keywords = allEmojis[key].getAttribute('keywords').toLowerCase();
				const sample = allEmojis[key].getAttribute('sample');
				const wasFound = name.includes(value) || keywords.includes(value) || value.includes(sample);

				const cat = allEmojis[key].getAttribute('cat');
				const isCatFiltered = catsToSearch.includes(cat);

				const isVisible = wasFound && isCatFiltered;
				allEmojis[key].classList.toggle("hide", !isVisible);
			}
		}
		else {
			const name = allEmojis[key].getAttribute('title').toLowerCase();
			const keywords = allEmojis[key].getAttribute('keywords').toLowerCase();
			const sample = allEmojis[key].getAttribute('sample');
			const isVisible = name.includes(value) || keywords.includes(value) || value.includes(sample);
			allEmojis[key].classList.toggle("hide", !isVisible);
		}
	});
	const stickerSearchTog = document.getElementById("stickerSearchTog");
	if (stickerSearchTog) {
		if (stickerSearchTog.classList.contains('toggled')) {
			updateVisibleStickers(value);
		}
	}
}
function updateVisibleStickers(value) {
	allStickers = document.getElementById('allStickers');
	if (allStickers) {
		let singletarget;
		const stickerSearchTog = document.getElementById('stickerSearchTog');
		if (stickerSearchTog.classList.contains('toggled')) {
			if (value=="") stickerSearchTog.value = "";
			singletarget = stickerSearchTog.value;
		} else {
			singletarget = "";
		}
		const searchableStickerCards = allStickers.querySelectorAll("span.stickercardholder.searchable");
		if (searchableStickerCards) searchableStickerCards.forEach( function (stkcard) {
			let show = false;
			if (value) {
				if (singletarget) {
					const cardbuttons = stkcard.querySelectorAll("button.emoji");
					if (cardbuttons) cardbuttons.forEach( function (cb) {
						const cardemoji = cb.innerHTML;
						if ( value==cardemoji )	show = true;
					});
				} else {
					const cardbuttons = stkcard.querySelectorAll("button.emoji");
					if (cardbuttons) cardbuttons.forEach( function (cb) {
						const cardemoji = cb.innerHTML;
							const ResultEmoji = emojis.querySelector("button[sample='"+cardemoji+"']");
							if (ResultEmoji) if (!ResultEmoji.classList.contains('hide'))	show = true;
					});
					const stkfilename = stkcard.dataset.filename.toLowerCase();
					const stkfolder = stkcard.dataset.folder.replace(/[_-]/g, ' ').toLowerCase();
					const stkpackname = stkcard.dataset.packName.replace(/[_-]/g, ' ').toLowerCase();
					if (stkfilename.includes(value)) show = true;
					if (stkfolder.includes(value)) show = true;
					if (stkpackname.includes(value)) show = true;
				}
			} else {
				show = true;
			}
			if (!show) {
				if (!stkcard.classList.contains('hide'))	stkcard.classList.add('hide');
			} else {
				if (stkcard.classList.contains('hide'))		stkcard.classList.remove('hide');
			}
		} );
		updateVisibleStickersCount();
	}
}
function updateVisibleStickersCount() {
	const allStickers = document.getElementById("allStickers");
	if (allStickers) visiblestkcards = allStickers.querySelectorAll('span.stickercardholder:not(.hide)');
	const visibleStickersCount = visiblestkcards.length;
	const visibleStickersCountSpan = document.getElementById("visibleStickersCount");
	if (visibleStickersCountSpan) visibleStickersCountSpan.innerHTML = visibleStickersCount;
}
function updateAllStickersCount() {
	const allStickers = document.getElementById("allStickers");
	if (allStickers) stkcards = document.getElementById('allStickers').querySelectorAll('span.stickercardholder');
	const allStickersCount = stkcards.length;
	const allStickersCountSpan = document.getElementById("allStickersCount");
	if (allStickersCountSpan) allStickersCountSpan.innerHTML = allStickersCount;
}
function toggleLanguage() {
	const languageMenuDiv = document.getElementById('languageMenu');
	if (languageMenuDiv.classList.contains('softhide')) {
		languageMenuDiv.classList.remove('softhide');
	} else {
		languageMenuDiv.classList.add('softhide');
	}
}
async function setLanguageEnglish() {
	await buildEmojisFrom('emojis-latest.json');
	updateEmojiTitles("allStickers");
	document.getElementById('languageMenu').classList.add('softhide');
	document.getElementById('language').innerHTML='🇺🇸';
	document.getElementById('searchInput').placeholder='Search';
	setCookie('Language','English',30);
	
}
async function setLanguagePortuguese() {
	await buildEmojisFrom('emojis-latest-PT.json');
	updateEmojiTitles("allStickers");
	document.getElementById('languageMenu').classList.add('softhide');
	document.getElementById('language').innerHTML='🇧🇷';
	document.getElementById('searchInput').placeholder='Pesquisar';
	setCookie('Language','Portuguese',30);
}
async function setLanguageSpanish() {
	await buildEmojisFrom('emojis-latest-ES.json');
	updateEmojiTitles("allStickers");
	document.getElementById('languageMenu').classList.add('softhide');
	document.getElementById('language').innerHTML='🇪🇸';
	document.getElementById('searchInput').placeholder='Buscar';
	setCookie('Language','Spanish',30);
}

//	Camera
function tick() {
    const video = document.getElementById('video');
    const canvasElement = document.getElementById('canvas');
    const canvas = canvasElement.getContext('2d', { willReadFrequently: true });

    // Only process if the video is actually playing and receiving data
    if (video.readyState === video.HAVE_ENOUGH_DATA && scanning) {
        canvasElement.height = video.videoHeight;
        canvasElement.width = video.videoWidth;
        
        // Draw the current video frame onto the hidden canvas
        canvas.drawImage(video, 0, 0, canvasElement.width, canvasElement.height);
        
        // Extract the raw RGBA pixel data from the canvas
        const imageData = canvas.getImageData(0, 0, canvasElement.width, canvasElement.height);
        
        // The jsQR magic happens here
		const code = jsQR(imageData.data, imageData.width, imageData.height, {
			inversionAttempts: "attemptBoth", // Now supports both Light and Dark themes
		});

        if (code) {
            // Success! Send the data to your handler
            handleScanResult(code.data);
            return; // Exit the loop
        }
    }
    
    // If no code found, request the next animation frame to try again
    if (scanning) {
        requestAnimationFrame(tick);
    }
}
async function startScanner() {
    const video = document.getElementById('video');
    const container = document.getElementById('camera-container');

    try {
        // Request access to the back camera specifically
        videoStream = await navigator.mediaDevices.getUserMedia({ 
            video: { facingMode: "environment" } 
        });
        
        // Link the camera stream to your video element
        video.srcObject = videoStream;
        video.setAttribute("playsinline", true); 
        video.play();
		
        // Show the container now that the stream is ready
        container.style.display = "flex";
		goToPage('serverChatPage');
        
        // Next step will be the 'tick' function to start scanning...
        scanning = true;
		requestAnimationFrame(tick);
		
    } catch (err) {
        console.error("Camera access denied or unavailable:", err);
        alert("Unable to access camera.");
    }
}
function stopScanner() {
    scanning = false;
	if (videoStream) {
        // Correctly shut down the camera hardware
        videoStream.getTracks().forEach(track => track.stop());
        videoStream = null;
    }
    document.getElementById('camera-container').style.display = "none";
}
function toggleScanner() {
    const container = document.getElementById('camera-container');
    const toggleBtn = document.getElementById('cameraTog');
	
	toggleBtn.classList.toggle('toggled');
    if (container.style.display === 'none') { startScanner(); }
	else									{ stopScanner();  }
}
function handleScanResult(scannedData) {
    toggleScanner(); 

    try {
        const url = new URL(scannedData);
        // Extract the IP or hostname (e.g., "192.168.68.103")
        const collection = url.hostname; 

        if (collection) {
            // Check if we are scanning ourselves (optional safety)
            if (collection === "localhost" || collection === "127.0.0.1") {
                alert("You are scanning your own local server.");
                return;
            }

            // Redirect to local app but pull data from the scanned IP
            window.location.href = `http://127.0.0.1:8269/?collection=${collection}`;
        }
    } catch (e) {
        console.error("Invalid QR Code:", scannedData);
        alert("This QR code does not contain a valid wastks link.");
    }
}

//	Owner section
function login() {
	document.getElementById('ownerLoginPage').scrollIntoView({ behavior: 'smooth' });
	document.getElementById('owner-passwd').focus();
}
async function ownerLogin() {
    const passwd = document.getElementById('owner-passwd');
	if (passwd.value!="") {
		let data = new FormData();
		const command = 'Authentication';
		const reference = 'Attempt';
		let reqID = Date.now();
		let password = passwd.value;
		data.append(command, reference);
		data.append('reqID', reqID);
		data.append('passwd', password);
		
		fetch("fetch.php", { method: "POST", body: data })
		.then(response => { return response.text(); })
		.then(htmlString => {
			const parser = new DOMParser();
			const parsedResponse = parser.parseFromString(htmlString, "text/html");
			// postSuccessful(parsedResponse, command, reference, reqID);
			let serverResponse = parsedResponse.querySelectorAll('div.serverMsgDiv');
			serverResponse.forEach(msg => {
				console.log(msg.textContent.trim());
				serverMsg(msg.outerHTML, reqID);
				if (msg.innerText.includes('Owner logged in')) {
					setCookie('owner_auth', password, 365);
					loadContent('owner-login','owner-login.php');
					buildStickersFromManifest();
					unlockUI();
				}
				else {
					passwd.value = "";
				}
			});
		});
	}
}
function ownerLogout() {
	fullServerMsg('Owner logged out');
	console.log(getTimestamp() + ' Owner logged out')
	setCookie('owner_auth', '', 0.0001);
	loadContent('owner-login','owner-login.php');
	buildStickersFromManifest();
	unlockUI();
}
function unlockUI() {
	const linkBtns = document.querySelectorAll('button[title="Link"]');
	const workshoppage = document.getElementById('workshopPage');
	
	let isOwner = getCookie('owner_auth');
	if (isOwner) {
		if (workshoppage.classList.contains('hide')) workshoppage.classList.remove('hide');
		loadContent('workshop','workshop.php');
		loadContent('imgseq','imgseq.php');
		linkBtns.forEach((btn) => btn.style.display = "flex");
	}
	else {
		if (!workshoppage.classList.contains('hide')) workshoppage.classList.add('hide');
		document.getElementById('workshop').innerHTML = "";
		document.getElementById('imgseq').innerHTML = "";
		linkBtns.forEach((btn) => btn.style.display = "none");
	}
}
function indexMetadata() {
	const allStickersCount = Number(document.getElementById('localAllStickersCount').innerText);
	const showProgress = document.getElementById("showProgress");
	let resumeFrom = Number(getCookie('indexMetadata'));
	
	let data = new FormData();
    const reqID = Date.now();
	
	data.append('indexMetadata',resumeFrom);
	data.append('reqID', reqID);
	
	// console.log('resumeFrom='+resumeFrom);
	if ( (resumeFrom == 0) || (resumeFrom == allStickersCount) ) {
		goToPage('serverChatPage');
		clientRequest('Index metadata.', reqID);
		console.log(getTimestamp()+' Indexing metadata');
		updateProgress(0);
		if (showProgress) showProgress.classList.remove('softhide');
		resumeFrom=0;
	} else {
		console.log(getTimestamp()+' Resuming index of metadata from '+resumeFrom);
		let progress = resumeFrom/allStickersCount*100;
		updateProgress(progress.toFixed(2));
		if (showProgress) showProgress.classList.remove('softhide');
	}
	
    fetch('fetch.php', { method: "POST", body: data })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.text();
    })
    .then(htmlString => {
        
		const parser = new DOMParser();
        const parsedResponse = parser.parseFromString(htmlString, "text/html");
		let serverResponse = parsedResponse.querySelectorAll('div.serverMsgDiv');
		serverResponse.forEach(msg => {
			if ( msg.innerHTML.includes('Updated ') ) {
				serverMsg(msg.outerHTML, reqID);
			}
			if ( msg.innerHTML.includes('Resume from index') ) {
				resumeFrom = parseInt(msg.innerHTML.slice(-10));
				setCookie('indexMetadata', resumeFrom, 0.01);
				if (Number.isFinite(resumeFrom)) {
					let progress = resumeFrom/allStickersCount*100;
					updateProgress(progress.toFixed(2));
					resumeIndexMetadata(reqID);
				}
			}
			else if ( msg.innerHTML.includes('Completed!') ) {
				serverMsg(msg.outerHTML, reqID);
				setCookie('indexMetadata', allStickersCount, 0.01);
				updateProgress(100);
				setTimeout(() => {showProgress.classList.add('softhide');}, 2000);
			}
			console.log(msg.textContent.trim());
		});
		if (serverResponse.length === 0 && htmlString.length > 0) {
			// If the server sent text but NO .serverMsgDiv, it's likely a PHP error
			fullServerMsg(htmlString);
		}
    })
    .catch(error => { console.error(error);	});
}
function resumeIndexMetadata(reqID) {
	const allStickersCount = Number(document.getElementById('localAllStickersCount').innerText);
	const showProgress = document.getElementById("showProgress");
	let resumeFrom = Number(getCookie('indexMetadata'));
	
	let data = new FormData();
	
	data.append('indexMetadata',resumeFrom);
	data.append('reqID', reqID);
	
	// console.log('resumeFrom='+resumeFrom);
	console.log(getTimestamp()+' Resuming index of metadata from '+resumeFrom);
	let progress = resumeFrom/allStickersCount*100;
	updateProgress(progress.toFixed(2));
	if (showProgress) showProgress.classList.remove('softhide');
	
    fetch('fetch.php', { method: "POST", body: data })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.text();
    })
    .then(htmlString => {
        
		const parser = new DOMParser();
        const parsedResponse = parser.parseFromString(htmlString, "text/html");
		let serverResponse = parsedResponse.querySelectorAll('div.serverMsgDiv');
		serverResponse.forEach(msg => {
			if ( msg.innerHTML.includes('Updated ') ) {
				serverMsg(msg.outerHTML, reqID);
			}
			if ( msg.innerHTML.includes('Resume from index') ) {
				resumeFrom = parseInt(msg.innerHTML.slice(-10));
				setCookie('indexMetadata', resumeFrom, 0.01);
				if (Number.isFinite(resumeFrom)) {
					let progress = resumeFrom/allStickersCount*100;
					updateProgress(progress.toFixed(2));
					resumeIndexMetadata(reqID);
				}
			}
			else if ( msg.innerHTML.includes('Completed!') ) {
				serverMsg(msg.outerHTML, reqID);
				setCookie('indexMetadata', allStickersCount, 0.01);
				updateProgress(100);
				setTimeout(() => {showProgress.classList.add('softhide');}, 2000);
			}
			console.log(msg.textContent.trim());
		});
		if (serverResponse.length === 0 && htmlString.length > 0) {
			// If the server sent text but NO .serverMsgDiv, it's likely a PHP error
			fullServerMsg(htmlString);
		}
    })
    .catch(error => { console.error(error);	});
}
function updateProgress(percentage) {
  const progressBar = document.getElementById("progressBar");
  const progressContainer = document.getElementById("progressContainer");
  if (progressBar) progressBar.style.width = percentage + "%";
  if (progressContainer) progressContainer.innerText = percentage + "%";
}
function savePrivateVisibility() {
	const privateVisibilityButton = document.querySelector('input[name="privatevisibility"]:checked');
	if (privateVisibilityButton) {
		let privateVisibility = privateVisibilityButton.value;
		setCookie('privateVisibility',privateVisibility,365);
		goToPage('serverChatPage');
		showStickers();
		refreshStickers();
	}
	
}
//	Workshop
async function linkSticker(url) {
	// 1. Convert the direct URL to a PHP-gated URL
    // From: http://192.168.68.100:8269/WA-Media/stk.webp
    // To:   http://192.168.68.100:8269/fetch.php?get_file=WA-Media/stk.webp
    
	const urlObj = new URL(url);
    let proxiedUrl = url;
	
	if (urlObj.hostname !== window.location.hostname) {
		const serverBase = urlObj.origin + '/fetch.php';
		// urlObj.pathname includes the leading slash, 
		// so we remove it to match your strpos($filePath, 'WA-Media/') === 0 check
		const filePath = urlObj.pathname.startsWith('/') ? urlObj.pathname.substring(1) : urlObj.pathname;
		proxiedUrl = `${serverBase}?get_file=${encodeURIComponent(filePath)}`;
	}	

    try {
        // 1. Fetch the file into browser memory as a Blob
        const response = await fetch(proxiedUrl);
        const blob = await response.blob();
        
        // Extract original filename from the URL
        const filename = url.split('/').pop(); 

        // 2. Prepare the payload for your LOCAL server
        const formData = new FormData();
        // Command is 'Link', Reference is the filename
        formData.append('LinkRemote', filename);
        // Pass the actual file binary
        formData.append('sticker_blob', blob, filename);
        formData.append('reqID', 'link_' + Date.now());

        // 3. POST to the local root (fetch.php)
        const localResponse = await fetch('fetch.php', {
            method: 'POST',
            body: formData
        });

		const result = await localResponse.text();
		const parser = new DOMParser();
		const parsedResponse = parser.parseFromString(result, "text/html");
		await updateDiv(parsedResponse, 'workshop');
        
    } catch (error) {
        console.error("Link failed:", error);
    }
}
function toggleSignature(filename) {
	const overlay = document.getElementById('workshopOverlay');
	const signForm = document.getElementById('signForm');
	
	// 1. Handle "Force Close" or empty call
	if (!filename || filename === '') {
		const currentSign = overlay.querySelector('#SignatureFor');
		if (currentSign) {
			const currentFilename = currentSign.value;
			const prevBtn = document.querySelector('button[title="ToggleSign '+currentFilename+'"]');
			if (prevBtn) prevBtn.classList.remove('toggled');
		}
		signForm.innerHTML = "";
		overlay.classList.remove('active');
		return;
	}

	const toggleBtn = document.querySelector('button[title="ToggleSign '+filename+'"]');
	if (overlay.classList.contains('active')) {
		const currentSign = overlay.querySelector('#SignatureFor');
		if (currentSign) {
			const currentFilename = currentSign.value;
			if (currentFilename == filename) {
				// Clear and deactivate overlay
				signForm.innerHTML = "";
				overlay.classList.remove('active');
				if (toggleBtn) toggleBtn.classList.remove('toggled');
			} else {
				// Deactivate previous toggleButton
				const prevtoggleBtn = document.querySelector('button[title="ToggleSign '+currentFilename+'"]');
				if (prevtoggleBtn) prevtoggleBtn.classList.remove('toggled');
				// Load signature for filename
				loadSignForm(filename);
				if (toggleBtn) toggleBtn.classList.add('toggled');
			}
		}
	} else {
		// Load signature for filename
		loadSignForm(filename);
		if (toggleBtn) toggleBtn.classList.add('toggled');
	}
}
function loadSignForm(filename) {
	let data = new FormData();
    data.append('GetExifForm', filename);

    fetch('fetch.php', {
        method: "POST",
        body: data
    })
    .then(response => response.text()) // Get the full page HTML
    .then(htmlString => {
        const parser = new DOMParser();
        const parsedResponse = parser.parseFromString(htmlString, "text/html");
        
        // Use your existing logic to find the new form in the returned HTML
        // Assuming your PHP puts the form inside an element with ID 'sign-form'
        const responseSignForm = parsedResponse.getElementById('signForm');
		const overlay = document.getElementById('workshopOverlay');
		const signForm = document.getElementById('signForm');
		
        if (responseSignForm && signForm && overlay) {
            signForm.innerHTML = responseSignForm.innerHTML;
			updateEmojiTitles('signForm');
            overlay.classList.add('active');
        } else {
            console.error("Could not find #signForm in server response");
        }
        
        // If you need to update other parts of the UI (like serverChat) 
        // you can call your updateDiv logic here too
        // updateDiv(parsedResponse, "workshopContent");
    })
    .catch(error => console.error('Workshop Fetch Error:', error));
}
function applySignatureTemplate() {
    const currentSign = document.getElementById('SignatureFor');
    if (!currentSign) return;

    const filename = currentSign.value;
    const form = document.getElementById('form-' + filename);
    if (!form) return;

    /* 1. Retrieve hidden data and input fields */
    const originPath = document.getElementById('origin-' + filename)?.value || "";
    const defaultPublisher = document.getElementById('default-pub-' + filename)?.value || "";
    
    const packInput = form.querySelector('input[name="exif_data[sticker-pack-name]"]');
    const newPackInput = document.getElementById('PackageName');
    const pubInput = form.querySelector('input[name="exif_data[sticker-pack-publisher]"]');
    const idInput = form.querySelector('input[name="exif_data[sticker-pack-id]"]');
    const accessInput = form.querySelector('input[name="exif_data[accessibility-text]"]');

    let packName = "";
    let accessibilityText = "";
	let workshopAudioSize = "";
	
    /* Sync Emojis from Card */
    const indexInput = document.getElementById('workshop' + filename);
    const i = (indexInput !== null) ? indexInput.value : null;
    const workshopCard = (i !== null) ? document.getElementById('workshop' + i + 'card') : null;

    if (workshopCard) {
        const workshopEmojis = Array.from(workshopCard.querySelectorAll('.workshopEmojis .emoji')).map(e => e.innerText);
        workshopEmojis.forEach((emoji, index) => {
            const editorInput = document.getElementById(`workshop_emoji_${index + 1}`);
            if (editorInput) editorInput.value = emoji;
        });
		const workshopAudio = workshopCard.querySelector('button[title="Play"]');
		if (workshopAudio) workshopAudioSize = workshopAudio.getAttribute('data-audio-size');
    }

    /* 2. Pack Name */
	if (packInput)		packName = packInput.value.trim();
	if (newPackInput)	packName = newPackInput.value.trim();

    /* 3. Logic for Origin Path extraction */
    if (originPath) {
        const lastSlash = originPath.lastIndexOf('/');
        const pathPart = originPath.substring(0, lastSlash);
        const filePart = originPath.substring(lastSlash + 1).replace('.webp', '').replace('_Sub_', '_');

        if (pathPart.includes('Packs/')) {
            const parts = pathPart.split('Packs/');
            if (parts[1]) packName = parts[1].split('/')[0];
			if (packName.substring(0,1)=='_') packName = packName.substring(1);
        }
        
        if (workshopAudioSize)
			accessibilityText = `${filePart} in ${packName} with audio (${workshopAudioSize})`;
		else
			accessibilityText = `${filePart} in ${packName}`;
    }
	else {
        if (workshopAudioSize)
			accessibilityText = `${filename} in ${packName} with audio (${workshopAudioSize})`;
		else
			accessibilityText = `${filename} in ${packName}`;
	}

    
    if (packName && !accessibilityText) {
        const unsubbedFilename = filename.replace('.webp', '').replace('_Sub_', '_');
		
        if (workshopAudioSize)
			accessibilityText = `${unsubbedFilename} in ${packName} with audio (${workshopAudioSize})`;
		else
			accessibilityText = `${unsubbedFilename} in ${packName}`;
    }

    /* 4. Update the Standard Form Fields */
    if (packName) {
        if (packInput) packInput.value = packName;
        if (idInput) idInput.value = `app.spuqui.wastks.stickercontentprovider ${packName}`;
    }

    if (accessibilityText && accessInput) {
        accessInput.value = accessibilityText;
    }

    if (pubInput && !pubInput.value.trim()) {
        pubInput.value = defaultPublisher;
    }

    console.log(`${getTimestamp()} Template Applied & Legacy Data Cleared for ${filename}`);
}
function updateSignature(filename) {
	// 1. Target the specific form ID
	const formElement = document.getElementById('form-' + filename);
	if (!formElement) return console.error('Form not found for', filename);
	
	// 2. Capture all fields (Dynamic EXIF array + Emoji slots)
	let data = new FormData(formElement);
	
	// 3. Attach metadata for the PHP router
	data.append('UpdateEXIF', filename);
	const reqID = Date.now();
	data.append('reqID', reqID);
	
	clientRequest('Update signature for ' + filename, reqID);
	
	// 4. POST to server
	fetch('fetch.php', {
		method: "POST",
		body: data
	})
	.then(response => {
		if (!response.ok) throw new Error('Network response was not ok');
		return response.text();
	})
	.then(htmlString => {
		const parser = new DOMParser();
		const parsedResponse = parser.parseFromString(htmlString, "text/html");
		
		// Handle the Server Message (Success/Error log)
		let srvMsgElement = parsedResponse.getElementById(reqID);
		if (srvMsgElement) {
			serverMsg(srvMsgElement.outerHTML, reqID);
		}

		// 5. REFRESH THE EDITOR
		// We reload the form specifically to verify the file content changed.
		// This leaves the window open and the button "toggled" as requested.
		loadSignForm(filename);
	})
	.catch(error => { 
		console.error('Workshop Update Failed:', error);
	});
}

function togglePhone(filename) {
	const overlay = document.getElementById('workshopOverlay');
	
	// 1. Handle "Force Close" or empty call
	if (!filename || filename === '') {
		const currentPhone = overlay.querySelector('#PhoneFor');
		if (currentPhone) {
			const currentFilename = currentPhone.value;
			const prevBtn = document.querySelector('button[title="TogglePhone '+currentFilename+'"]');
			if (prevBtn) prevBtn.classList.remove('toggled');
		}
		overlay.innerHTML = "";
		overlay.classList.remove('active');
		return;
	}

	const toggleBtn = document.querySelector('button[title="TogglePhone '+filename+'"]');
	if (overlay.classList.contains('active')) {
		const currentPhone = overlay.querySelector('#PhoneFor');
		if (currentPhone) {
			const currentFilename = currentPhone.value;
			if (currentFilename == filename) {
				// Clear and deactivate overlay
				overlay.innerHTML = "";
				overlay.classList.remove('active');
				if (toggleBtn) toggleBtn.classList.remove('toggled');
			} else {
				// Deactivate previous toggleButton
				const prevtoggleBtn = document.querySelector('button[title="TogglePhone '+currentFilename+'"]');
				if (prevtoggleBtn) prevtoggleBtn.classList.remove('toggled');
				// Load signature for filename
				loadPhoneForm(filename);
				if (toggleBtn) toggleBtn.classList.add('toggled');
			}
		}
	} else {
		// Load signature for filename
		loadPhoneForm(filename);
		if (toggleBtn) toggleBtn.classList.add('toggled');
	}
}
function loadPhoneForm(filename) {
	let data = new FormData();
    data.append('GetPhoneForm', filename);

    fetch('fetch.php', {
        method: "POST",
        body: data
    })
    .then(response => response.text())
    .then(htmlString => {
        const parser = new DOMParser();
        const parsedResponse = parser.parseFromString(htmlString, "text/html");
        
        const newForm = parsedResponse.getElementById('phone-form');
		const overlay = document.getElementById('workshopOverlay');
		
        if (newForm && overlay) {
            overlay.innerHTML = newForm.outerHTML;
            overlay.classList.add('active');
        } else {
            console.error("Could not find #phone-form in server response");
        }
        
    })
    .catch(error => console.error('Workshop Fetch Error:', error));
}
function updateLastSticker(filename, lastSticker) {
	let data = new FormData();
    data.append('updateLastSticker', filename);
    data.append('lastSticker', lastSticker);

    let reqID = Date.now();
	data.append('reqID', reqID);
	
	clientRequest('Update Phone\'s Last Sticker with '+filename, reqID);
	
    fetch('fetch.php', {
        method: "POST",
        body: data
    })
    .then(response => response.text())
    .then(htmlString => {
		const parser = new DOMParser();
        const parsedResponse = parser.parseFromString(htmlString, "text/html");
		postSuccessful(parsedResponse, 'updateLastSticker', filename, reqID);
		loadPhoneForm(filename);
    })
    .catch(error => console.error('Workshop Fetch Error:', error));
}

window.onload = async function() {
	systemMsg(getFullDate());
	selectedTheme = getCookie('selectedTheme');
	if (selectedTheme) themeSet(selectedTheme);

	let language = getCookie('Language');
	if (language) {
		if (language=='English') setLanguageEnglish();
		if (language=='Portuguese') setLanguagePortuguese();
		if (language=='Spanish') setLanguageSpanish();
	}
	else await buildEmojisFrom('emojis-latest.json');
	document.getElementById('searchInput').addEventListener("input", updateVisible);

	document.getElementById('view-pager').addEventListener('focusin', function(e) {
		// Target any input that isn't the search bar
		if (e.target.tagName === 'INPUT' && e.target.id !== 'searchInput') {
			if (window.innerWidth < window.innerHeight) {
				document.getElementById('stickers').style.display = 'none';
			}
		}
	});
	document.addEventListener('dragstart', function(e) {
		// Explicitly targeting only elements with this class
		if (e.target.classList.contains('audio-draggable')) {
			const btn = e.target;
			const audioPath = btn.getAttribute('data-src'); // Using your attribute name from the screenshot
			
			// Construct the full URI
			const fullUri = window.location.origin + '/' + audioPath;
			// Clear existing data to prevent the browser from trying to drag a file/object automatically
			e.dataTransfer.items.clear();
			// Set ONLY the text/uri-list or text/plain. 
			e.dataTransfer.setData('text/plain', fullUri);
			e.dataTransfer.setData('text/uri-list', fullUri);
			// Set drag feedback
			e.dataTransfer.effectAllowed = 'copy';
			e.dataTransfer.dropEffect = 'copy';
		}
	});
	document.addEventListener('keypress', function (e) {
		if (e.key === 'Enter' && e.target && e.target.id === 'owner-passwd') {
			document.getElementById('owner-login-button').click();
		}
		if (e.key === 'Enter' && e.target && e.target.id === 'newPackageName') {
			document.getElementById('packageworkshop').click();
		}
	});	
	
	await buildStickersFromManifest();
	enableHorizontalDrag('#allFolders');

	document.getElementById('ownerLoginPage').scrollIntoView({ behavior: 'instant' });
	await loadContent('owner-login', 'owner-login.php');

	let ownerName;
	const ownerNameSpan = document.getElementById('owner_name');
	if (ownerNameSpan) ownerName = ownerNameSpan.innerText;

	if (ownerName) {
		fullServerMsg('Welcome back to WAStks, '+ownerName+'!','');
		console.log(getTimestamp() + ' Welcome back to WAStks, '+ownerName+'!');
		document.getElementById('view-pager').focus();
		document.getElementById('serverChatPage').scrollIntoView({ behavior: 'smooth' });
		const workshopContent = document.getElementById('workshop-content');
		if (workshopContent) if (!workshopContent.classList.contains('hide'))
			document.getElementById('workshopPage').scrollIntoView({ behavior: 'smooth' });
	}
	else {
		fullServerMsg('Welcome to Spuqui\'s WAStks!','');
		console.log(getTimestamp() + ' Welcome to Spuqui\'s WAStks!');
		document.getElementById('view-pager').focus();
		document.getElementById('serverChatPage').scrollIntoView({ behavior: 'smooth' });
		if (window.innerHeight > window.innerWidth)
			fullServerMsg('For instructions on how to install the WAStks tool on an Android device, swipe right and hide stickers or just <button onclick="howtoinstall();">click here</button>.','');
	}
	
	adjustDynamicHeight();
	scrollToBottomOfDiv('serverChat');
	
};
