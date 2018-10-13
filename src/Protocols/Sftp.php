<?php
/**
* SFTP Protocol
* 
* @author zhusaidong [zhusaidong@gmail.com]
*/
namespace Protocols;

use Workerman\Connection\ConnectionInterface;
use Workerman\Protocols\ProtocolInterface;
use FTPServer\Command\Input;
use FTPServer\Command\Output;
use FTPServer\Config\Server as ServerConfig;

class SFtp implements ProtocolInterface
{
	/**
	* Check the integrity of the package.
	* Please return the length of package.
	* If length is unknow please return 0 that mean wating more data.
	* If the package has something wrong please return false the connection will be closed.
	*
	* @param ConnectionInterface $connection
	* @param string              $recv_buffer
	* @return int|false
	*/
	public static function input($recv_buffer, ConnectionInterface $connection)
	{
		return strlen($recv_buffer);
	}

	/**
	* Decode package and emit onMessage($message) callback, $message is the result that decode returned.
	*
	* @param ConnectionInterface $connection
	* @param string              $recv_buffer
	* @return mixed
	*/
	public static function decode($recv_buffer, ConnectionInterface $connection)
	{
		/*
		$serverConfig = new ServerConfig;
		
		$cert = $serverConfig->getConfig('sftp.local_cert','/server.pem');
		
		openssl_public_decrypt($recv_buffer,$decryptData,file_get_contents($cert));
		$recv_buffer = $decryptData;
		*/
		return new Input(trim($recv_buffer));
	}

	/**
	* Encode package brefore sending to client.
	*
	* @param ConnectionInterface $connection
	* @param mixed               $data
	* @return string
	*/
	public static function encode($data, ConnectionInterface $connection)
	{
		$data instanceof Output and $data = $data->getCode().' '.$data->getMsg();
		/*
		$serverConfig = new ServerConfig;
		
		$pk = $serverConfig->getConfig('sftp.local_pk','/server.key');
		
		openssl_private_encrypt($data,$encryptData,file_get_contents($pk));
		$data = $encryptData;
		*/
		return $data.PHP_EOL;
	}
}
