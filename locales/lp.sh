#!/bin/bash

# setup tmp files from tar.gz's
cd /home/matt/habari-locales/bzr
bzr pull
cd ../

# check fo .po
for d in `ls bzr/system/locale/*.po`;
do
    if [[ $d =~ bzr/system/locale/(.*).po ]]
    then
        if [ ! -d ${BASH_REMATCH[1]} ]
        then
            mkdir  ${BASH_REMATCH[1]}
            mkdir ${BASH_REMATCH[1]}/trunk
            mkdir ${BASH_REMATCH[1]}/trunk/LC_MESSAGES
            mkdir ${BASH_REMATCH[1]}/trunk/dist
            mkdir ${BASH_REMATCH[1]}/tags
            mkdir ${BASH_REMATCH[1]}/braches
        fi
        echo "moving  ${BASH_REMATCH[1]}.po"
        mv bzr/system/locale/${BASH_REMATCH[1]}.po ${BASH_REMATCH[1]}/trunk/LC_MESSAGES/habari.po
        echo "generating mo file"
        msgfmt -o ${BASH_REMATCH[1]}/trunk/LC_MESSAGES/habari.mo ${BASH_REMATCH[1]}/trunk/LC_MESSAGES/habari.po
        svn add -q ${BASH_REMATCH[1]} ${BASH_REMATCH[1]}/trunk/LC_MESSAGES/habari.po ${BASH_REMATCH[1]}/trunk/LC_MESSAGES/habari.mo
    fi
done

# remove tmp files
rm -rf tmp

svn ci -m"launchpad sync"

echo "done!"

