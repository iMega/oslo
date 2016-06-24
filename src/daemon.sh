#!/bin/sh

/usr/bin/rsync --no-detach --daemon --config /etc/rsyncd.conf &

inotifywait -mr -e close_write --fromfile /app/wait-list.txt | while read DEST EVENT FILE
do
    UUID=`echo $(basename "$DEST")`
    /app/oslo parse $DEST
    rsync --inplace -av $DEST/dump.sql rsync://fileman:873/storage/$UUID/
    rm -rf $DEST
done
