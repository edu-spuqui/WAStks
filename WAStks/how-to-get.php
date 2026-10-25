<div style="line-height:1.5;margin:1vh;">
Get the <a href="https://play.google.com/store/apps/details?id=app.spuqui.wastks" target="_blank">WAStks</a> Android app from <a href="https://play.google.com/store/apps" target="_blank">Google Play Store</a>.<br>
<br>
How to install the WAStks tool on Android:<br>
<ul>
<li>Get the <a href="https://play.google.com/store/apps/details?id=com.termux" target="_blank">Termux</a> app from <a href="https://play.google.com/store/apps" target="_blank">Google Play Store</a>.</li>
<li>Install it, open it, run commands:
	<ul>
		<li><a class="commands" onclick="navigator.clipboard.writeText('pkg install -y git')">pkg install -y git</a></li>
<!--
		<li><a class="commands" onclick="navigator.clipboard.writeText('zrok copy https://wastkslatest.share.zrok.io .')">zrok copy https://wastkslatest.share.zrok.io .</a></li>
-->
		<li><a class="commands" onclick="navigator.clipboard.writeText('git clone https://github.com/edu-spuqui/WAStks.git ~/wastks')">git clone https://github.com/edu-spuqui/WAStks.git ~/wastks</a></li>
		<li><a class="commands" onclick="navigator.clipboard.writeText('bash ~/wastks/install-wastks.sh')">bash ~/wastks/install-wastks.sh</a></li>
	</ul>
</li>
</ul>
Extra installation steps if you want to share your stickers on the internet:<br>
<ul>
	<li>On Termux, run commands:</li>
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