#!/bin/bash
if ! dpkg -s termux-services >/dev/null 2>&1; then
	bash ~/wastks/prepare-wastks.sh
	if [ $? -ne 0 ]; then
		exit 1
	fi
fi
chmod +x ~/wastks/*.sh
grep -qxF "cd ~/wastks" ~/.bashrc 2>/dev/null || echo "cd ~/wastks" >> ~/.bashrc
[ -d ~/storage ] || termux-setup-storage
pkg update -y && pkg upgrade -y -o Dpkg::Options::="--force-confold"
echo =========================================
echo       WAStks installation starting
echo =========================================
echo Installing requirements...
if ! dpkg -s php-apache >/dev/null 2>&1; then
	pkg install -y php-apache
fi
if ! dpkg -s zrok >/dev/null 2>&1; then
	pkg install -y zrok
fi
if ! dpkg -s libwebp >/dev/null 2>&1; then
	pkg install -y libwebp
fi
if ! dpkg -s exiftool >/dev/null 2>&1; then
	pkg install -y exiftool
fi
echo Configuring Apache for WAStks...
if [ ! -f "$PREFIX/etc/apache2/httpd-original.conf" ]; then
	echo - Creating backup of original httpd.conf file...
	mv -f $PREFIX/etc/apache2/httpd.conf $PREFIX/etc/apache2/httpd-original.conf
fi
if [ -f "$HOME/wastks/termux-httpd.conf" ]; then
	echo - Updating httpd.conf file with WAStks version...
	mv -f ~/wastks/termux-httpd.conf $PREFIX/etc/apache2/httpd.conf
fi
if [ -f "$HOME/wastks/termux-php.ini" ]; then
	echo - Adding php.ini config file...
	mkdir -p $PREFIX/etc/php
	mv -f ~/wastks/termux-php.ini $PREFIX/etc/php/php.ini
fi
if [ ! -e "$HOME/wastks/WAStks/.owner" ]; then
	bash ~/wastks/config-wastks.sh
fi
if [ ! -e "$HOME/wastks/WAStks/WA-Media" ]; then
	echo Creating SymLink for WhatsApp Media folder...
	ln -s /storage/emulated/0/Android/media/com.whatsapp/WhatsApp/Media ~/wastks/WAStks/WA-Media
fi
echo Setting Apache to run as a service...
sv-enable httpd
echo Apache service current status:
sv status httpd
echo =========================================
echo      WAStks installation complete
echo =========================================
echo Launching WAStks...
sleep 2
bash ~/wastks/launch-wastks.sh
