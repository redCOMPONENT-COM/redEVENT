redEVENT
=========
![Build Status](https://travis-ci.com/redCOMPONENT-COM/redEVENT.svg?token=Qnz9x1bAjvEBMoJVpNb7&branch=develop)

use the build.xml phing script to build the packages or copy to your test site
*  first copy build/build.properties.dist to build/build.properties, and edit to match your configuration
* execute 'phing site': copies to the joomla location specified in properties file
* execute 'phing release': creates install packages
