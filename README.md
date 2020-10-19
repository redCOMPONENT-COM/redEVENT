# redEVENT [![Download latest](https://img.shields.io/badge/Download-stable-brightgreen.svg)](https://github.com/redCOMPONENT-COM/redEVENT/releases/latest) [![Drone Build Status](http://qa.redweb.dk/api/badges/redCOMPONENT-COM/redEVENT/status.svg?branch=master)](http://qa.redweb.dk/redCOMPONENT-COM/redEVENT) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/redCOMPONENT-COM/redEVENT/badges/quality-score.png?b=master&s=bab856597355afd65c6d3f41f21c2767e4e2e92f)](https://scrutinizer-ci.com/g/redCOMPONENT-COM/redEVENT/?branch=master)

use the build.xml phing script to build the packages or copy to your test site

- first copy build/build.properties.dist to build/build.properties, and edit to match your configuration
- execute 'phing site': copies to the joomla location specified in properties file
- execute 'phing release': creates install packages
