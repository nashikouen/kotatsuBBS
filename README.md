# kotatsuBBS
### info
This is an imageboard software that aims to be the best out there in terms of code readability and modularity.
We wont accept ugly code unless its in the form of a moduel amd you plan to maintain it.

# Required stack
KotatsuBBS is designed and tested on the following stack.<br>
Web server: nginx/httpd<br>
DB: mariadb<br>
PHP: PHP8.2-PHP8.3<br>
<sub><sub>just the most basic LAMP stack would work. the php version actually matters here as we are using fetures that are in 8.2+</sub></sub>


## installation guide for OpenBSD

install the required packages : 
```
pkg_add mariadb-server php php-mysqli php-gdb ffmpeg composer
```
php8.2 is what i am going with for this guide.
since OpenBSD runs php in a chroot. you will need to copy ffmpeg and all of its dependecies into the chroot.
here is a script that can do that.
```
#!/bin/sh

# Define the paths
CHROOT_DIR="/var/www"
FFMPEG_BIN=$(which ffmpeg)

# Function to copy files safely
safecopy() {
    src=$1
    dest=$2
    if [ -f "$src" ]; then
        doas mkdir -p "$(dirname $dest)"
        doas cp "$src" "$dest"
    else
        echo "File $src not found."
    fi
}

# Copy FFmpeg binary
safecopy "$FFMPEG_BIN" "$CHROOT_DIR$FFMPEG_BIN"

# Copy dependencies
for lib in $(ldd "$FFMPEG_BIN" | awk '{print $7}' | grep '^/'); do
    safecopy "$lib" "$CHROOT_DIR$lib"
done

# Copy /bin/sh if not present
safecopy "/bin/sh" "$CHROOT_DIR/bin/sh"

echo "All necessary files have been copied to $CHROOT_DIR."

```

initalize and install  the mysql server.
then start it up and run the secure instalation script.
```
mysql_install_db 
rcctl start mysqld
mysql_secure_installation
```


log into mysql as root 
```
mysql -u root -p
```
you will now need to create a database and a user account.
remeber the username and password as you will need that for the configs.
```mysql
CREATE DATABASE boarddb;
CREATE USER 'username'@'localhost' IDENTIFIED BY 'password';
GRANT ALL ON boarddb.* TO 'username'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

now with the database set we will set up the httpd server.<br>
edit ``/etc/httpd.conf`` and add the fallowing
```
server "127.0.0.1" {
	listen on * port 80
	root "/htdocs/kotatsuBBS"
	directory index index.php

    # file upload max size 60 in this case
	connection max request body 62914560 

	location "*.php" {
		fastcgi socket "/run/php-fpm.sock"
	}

    # add some matching and rewrites as we expect these pretty links.

    # board
    # /boardName
	location match "^/([a-zA-Z]+)/?$"{
		request rewrite \
		  "/bbs.php?boardNameID=%1"
	}

    # threads
    # /boardName/thread/1234
	location match "^/([a-zA-Z]+)/thread/([0-9]+)/?$" {
		request rewrite \
		  "/bbs.php?boardNameID=%1&thread=%2"
	}

    # pages
    # /boardName/1
	location match "^/([a-zA-Z]+)/([0-9]+)/?$" {
		request rewrite \
		  "/bbs.php?boardNameID=%1&page=%2"
	}

    # admin page
    # /boardname/admin
	location match "^/([a-zA-Z]+)/admin/?$" {
		request rewrite \
		  "/admin.php?boardNameID=%1"
	}

    # ban page by postID
    # /boardname/admin/ban/123
	location match "^/([a-zA-Z]+)/admin/ban/([0-9]+)/?$" {
		request rewrite \
		  "/admin.php?boardNameID=%1&action=banPost&postID=%2"
	}
}

```

what we need to do now is enable the php moduels we installed.<br>
edit the ``/etc/php-8.2.ini`` and find the extensions section and uncomment the fallowing<br>
```
extension=gd
extention=mysqli
```
while you are in this file, you should also change the max upload. by defualt php caps it to 2mb. 
```
upload_max_filesize = 10M
post_max_size = 12M
```
now you need to allow data to come into the server
edit ``/etc/pf.conf`` and add this line
```
pass in on egress proto tcp from any to any port { 80 443 }
```
and run ``pfctl -f /etc/pf.conf`` to reload your firewall rules
```
pfctl -f /etc/pf.conf
```
now inside of kotatsuBBS. you are going to want to install the composter stuff. ``composer install``
next you want to edit ``conf .php`` file. make sure to set your mysql credentals you made earlier.

now you can enable and start all of the services<br>
```
rcctl enable php82_fpm mysqld httpd
rcctl start php82_fpm httpd
```
before hopping into the webview. go and edit the fallowing file and set some defaults ``boardConfigs/baseConf .php``

with everything set up. go to your website and go to install.php
fallow the instructions and then delete instal.php off your webserver and everything should be set!