Lcd CMS
=================
### Introduce
Lcd CMS is small project build on **Phalcon framework 3.2**. It's apply to **8-Bit-Baseball's projects**. <br/>
Developer can use to develop powerful applications with low server consumption and high performance via Phalcon's architecture allows the framework to always be memory resident, offering its functionality whenever its needed, without expensive file stats and file reads that traditional PHP frameworks employ.<br/>
* Author: lechidungvl@gmail.com<br/>
* Blog: http://lcdung.top
<<<<<<< HEAD
* Document: updating

### Basic features
* Use HMVC, Repository Pattern
* Use multi modules
* Use multi languages (English package, Vietnamese package)
* Use configuration: development, staging, production
* Use vendor php via composer
* Use library private
* Use helper for view or controller of module
* Use memcached, APC cached
=======
>>>>>>> Add new version Lcd CMS 🎌

### Install required
* Phalcon 3.2+
* PHP >= 5.6 and 7.0 (Phalcon framework support)
* MySQL, MariaDB,... (Phalcon framework support)

### Get Started
#### Config on Nginx
```bash
	server {
	    listen 80;
	    server_name project-url;
	    root /path/project/public;
	    index index.php index.html index.htm;
	
	    location / {
	        if ($request_uri ~ (.+?\.php)(|/.+)$ ) {
	            break;
	        }
	
	        if (!-e $request_filename) {
	            rewrite ^/(.*)$ /index.php?_url=/$1;
	        }
	    }
	
	    location ~ \.php {
	        fastcgi_pass  unix:/tmp/php-cgi.sock;
	        fastcgi_index index.php;
	        include fastcgi_params;
	        set $real_script_name $fastcgi_script_name;
	        if ($fastcgi_script_name ~ "^(.+?\.php)(/.+)$") {
	            set $real_script_name $1;
	            set $path_info $2;
	        }
	        fastcgi_param SCRIPT_FILENAME $document_root$real_script_name;
	        fastcgi_param SCRIPT_NAME $real_script_name;
	        fastcgi_param PATH_INFO $path_info;
	    }
	
	    access_log  /path/logs/project/access.log  access;
	    error_log  /path/logs/project/error.log;
	}
```
* Change permissions the directory of "app/cache" ：chmod -R 0777 app/cache
* Modify the "$runtime" value in public / index.php (dev: Development, sta: Staging, pro: Production). The program will match the configuration (app/config/api/, app/config/system/) files required for different operating environments based on this variable
* backend login page(http://project-url/backend/index/index) username/password：admin/654321

#### Phalcon migration
* First, install **Phalcon Developer Tools** (https://olddocs.phalconphp.com/en/3.0.0/reference/tools.html)
* Folder .phalcon (root/.phalcon) must have full permission 
* Folder migrations (root/app/migrations) must have full permission
* Config connect DB on system_migration.php (root/app/config/system/system_migration.php)

```
# View help
phalcon migration --help

# Generate migration data
phalcon migration generate --config=app/config/system/system_migration.php --migrations=app/migrations --data=always

# Run migration data
phalcon migration run --config=app/config/system/system_migration.php --migrations=app/migrations --data=always
<<<<<<< HEAD
```
=======
```
>>>>>>> Add new version Lcd CMS 🎌
