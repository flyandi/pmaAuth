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

Ok, once you made a decision, let's install pmaAuth:

1) Download or clone pmaAuth from GitHub [https://github.com/flyandi/pmaAuth]

2) Replace the config.inc.php of your phpMyAdmin installation with the one found in pmaAuth's setup folder. Make sure to modify the path to reflect the installation location of pmaAuth

```php
<?php
include("../pmaauth/pmaauth.php");
return;
```
OR keep your existing config.inc.php and just add the lines above right after the `<?php` tag.

**Again: I remind you to backup your config.inc.php before doing this**

3) That's it. If you go to your phpMyAdmin you should be greeted with a new login screen.


## Configuration

Next we need to configure pmaAuth. All settings are maintained through a JSON file called `pmaauth.conf`. Below is a default with all options: 

```json
{
	"sessionname": "pmaAuthSession",
	"sessiontimeout": 60,
	"sessionpath": "/",
	"sessiondomain": "www.example.com",
	"sessionsecure": false,
	"sessionhttp": false,
	"html": {
		"title": "pmaAuth Authorization for phpMyAdmin",
		"header": "Database Management",
		"message": "Please enter your credentials to continue."
	},
	"groups": {
		"default": "config.inc.php"
	}
}
```

Most settings should be self explanatory and you can also do some basic customization within the `html` key. All `session` keys reflect the internal session management and the `sessiontimeout` is in `minutes`. 

Important is the `groups` key that I will discuss in the next chapter.


## Setting up Security Groups

Security Groups or just Groups are the secret sauce of pmaAuth. Basically when a user is signin-in, pmaAuth will load a group specific configuration file for phpMyAdmin. All security groups are defined within the `groups` key of the configuration file.

The `key` is the name of the group while the `value` points to the phpMyAdmin configuration file within the `groups/` folder of pmaAuth.

Example:

```json
{
	"groups": {
		"example":	"example.inc.php"
	}
}
```

Make sure not to forget to place the actual configuration file of the group within the `groups/` folder of pmaAuth. 

Configure the group configuration file for phpMyAdmin as needed. If you specify `auth-type = cookie` for phpMyAdmin you may end up with two login screens. 

Usually you would add universal users with different access rights (e.g. full-access, readonly-access, etc) to your MySQL instances and create for each "access-role-user" different phpMyAdmin configuration files with hardcoded login information and assign them to security groups. Makes sense?


## Setting up Users

Now that we have setup all of our basics, we need some users. pmaAuth comes with a handy CLI manager that allows to create and modify users.

Navigate to the installation folder of pmaAuth and execute the pma.php tool. 

```shell
> php pma.php <options>
```

Under Linux you can also execute the shell script:

```shell
> ./pma.sh <options>
```

### Actions

Action			| Call													| Example
---				| ---													| ---
Add User		| `php pma.php add <username> <password> <group>`		| `php pma.php add example 123456 default`
Change Password	| `php pma.php passwd <username> <password>`			| `php pma.php passwd example 654321`
Change Group	| `php pma.php group <username> <group>`				| `php pma.php group example full`
Delete User		| `php pma.php revoke <username>`						| `php pma.php revoke example`

You also can check if a user exists using `php pma.php test <username>`.


## Important

Make sure that the folder `sessions/` is writeable otherwise no session information can be stored and you won't be able to login. 

Also make sure that the file in `secure/users` is writeable.


## Customization

If you like you can customize the login screen of pmaAuth to your needs. For basic messaging please refer to the configuration file of pmaAuth, however if you want to change colors, design, etc - you will find the template in `template/signin.template` which is pure HTML and easy to modify.

Most important is that you keep the name of the two form fields intact: `username` for Username and `password` for the Password field. Otherwise, go crazy!

Make sure to put external css, images, etc into public-accessible paths (like the phpMyAdmin folder) because I hope you followed my strong advice to keep pmaAuth in a non-web-accessible path.


## Future

Well I have some ideas including logging of individual users what they actually do in phpMyAdmin and create reports of important modifications such as `DELETE`, `GRANT`, etc MySQL operations. 

## License

This software is released under the GPLv3 License.