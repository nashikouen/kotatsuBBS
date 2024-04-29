# kotatsuBBS
### info
This is an imageboard software that aims to be the best out there in terms of code readability and modularity.
We wont accept ugly code unless its in the form of a moduel amd you plan to maintain it.

# Required stack
KotatsuBBS is designed and tested on the following stack.<br>
Web server: nginx/httpd<br>
DB: mariadb<br>
PHP: PHP7.2-PHP8.3<br>
<sub><sub>just the most basic LAMP stack would work. the php version actually matters here as we are using fetures that are in 7.2+</sub></sub>


## installation guide for OpenBSD

install the required packages : 
```
pkg_add mariadb-server php php-mysqli php-gdb ffmpeg imagemagick
```
php8.2 is what i am going with foir this guide.

initalize and install  the mysql server 
then start it up and run the secure instalation script
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
remeber the username and password. you will need that for the configs
```mysql
CREATE DATABASE boarddb;
CREATE USER 'username'@'localhost' IDENTIFIED BY 'password';
GRANT ALL ON boarddb.* TO 'username'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

now with the data base set we will set up the httpd server.<br>
edit ``/etc/httpd.conf`` and add the fallowing
```
server "127.0.0.1" {
	listen on * port 80
	root "/htdocs/KotatsuBBS"
	directory index index.php
	location "*.php" {
		fastcgi socket "/run/php-fpm.sock"
	}

    # add some matching and redirects as we use pretty links.
    # [note] this will just redirect. use nginx or appachi with appropriate rewrite
    # /boardName
	location match "^/([a-zA-Z]+)/?$"{
		block return 302 \
		  "/bbs.php?boardNameID=%1"
	}
    # /boardName/thread/1234
	location match "^/([a-zA-Z]+)/thread/([0-9]+)/?$" {
		block return 302 \
		  "/bbs.php?boardNameID=%1&thread=%2"
	}
    # /boardName/1
	location match "^/([a-zA-Z]+)/([0-9]+)/?$" {
		block return 302 \
		  "/bbs.php?boardNameID=%1&page=%2"
	}
}
```

what we need to do now is add moduals to php to support mysqli and gd<br>
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

now inside of kotatsuBBS. edit ``conf .php`` file. make sure to set your mysql credentals you made earlier.

now you can enable and start all of the services<br>
```
rcctl enable php82_fpm mysqld httpd
rcctl start php82_fpm httlps
```
before hopping into the webview. go and edit this file and set some defaults ``boardConfigs/baseConf .php``

with everything set up. go to your website and go to /install.php
fallow the instructions and then delete instal.php off your webserver and everything should be set!.