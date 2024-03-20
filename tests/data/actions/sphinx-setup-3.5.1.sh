#!/bin/sh -e
SCRIPT=$(readlink -f "$0")
CWD=$(dirname "$SCRIPT")

wget https://sphinxsearch.com/files/sphinx-3.5.1-82c60cb-linux-amd64.tar.gz -O /tmp/sphinxsearch.tar.gz
sudo mkdir /opt/sphinx
cd /opt/sphinx && sudo tar -zxf /tmp/sphinxsearch.tar.gz
rm /tmp/sphinxsearch.tar.gz

# make dir that is used in sphinx config
mkdir -p sphinx
sed -i s\~SPHINX_BASE_DIR~$PWD/sphinx~g $CWD/../sphinx-3.5.1.conf
