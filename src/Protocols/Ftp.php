<?php
/**
 * FTP Protocol
 *
 * @author zhusaidong <zhusaidong@gmail.com>
 */
namespace Protocols;

use Workerman\Connection\ConnectionInterface;
use Workerman\Protocols\ProtocolInterface;
use FTPServer\Command\Input;
use FTPServer\Command\Output;

class Ftp implements ProtocolInterface
{
	/**
	 * Check the integrity of the package.
	 * Please return the length of package.
	 * If length is unknow please return 0 that mean wating more data.
	 * If the package has something wrong please return false the connection will be closed.
	 *
	 * @param ConnectionInterface $connection
	 * @param string              $recv_buffer
	 *
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
	 *
	 * @return mixed
	 */
	public static function decode($recv_buffer, ConnectionInterface $connection)
	{
		return new Input(trim($recv_buffer));
	}
	
	/**
	 * Encode package brefore sending to client.
	 *
	 * @param ConnectionInterface $connection
	 * @param mixed               $data
	 *
	 * @return string
	 */
	public static function encode($data, ConnectionInterface $connection)
	{
		$data instanceof Output and $data = $data->getCode() . ' ' . $data->getMsg();
		
		return $data . PHP_EOL;
	}
}
