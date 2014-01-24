pmaAuth
=======

A simple user management extension for phpMyAdmin that allows to secure phpMyAdmin independent from MySQL users.

One of the big drawbacks of phpMyAdmin is that it hasn't a dedicated user management and soley relys on MySQL to manage users and access rights. In 95% of all cases that might be very acceptable but what about the 5% that may require a bit more?

I recently faced a big challenge were several servers across different data centers needed to be accessible through one centralized phpMyAdmin installation. Well it also entailed a long list of users that needed to be added and maintained including different access levels. The traditional way would be either to setup each user on each server or setting up a PAM server that handles centralized MySQL User Management.

I didn't want to mess with the individual MySQL servers so I thought it would be better to shift the whole User Management thingy to phpMyAdmin and that's where pmaAuth was born.

Users are managed using the CLI and assigned to security groups which load different configuration files for phpMyAdmin upon successfull login.

Comes as a full package with it's own session management and doesn't rely on any third party modules.


## Installation

**Before you do anything: Backup your existing config.inc.php of your phpMyAdmin Installation**

Next you need to think about some security designs. pmaAuth can run in the same directory as phpMyAdmin, **however** I highly discourage you from doing that. Put pmaAuth in a non-web-accessible directory like the parent folder of phpMyAdmin. 

Here is an example how it could look:

```
 /var/www/html/db/phpmyadmin -> Location of phpMyAdmin -> That's the path that is publicily accessible
 /var/www/html/db/pmaauth    -> Location of pmaAuth    
```

Once you figured that out and made a decision, let's get started:

1) Download or clone pmaAuth from GitHub [https://github.com/flyandi/pmaAuth]

2) Replace the config.inc.php of your phpMyAdmin installation with the one found in pmaAuth's setup folder. Make sure to modify the path to reflect the installation location of pmaAuth

```php
<?php
include("../pmaauth/pmaauth.php");
return;
```
OR keep your existing config.inc.php and just add the lines above right after the <?php tag.

**Again: I remind you to backup your config.inc.php before doing this**

3) That's it. If you go to your phpMyAdmin you should be greeted with a new login screen.


## Setup Users


