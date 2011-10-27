#!/bin/bash
DIR="$( cd "$( dirname "$0" )" && pwd )"

# get the zend framework release 1.11
echo "Installing the Zend Framework 1.11\n"
URL="http://framework.zend.com/svn/framework/standard/branches/release-1.11/library"
svn export $URL $DIR/../library/. --force