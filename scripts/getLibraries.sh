#!/bin/bash
DIR="$( cd "$( dirname "$0" )" && pwd )"

# get the zend framework release 1.12
echo "Installing the Zend Framework 1.12\n"
URL="http://framework.zend.com/svn/framework/standard/branches/release-1.12/library"
svn export $URL $DIR/../library/. --force
