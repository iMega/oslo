#!/usr/bin/env bash
mkdir -p $ROOTFS/var/run
mkdir -p $ROOTFS/app/src

cp $SRC/daemon.sh $ROOTFS/app/
cp $SRC/oslo $ROOTFS/app/
cp -r $SRC/iMega $ROOTFS/app/src/
cp -r $SRC/vendor $ROOTFS/app/
cp -r $SRC/config $ROOTFS/app/
chmod +x $ROOTFS/app/daemon.sh
cp $SRC/wait-list.txt $ROOTFS/app/wait-list.txt

cp $SRC/rsyncd.conf $ROOTFS/etc/rsyncd.conf
