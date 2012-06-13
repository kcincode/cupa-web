#!/bin/bash    
HOST="cincyultimate.org"
USER="cincyu6"
PASS="sdgfwe777"
LCD="/Users/fellw9/NetBeansProjects/cupa"
RCD="~/cupawebtest"
lftp -c "set ftp:list-options -a;
open ftp://$USER:$PASS@$HOST; 
lcd $LCD;
cd $RCD;
mirror --reverse \
       --delete \
       --verbose \
       --use-cache \
       --parallel=2 \
       --exclude-glob .git/ \
       --exclude-glob .gitignore \
       --exclude-glob .DS_Store \
       --exclude-glob application/data/ \
       --exclude-glob nbproject/ \
       --exclude-glob library/ \
       --exclude-glob docs/ \
       --exclude-glob public/ \
       --exclude-glob tests/ \
       --exclude-glob scripts/";
     
lftp -c "set ftp:list-options -a;
open ftp://$USER:$PASS@$HOST; 
lcd $LCD/public;
cd $RCD/../public_html_test;
mirror --reverse \
       --delete \
       --verbose \
       --use-cache \
       --parallel=2 \
       --exclude-glob upload/ \
       --exclude-glob .DS_Store \
       --exclude-glob images/team_logos/ \
       --exclude-glob images/officers/ \
       --exclude-glob images/tournaments/";
