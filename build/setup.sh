#!/bin/bash
set -ev

sudo apt-get update
sudo apt-get install apache2 libapache2-mod-fastcgi
# enable php-fpm
sudo cp ~/.phpenv/versions/$(phpenv version-name)/etc/php-fpm.conf.default ~/.phpenv/versions/$(phpenv version-name)/etc/php-fpm.conf
sudo a2enmod rewrite actions fastcgi alias
echo "cgi.fix_pathinfo = 1" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
~/.phpenv/versions/$(phpenv version-name)/sbin/php-fpm
# configure apache virtual hosts
sudo cp -f build/travis-ci-apache /etc/apache2/sites-available/default
sudo sed -e "s?%TRAVIS_BUILD_DIR%?$(pwd)?g" --in-place /etc/apache2/sites-available/default
sudo service apache2 restart

sh -e /etc/init.d/xvfb start
sleep 3 # give xvfb some time to start

sudo apt-get install fluxbox -y --force-yes
fluxbox &
sleep 3 # give fluxbox some time to start

git submodule update --init --recursive

composer config -g github-oauth.github.com $GITHUB_TOKEN
composer global require hirak/prestissimo

cd component/libraries/redevent
composer dump-autoload
cd ../../../build

npm install -g gulp-cli
npm install

mv gulp-config.json.dist gulp-config.json
gulp release --skip-version --testRelease
cd ../tests
composer install --prefer-dist
pwd
ls vendor/bin
