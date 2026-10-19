#!/bin/bash
if ! dpkg -s termux-services >/dev/null 2>&1; then
	bash prepare-wastks.sh
	if [ $? -ne 0 ]; then
		exit 1
	fi
fi
echo =========================================
echo       WAStks installation starting
echo =========================================
echo Installing requirements...
if ! dpkg -s php-apache >/dev/null 2>&1; then
	pkg install -y php-apache
fi
echo Configuring Apache for WAStks...
if [ ! -f "$PREFIX/etc/apache2/httpd-original.conf" ]; then
	echo - Creating backup of original httpd.conf file...
	mv -f $PREFIX/etc/apache2/httpd.conf $PREFIX/etc/apache2/httpd-original.conf
fi
if [ -f "termux-httpd.conf" ]; then
	echo - Updating httpd.conf file with WAStks version...
	mv -f termux-httpd.conf $PREFIX/etc/apache2/httpd.conf
fi
if [ -f "termux-php.ini" ]; then
	echo - Adding php.ini config file...
	mv -f termux-php.ini $PREFIX/etc/php/php.ini
fi
if [ ! -e "$HOME/WAStks/.owner" ]; then
	bash config-wastks.sh
fi
if [ ! -e "$HOME/WAStks/WA-Media" ]; then
	echo Creating SymLink for WhatsApp Media folder...
	ln -s /storage/emulated/0/Android/media/com.whatsapp/WhatsApp/Media ~/WAStks/WA-Media
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
bash launch-wastks.sh
