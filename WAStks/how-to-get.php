<div style="line-height:1.5;margin:1vh;">
How to install WAStks on Android:<br>
<ul>
<li>Get the <a href="https://f-droid.org/en/packages/com.termux/">Termux</a> app from <a href="https://f-droid.org/">F-Droid</a>, NOT from <a href="https://play.google.com/store/games?hl=en">Playstore</a>.</li>
<li>Install it, open it, run commands:
	<ul>
		<li>(optional) <a class="commands" onclick="navigator.clipboard.writeText('termux-change-repo')">termux-change-repo</a> (choose a closer/faster mirror)</li>
		<li><a class="commands" onclick="navigator.clipboard.writeText('pkg install zrok')">pkg install zrok</a></li>
<!--
		<li>(optional) <a class="commands" onclick="navigator.clipboard.writeText('zrok invite ')">zrok invite </a>your@email.com</li>
		<li>(optional) <a class="commands" onclick="navigator.clipboard.writeText('zrok enable ')">zrok enable </a>{token from email by zrok}</li>
-->
		<li><a class="commands" onclick="navigator.clipboard.writeText('zrok copy https://wastkslatest.share.zrok.io .')">zrok copy https://wastkslatest.share.zrok.io .</a></li>
		<li><a class="commands" onclick="navigator.clipboard.writeText('bash prepare-wastks.sh')">bash prepare-wastks.sh</a></li>
	</ul>
</li>
<li>Restart Termux to apply changes, run:</li>
	<ul>
		<li><a class="commands" onclick="navigator.clipboard.writeText('bash install-wastks.sh')">bash install-wastks.sh</a></li>
	</ul>
<li>Download the Android app <a href="https://spuquipcwastks.share.zrok.io/wastks_v5.apk" download="wastks_v5.apk">WAStks_v5.APK</a> and install it.</li>
</ul>
Extra installation steps if you want to share your stickers on the internet:<br>
<ul>
	<li>On Termux, run command:</li>
	<ul>
		<li><a class="commands" onclick="navigator.clipboard.writeText('zrok invite')">zrok invite</a></li> (this will open another window where you will have to type your e-mail address twice to send it the invitation)
	</ul>
	<li>Check your e-mail, find the message "Welcome to zrok!", open it, click the "Create Account" button.</li>
	<li>After creating your account, you will be provided a token, get back to Termux and run command:</li>
	<ul>
		<li><a class="commands" onclick="navigator.clipboard.writeText('zrok enable ')">zrok enable </a>{token from zrok}</li>
	</ul>
	<li>Once you enabled your zrok environment, you can turn public online sharing on/off through the WAStks interface.</li>
</ul>
</div>