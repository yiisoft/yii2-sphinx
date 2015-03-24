This directory contains scripts for automated test runs via the [Travis CI](http://travis-ci.org) build service. They are used for the preparation of worker instances by setting up needed extensions and configuring database access.

These scripts might be used to configure your own system for test runs. But since their primary purpose remains to support Travis in running the test cases, you would be best advised to stick to the setup notes in the tests themselves.

The scripts are:

 - [`sphinx-setup.sh`](sphinx-setup.sh)
   Prepares the [Sphinx](http://sphinxsearch.com/) server instances by installing the server and attaching it to MySQL