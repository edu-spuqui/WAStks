#!/bin/bash
chmod +x ~/wastks/*.sh
grep -qxF "cd ~/wastks" ~/.bashrc 2>/dev/null || echo "cd ~/wastks" >> ~/.bashrc
[ -d ~/storage ] || termux-setup-storage
pkg update -y && pkg upgrade -y -o Dpkg::Options::="--force-confold"
if ! dpkg -s termux-services >/dev/null 2>&1; then
	pkg install -y termux-services
	echo ======================================
	echo Please restart Termux to apply changes
	echo ======================================
	sleep 5
	exit 1
fi