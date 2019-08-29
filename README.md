# php ftp server

PHP FTP server based on workerman

[中文文档](README-zh.md)

### ftp protocol document

[ftp protocol](http://cr.yp.to/ftp.html)

### server config

`server.ini`


```ini
[server]
;ip address
ip=0.0.0.0
;port
port=2323
;root path
root_path=data
;max users
max_users=1
;allow anonymous
allow_anonymous=1
;ftp pasv mode port range
pasv_port_range=50000-60000
```

### user config

`user.json`

```json
{
	"test": {
		"username": "test",
		"password": "123",
		"path": "/",
		"status": true
	}
}
```

### usage

```
composer require zhusaidong/ftp-server:dev-master
```

```php
//ftp.php
require_once('./vendor/autoload.php');

use FTPServer\FTPServer;

$fs = new FTPServer();
$fs->run();
```

```
php ftp.php
```

### TODO

- [ ] support sftp
