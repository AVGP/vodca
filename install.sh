#!/bin/sh
apt-get install vlc expect sudo
cp vlc /etc/init.d/vlc
cp vlc-runner.sh /usr/bin/vlc-runner.sh
adduser streaming
mkdir /var/log/vlc
chown streaming:streaming /var/log/vlc
echo "Done."
