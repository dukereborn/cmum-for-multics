cmum-for-multics
======================

A simple php-script to export users from CSP MySQL User Manager into MultiCS

## Requirements
* PHP 5.3.0 or newer
* MySQL 5.0 or newer
* CSP MySQL User Manager 3.0 or newer

## Download
You can download the newest release at http://github.com/dukereborn/cmum-for-multics/releases/

## Installation
1. Download and extract the php script to your server
2. Open the php script in a texteditor and edit the config part
3. Edit mutlics to fetch users from file
4. Execute the php script to generate multics user files

cmum-for-multics config part
```
// mysql settings for cmum3 database
define("DBHOST",""); <- hostname for mysql server
define("DBUSER",""); <- mysql server username
define("DBPASS",""); <- mysql server password
define("DBNAME",""); <- cmum3 database name

// local settings
define("CHARSET","utf-8"); <- change to match cmum3 installation (utf-8 or utf-16)
define("TIMEZONE","Europe/London"); <- change to match your timezone, list found here http://php.net/manual/en/timezones.php

// multics settings
define("CCCAMFILE",""); <- full path to multics cccam user file, leave empty if not used
define("MGCAMDFILE",""); <- full path to multics mgcamd user file, leave empty if not used
define("NEWCAMDFILE",""); <- full path to multics newcamd user file, leave empty if not used
```

sample cmum-for-multics config
```
// mysql settings for cmum3 database
define("DBHOST","localhost");
define("DBUSER","admin");
define("DBPASS","password");
define("DBNAME","cmum3");

// local settings
define("CHARSET","utf-8");
define("TIMEZONE","Europe/London");

// multics settings
define("CCCAMFILE","/usr/share/multics/cccamusers");
define("MGCAMDFILE","/usr/share/multics/mgcamdusers");
define("NEWCAMDFILE","/usr/share/multics/newcamdusers");
```

## Change MultiCS to fetch users from file
Edit your multics.cfg to include users from file like this, only used fils are required
```
INCLUDE: "/usr/share/multics/cccamusers"
INCLUDE: "/usr/share/multics/mgcamdusers"
INCLUDE: "/usr/share/multics/newcamdusers"
```

## Running cmum-for-multics
There are some different ways on how to run cmum-for-multics
* one time
* by cronjob or scheduled task
* looped in a screen session

### one time
```
php cmumformultics.php
```

### by cronjob or scheduled task
```
setup your cronjob or scheduled task to execute the scrip in your given interval
```

### looped in a screen session
```
php cmumformultics.php -l <loop interval in seconds, default is 300>
```

## Contact me
If you find any bugs, got an idea or just wanna say "Hi!", send me a email on dukereborn@gmail.com. You can also follow me on twitter for updates and news http://www.twitter.com/dukereborn/

## Donations
All donations are welcome through paypal to account dukereborn@gmail.com

## Last words
This is a quick and ugly script, will try to make it better in the future.

## License
Released under the [MIT license](http://makesites.org/licenses/MIT)
