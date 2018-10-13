<?php
/**
* SFTP Server
* 
* @author zhusaidong [zhusaidong@gmail.com]
*/
namespace FTPServer;

use Workerman\Worker;
use FTPServer\Config\Server as ServerConfig;
use FTPServer\Config\User as UserConfig;
use FTPServer\User;
use FTPServer\FTPCommand;
use FTPServer\Command\Input;
use FTPServer\Command\Output;
use FTPServer\FTPServer;

class SFTPServer extends FTPServer
{
	/**
	* create server
	* 
	* @return \Workerman\Worker server
	*/
	public function createServer()
	{
		$this->log('start SFTPServer',FALSE);
		
		$ip        = $this->serverConfig->getConfig('server.ip','localhost');
		$port      = $this->serverConfig->getConfig('server.port',23);
		$max_users = $this->serverConfig->getConfig('server.max_users',10);
		$cert      = $this->serverConfig->getConfig('sftp.local_cert','/server.pem');
		$pk        = $this->serverConfig->getConfig('sftp.local_pk','/server.key');
		
		$context = [
		    'ssl'=>[
		        'local_cert'		=>$cert,
		        'local_pk'			=>$pk,
		        'verify_peer'		=>FALSE,
		        'allow_self_signed'	=>TRUE,
		    ]
		];
		$worker = new Worker("sftp://{$ip}:{$port}",$context);
		$worker->transport = 'ssl';
		$worker->name = 'SFTP Server';
		$worker->count = $max_users;
		
		return $worker;
	}
}
