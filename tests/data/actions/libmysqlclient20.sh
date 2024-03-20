#!/bin/sh

# https://askubuntu.com/a/1337909
echo 'deb http://security.ubuntu.com/ubuntu xenial-security main' | sudo tee /etc/apt/sources.list.d/xenial-security.list
sudo apt update
sudo apt install libmysqlclient20
