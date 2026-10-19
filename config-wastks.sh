#!/bin/bash
echo =========================================
echo       Setting up Owner credentials
echo =========================================
echo ""
mkdir -p ~/WAStks/.owner
read -p "Choose your name/nickname: " owner_name
echo ""
printf "%s" "$owner_name" > ~/WAStks/.owner/name.txt
read -s -p "Choose your password, $owner_name: " owner_token
echo ""
printf "%s" "$owner_token" > ~/WAStks/.owner/token.txt
echo ""
read -p "Choose a name for this device: " device_name
if [ -n "$device_name" ]; then
    printf "%s" "$device_name" > ~/WAStks/.owner/device.txt
	echo "Welcome to WAStks, $owner_name on $device_name!"
else
	echo "Welcome to WAStks, $owner_name!"
fi
