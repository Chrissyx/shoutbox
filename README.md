# Chrissyx Homepage Scripts - Shoutbox

[![version](https://img.shields.io/badge/version-2.1.2-blue)](https://www.chrissyx.com/scripts.php#Shoutbox)

## Introduction
The multilingual shoutbox is fast, lightweight and user-friendly. Everything is configurable, starting from the number of displayed shouts, number of smilies (even for each row!) up to the storage locations of the internal system files. Archived shouts are being stored separately allowing really fast displaying of the most recent ones and listed in the archive having an optional configured pagination. The box is continuously being hold minimal, i.e. longer links are displayed shorten and also longer words are wrapped by hyphen(s) automatically if needed. Smilies are either maintained by the box itself or utilized from a [TBB1 forum](http://www.tritanium-scripts.com). All settings are accessable within the dedicated admin panel; cached settings are renewed automatically and immediately active after logout.

## Requirements
* ![php](https://img.shields.io/badge/php-%3E%3D5.3-blue)
* ![webspace](https://img.shields.io/badge/webspace-chmod--able-lightgrey)

## Installation
1. Upload in that directory, in which your website is (and you're planning to use the shoutbox), the folder `chscore` including its contents. If it already exists, overwrite the files.
2. If not yet present, paste in this code at the very beginning of your site (even before `<html>`, `<!DOCTYPE [...]` or `<?xml [...]`):  
   `<?php include('chscore/CHSCore.php'); ?>`
3. Point your browser to your website and attach `?module=CHSShoutboxAdmin` to the address. Follow the instructions.

### Example
* Your site is:  
  `https://www.mySite.com/myFolder/myIndex.php`  
  <sub>(Put in here in the first line `<?php include('chscore/CHSCore.php'); ?>`)</sub>
* Then upload the shoutbox to:  
  `https://www.mySite.com/myFolder/chscore/`
* And start installation with:  
  `https://www.mySite.com/myFolder/myIndex.php?module=CHSShoutboxAdmin`

## Update from 2.x
Upload the folder `chscore` including its contents and replace each existing file. Then upload `updateShoutbox.php`, execute and delete it again.

## FAQ
* How to manage my shoutbox?  
  Just point your browser to your website by adding `?module=CHSCounterAdmin` to the address, as you did during the installation and follow the instructions.
* I've forgot my password!  
  Go to the login form, you can request a new password there. The old one is still valid until you log in with the new password.
* I'm getting a message "ERROR: Can't create config file!"?!?  
  Set with your FTP program and chmod command the permisson to `755` for these folders:
  * `chscore/config/`
  * `chscore/data/`
* Is it possible to translate the shoutbox to another language?  
  Of course, copy an appropriate INI file (e.g. `en-US.CHSShoutbox.ini`) from the `chscore/languages/` folder and rename the official language code of the filename to the corresponding of the desired language. Like `fr-FR.CHSShoutbox.ini` for French in France or `nl.CHSShoutbox.ini` for general Dutch. Start translating the strings between the quotation marks and check the hints at the beginning of the file. By having a complete translation, upload it to the `chscore/languages/` folder and if applicable choose it from the language menu in the administration panel. Please send it also to me for providing it to other user! :slightly_smiling_face:
* My question isn't answered here!  
  Please visit my board at https://www.chrissyx.com/forum/ or write me an email: chris@chrissyx.com

## Credits
© 2006-2023 by Chrissyx  
Powered by V4 LeetCore Technology  
https://www.chrissyx.de/  
https://www.chrissyx.com/  
[![Twitter Follow](https://img.shields.io/twitter/follow/CXHomepage?style=social)](https://twitter.com/intent/follow?screen_name=CXHomepage)