#!/bin/sh

/usr/bin/rsync --no-detach --daemon --config /etc/rsyncd.conf &

inotifywait -mr -e close_write --fromfile /app/wait-list.txt | while read DEST EVENT FILE
do
    RESOURCE=`echo $DEST | cut -d"/" -f2`
    UUID=`echo $(basename "$DEST")`
    case "$RESOURCE" in
        "data")
            /app/oslo parse "$UUID" "$FILE"
        ;;
        "tmp")
            rsync --inplace -av "$DEST$FILE" rsync://fileman:873/storage/"$UUID"/
        ;;
    esac
done
