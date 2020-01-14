<?php
/**
 * FTP Server
 *
 * @author zhusaidong <zhusaidong@gmail.com>
 */
namespace FTPServer;

use Workerman\Worker;
use FTPServer\Config\Server as ServerConfig;
use FTPServer\Command\Input;
use FTPServer\Command\Output;

class FTPServer
{
	/**
	 * @var boolean $log log
	 */
	private $log;
	/**
	 * @var ServerConfig $serverConfig server config
	 */
	protected $serverConfig;
	
	/**
	 * __construct
	 *
	 * @param boolean $log log
	 */
	public function __construct($log = false)
	{
		$this->log          = $log;
		$this->serverConfig = new ServerConfig;
		
		$root = $this->serverConfig->getConfig('server.root_path', '/');
		if(!file_exists($root))
		{
			mkdir($root, 644);
		}
	}
	
	/**
	 * log
	 *
	 * @param mixed   $msg        log msg
	 * @param boolean $fileAppend log append
	 */
	protected function log($msg, $fileAppend = true)
	{
		if($this->log)
		{
			is_array($msg) and $msg = var_export($msg, true);
			$msg = date('Y-m-d H:i:s') . PHP_EOL . $msg . PHP_EOL . PHP_EOL;
			file_put_contents('FTPServer.log', $msg, $fileAppend ? FILE_APPEND : 0);
		}
	}
	
	/**
	 * create server
	 *
	 * @return Worker server
	 */
	public function createServer()
	{
		$this->log('start FTPServer', false);
		
		$ip        = $this->serverConfig->getConfig('server.ip', 'localhost');
		$port      = $this->serverConfig->getConfig('server.port', 23);
		$max_users = $this->serverConfig->getConfig('server.max_users', 10);
		
		$worker        = new Worker("ftp://{$ip}:{$port}");
		$worker->name  = 'FTP Server';
		$worker->count = $max_users;
		
		return $worker;
	}
	
	/**
	 * run
	 */
	public function run()
	{
		$worker     = $this->createServer();
		$ftpCommand = new FTPCommand;
		
		$worker->onConnect = static function($connection)
		{
			$connection->user = null;
			$connection->send(new Output(220, 'welcome login ftp!'));
		};
		$worker->onMessage = function($connection, Input $input) use ($ftpCommand)
		{
			$output = $ftpCommand->command($connection, $input);
			$connection->send($output);
			
			$this->log('message=>' . $input . '=>' . $output);
		};
		$worker->onClose   = static function($connection)
		{
			$connection->send(new Output(426, 'logout'));
			$connection->close();
		};
		
		Worker::runAll();
	}
}
