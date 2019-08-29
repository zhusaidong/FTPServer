# php ftp server

基于Workerman开发的FTPServer

[english document](README.md)

### ftp协议文档

[ftp协议](http://cr.yp.to/ftp.html)

### 服务器配置

`server.ini`


```ini
[server]
;ip address
ip=0.0.0.0
;port
port=2323
;根目录
root_path=data
;最大连接数
max_users=1
;是否允许匿名登录
allow_anonymous=1
;被动模式端口范围
pasv_port_range=50000-60000
```

### 用户配置

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

### 用法

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
