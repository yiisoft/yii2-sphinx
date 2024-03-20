#!/bin/sh -e
SCRIPT=$(readlink -f "$0")
CWD=$(dirname "$SCRIPT")

# install sphinx from https://sphinxsearch.com/downloads/release/
wget http://sphinxsearch.com/files/sphinxsearch_2.2.11-release-1~xenial_amd64.deb
sudo dpkg -i sphinxsearch_2.2.11-release-1~xenial_amd64.deb

# make dir that is used in sphinx config
mkdir -p sphinx
sed -i s\~SPHINX_BASE_DIR~$PWD/sphinx~g $CWD/../sphinx.conf

# setup test Sphinx indexes:
indexer --config $CWD/../sphinx.conf --all

# run searchd:
searchd --config $CWD/../sphinx.conf
