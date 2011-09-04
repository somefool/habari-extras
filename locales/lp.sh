#!/bin/bash

# setup tmp files from tar.gz's
cd /home/matt/habari-locales/bzr/
bzr merge
bzr add locale/*
bzr commit -m "lp sync"
cd ../

# check fo .po
for d in `ls bzr/locale/*.po`;
do
    if [[ $d =~ bzr/locale/(.*).po ]]
    then
        if [ ! -d ${BASH_REMATCH[1]} ]
        then
            mkdir  ${BASH_REMATCH[1]}
            mkdir ${BASH_REMATCH[1]}/trunk
            mkdir ${BASH_REMATCH[1]}/trunk/LC_MESSAGES
            mkdir ${BASH_REMATCH[1]}/trunk/dist
        fi
        echo "moving  ${BASH_REMATCH[1]}.po"
        cp bzr/locale/${BASH_REMATCH[1]}.po ${BASH_REMATCH[1]}/trunk/LC_MESSAGES/habari.po
        echo "generating mo file"
        msgfmt -o ${BASH_REMATCH[1]}/trunk/LC_MESSAGES/habari.mo ${BASH_REMATCH[1]}/trunk/LC_MESSAGES/habari.po
        svn add -q ${BASH_REMATCH[1]} ${BASH_REMATCH[1]}/trunk/LC_MESSAGES/habari.po ${BASH_REMATCH[1]}/trunk/LC_MESSAGES/habari.mo
    fi
done

# remove tmp files
rm -rf tmp

svn ci -m"launchpad sync"

echo "done!"

