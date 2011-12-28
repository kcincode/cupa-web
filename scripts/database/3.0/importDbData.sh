#!/bin/bash
mysql=`which mysql`
curl=`which curl`
gzip=`which gzip`
php=`which php`

echo "Getting most current DB data."
`$curl https://cincyu6:sdgfwe777@secure85.inmotionhosting.com:2083/getsqlbackup/cincyu6_cupa.sql.gz > /tmp/cupa-mysql-tmp.sql.gz`
`$gzip -d /tmp/cupa-mysql-tmp.sql.gz`
`echo "DROP DATABASE cupaweb; CREATE DATABASE cupaweb;" | mysql -u root -ppassword`
`$mysql -u root -ppassword cupaweb < /tmp/cupa-mysql-tmp.sql` 
`rm /tmp/cupa-mysql-tmp.sql*`

path=${0%/*}
if [ "$path" == "importDbData.sh" ]
then
    path=`pwd` 
fi

echo "Importing data into new DB."
`$php ${path}/doUpdate.php > ${path}/update.txt`
