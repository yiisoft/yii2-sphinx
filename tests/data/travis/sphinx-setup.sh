#!/bin/sh -e
SCRIPT=$(readlink -f "$0")
CWD=$(dirname "$SCRIPT")

# make dir that is used in sphinx config
mkdir -p sphinx
sed -i s\~SPHINX_BASE_DIR~$PWD/sphinx~g $CWD/../sphinx.conf

# Setup source database
mysql -D yiitest -u travis < $CWD/../source.sql

# setup test Sphinx indexes:
indexer --config $CWD/../sphinx.conf --all

# run searchd:
searchd --config $CWD/../sphinx.conf
