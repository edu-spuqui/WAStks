#!/bin/bash
[ -d ~/storage ] || termux-setup-storage
if grep -q "packages-cf.termux.dev" "$PREFIX/etc/apt/sources.list"; then
    termux-change-repo
fi
chmod +x *.sh
pkg update -y && pkg upgrade -y -o Dpkg::Options::="--force-confold"
if ! dpkg -s termux-services >/dev/null 2>&1; then
	pkg install -y termux-services
	echo ======================================
	echo Please restart Termux to apply changes
	echo ======================================
	sleep 5
	exit 1
fi