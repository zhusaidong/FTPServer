<?php
/**
* FTP Command Controller
* 
* @author zhusaidong <zhusaidong@gmail.com>
*/
namespace FTPServer;

use FTPServer\Command\Input;
use FTPServer\Command\Output;
use FTPServer\Config\Server as ServerConfig;
use FTPServer\Config\User as UserConfig;
use FTPServer\User;
use Workerman\Connection\ConnectionInterface;
use Workerman\Connection\TcpConnection;
use Workerman\Worker;
use DirectoryIterator;

class FTPCommand
{
	/**
	* @var boolean $log log
	*/
	private $log = FALSE;
	/**
	* @var mixed $serverConfig server config
	*/
	private $serverConfig = NULL;
	
	public function __construct()
	{
		$this->serverConfig = new ServerConfig;
	}
	/**
	* run command
	* 
	* @param \Workerman\Connection\ConnectionInterface $connection
	* @param \FTPServer\Command\Input $input
	* 
	* @return \FTPServer\Command\Output Output
	*/
	public function command(ConnectionInterface $connection,Input $input)
	{
		$method = '_'.strtolower($input->getCommand());
		if(method_exists($this,$method))
		{
			$param = $input->getParameter();
			PHP_OS == 'WINNT' and $param = mb_convert_encoding($param,'utf-8','gb2312');
			return call_user_func_array([$this,$method],[$connection,$param]);
		}
		else
		{
			return new Output(502,'command not support!');
		}
	}
	
	/**
	* get directory list
	* 
	* @param string $path directory path
	* 
	* @return array directory list
	*/
	private function getDirectoryLists($path)
	{
		$lists = [];
		if(!is_dir($path))
		{
			return $lists;
		}
		foreach(new DirectoryIterator($path) as $iterator)
		{
			if(!$iterator->isDot())
			{
				if(PHP_OS == 'WINNT')
				{
					$groupName = $ownerName = get_current_user();
				}
				else
				{
					$owner  = posix_getpwuid($iterator->getOwner());
					$group = posix_getgrgid($iterator->getGroup());
					
					$ownerName = $owner['name'];
					$groupName = $group['name'];
				}
				
				$isDir = $iterator->isDir() ? 'd' : '-';
				//$octal_perms = substr(sprintf('%o', $iterator->getPerms()), -4);//权限
				$lists[] = [
					'permission'=>$isDir.'rw-r--r--',		//权限
					'hard_link' =>0,						//文件硬链接数
					'owner'		=>$ownerName,				//用户
					'group'		=>$groupName,				//组
					'size'		=>$iterator->getSize(),		//大小
					'time'		=>$iterator->getMTime(),	//修改时间
					'name'		=>$iterator->getFilename(),	//文件名
				];
			}
		}
		return $lists;
	}
	/**
	* get formatted directory list
	* 
	* @param string $path directory path
	* @param string $format directory format
	* 
	* @return array directory list
	*/
	private function _dirFormattedList($path,$format = '')
	{
		$lists = [];
		foreach($this->getDirectoryLists($path) as $iterator)
		{
			list(
				$permission,
				$hard_link,
				$owner,
				$group,
				$size,
				$time,
				$name
			) = array_values($iterator);
			
			//http://cr.yp.to/ftp/list/binls.html
			switch($format)
			{
				case '-l':
					//不带.的文件
					if(substr($name,0,1) == '.')
					{
						break 2;
					}
					
					$year = date('Y',$time);
					//六个月内修改的文件显示时分秒
					if(time() - $time <= 6 * 30 * 86400)
					{
						$year = date('H:i',$time);
					}
					$ls = [
						$permission,
						$hard_link,
						$owner,
						$group,
						$size,
						date('d',$time),
						date('M',$time),
						$year,
						$name,
					];
					break;
				case '-al':
					$year = date('Y',$time);
					//六个月内修改的文件显示时分秒
					if(time() - $time <= 6 * 30 * 86400)
					{
						$year = date('H:i',$time);
					}
					$ls = [
						$permission,
						$hard_link,
						$owner,
						$group,
						$size,
						date('d',$time),
						date('M',$time),
						$year,
						$name,
					];
					break;
				case 'nlst':
					$year = date('Y',$time);
					//六个月内修改的文件显示时分秒
					if(time() - $time <= 6 * 30 * 86400)
					{
						$year = date('H:i',$time);
					}
					$ls = [
						$name,
					];
					break;
				default:
					$ls = [
						$permission,
						$hard_link,
						$owner,
						$group,
						$size,
						date('Y',$time),
						date('M',$time),
						date('d',$time),
						$name,
					];
					break;
			}
			$lists[] = implode(' ',$ls);
		}
		return $lists;
	}
	
	//command methods
	/**
	* username
	*/
	private function _user($connection,$args)
	{
		if($args == 'anonymous' or $args == '')
		{
			if($this->serverConfig->getConfig('server.allow_anonymous',false))
			{
				$root = $this->serverConfig->getConfig('server.root_path','/');
				
				$connection->user = new User([]);
				$connection->user->setRoot($root);
				$connection->user->setRoot($connection->user->getRootPath());
				$connection->user->setPath('/');
				
				if(!file_exists($connection->user->getRootPath()))
				{
					mkdir($connection->user->getRootPath(),644);
				}
				
				return new Output(230,'anonymous successfully logged in');
			}
			else
			{
				return new Output(530,'server not allow anonymous');
			}
		}
		else
		{
			$userConfig = new UserConfig;
			$info = $userConfig->getInfoByUserName($args);
			if($info == NULL or !$info->status)
			{
				return new Output(530,'login error');
			}
			else
			{
				$connection->user = $info;
				return new Output(331,'passwd required for'.$args);
			}
		}
	}
	/**
	* password
	*/
	private function _pass($connection,$args)
	{
		$user = $connection->user;
		if($user == NULL or $user->password != $args)
		{
			return new Output(530,'not logged in, user or password incorrect');
		}
		else
		{
			$root = $this->serverConfig->getConfig('server.root_path','/');
			
			$connection->user->setRoot($root);
			$connection->user->setRoot($connection->user->getRootPath());
			$connection->user->setPath('/');
			
			if(!file_exists($connection->user->getRootPath()))
			{
				mkdir($connection->user->getRootPath(),644);
			}
			$connection->user->isBinary = FALSE;
			
			return new Output(202,'user successfully logged in');
		}
	}
	/**
	* directory details list
	*/
	private function _list($connection,$args)
	{
		$connection->send(new Output(125,'Opening data connection for directory list.'));
		
		$lists = $this->_dirFormattedList($connection->user->getRootPath(),$args);
		$msg = implode(PHP_EOL,$lists).PHP_EOL;
		
		if($connection->user->mode == 'port')
		{
			$connection->user->port_connection->send($msg);
			$connection->user->port_connection->close();
			$connection->user->port_connection = NULL;
		}
		else
		{
			if(($pasv_connection = current($connection->user->pasv_worker->connections)) !== FALSE)
			{
				$pasv_connection->send($msg);
				$pasv_connection->close();
			}
			$connection->user->pasv_worker->unlisten();
			$connection->user->pasv_worker = NULL;
		}
		
		return new Output(226,'Transfer complete');
	}
	/**
	* directory list
	*/
	private function _nlst($connection,$args)
	{
		return $this->_list($connection,empty($args) ? 'nlst' : $args);
	}
	/**
	* port mode
	*/
	private function _port($connection,$args)
	{
		$ip_port = explode(',',$args);
		$ip = implode('.',array_slice($ip_port,0,4));
		$port = $ip_port[4] << 8 | $ip_port[5];
		
		$connection->user->mode = 'port';
		
		if(($socket = @stream_socket_client("tcp://".$ip.':'.$port)) != FALSE)
		{
			$tcpConnection = new TcpConnection($socket);
			$tcpConnection->connect();
			
			$connection->user->port_connection = $tcpConnection;
			
			return new Output(200,'PORT command successful.');
		}
		else
		{
			return new Output(502,'unable to connect client!');
		}
	}
	/**
	* pasv mode
	*/
	private function _pasv($connection,$args)
	{
		$ip = $this->serverConfig->getConfig('server.ip','localhost');
		$pasv_port_range = $this->serverConfig->getConfig('server.pasv_port_range','50000');
		
		$_port_range = explode('-',$pasv_port_range);
		$random_port = random_int($_port_range[0],count($_port_range) >= 2 ? $_port_range[1] : $_port_range[0]);

		$port1 = floor($random_port / 256);
		$port2 = $random_port % 256;
		
		$msg = implode(',',[str_replace('.',',',$ip),$port1,$port2]);
		
		$connection->user->mode = 'pasv';
		
		$inner_worker = new Worker('tcp://'.$ip.':'.$random_port);
		$inner_worker->name = 'pasv Worker';
		$inner_worker->count = 1;
	    $inner_worker->listen();
		$connection->user->pasv_worker = $inner_worker;
		
		return new Output(227,$msg);
	}
	/**
	* change work dir
	*/
	private function _cwd($connection,$args)
	{
		if($args == '.')
		{
			return new Output(250,'CWD command successful.');
		}
		else if($args == '..')
		{
			return $this->_cdup($connection,$args);
		}
		else if(substr($args,0,1) == '/')
		{
			$rootPath = $connection->user->getRoot($args);
			$path = $args;
		}
		else
		{
			$rootPath = $connection->user->getRootPath($args);
			$path = $connection->user->getPath($args);
		}
		
		if(!file_exists($rootPath))
		{
			return new Output(550,$args.':No such file or directory.');
		}
		else
		{
			$connection->user->setPath($path);
			return new Output(250,'CWD command successful.');
		}
	}
	/**
	* print work dir
	*/
	private function _pwd($connection,$args)
	{
		return new Output(257,'"'.$connection->user->getPath().'" is current directory.');
	}
	/**
	* windows ftp's print work dir
	*/
	private function _xpwd($connection,$args)
	{
		return $this->_pwd($connection,$args);
	}
	/**
	* change to upper dir
	*/
	private function _cdup($connection,$args)
	{
		$connection->user->setPath(dirname($connection->user->getPath()));
		return new Output(250,'CWD command successful.');
	}
	/**
	* download
	*/
	private function _retr($connection,$args)
	{
		$user = $connection->user;
		
		$path = $user->getRootPath($args);
		
		if(!file_exists($path))
		{
			return new Output(550,$args.':No such file or directory.');
		}
		
		$connection->send(new Output(115,'Opening data connection'));
		
		$fmode = $user->isBinary ? 'rb' : 'r';
		
		$handle = fopen($path,$fmode);
		
		$offset = isset($user->download_size)?$user->download_size:0;
		fseek($handle, $offset);
		
		if($user->mode == 'port')
		{
			$port_connection = $user->port_connection;
			$port_connection->maxSendBufferSize = filesize($path);
			
			$buffer = 1024 * 1024;
			while(!feof($handle))
			{
				$msg = fread($handle,$buffer);
				$port_connection->send($msg);
			}
			$port_connection->close();
			$connection->user->port_connection = NULL;
		}
		else
		{
			$pasv_connection = current($user->pasv_worker->connections);
			$pasv_connection->maxSendBufferSize = filesize($path);
			
			$buffer = 1024 * 1024;
			while(!feof($handle))
			{
				$msg = fread($handle,$buffer);
				$pasv_connection->send($msg);
			}
			$pasv_connection->close();
			$user->pasv_worker->unlisten();
			$connection->user->pasv_worker = NULL;
		}
		fclose($handle);
		
		return new Output(226,'Transfer complete');
	}
	/**
	* server info
	*/
	private function _syst($connection,$args)
	{
		return new Output(215,PHP_OS);
	}
	/**
	* server status
	*/
	private function _stat($connection,$args)
	{
		$status = "FTP server status:".PHP_EOL."Version 1.0.0";
		return new Output(211,$status);
	}
	/**
	* quit ftp
	*/
	private function _quit($connection,$args)
	{
		return new Output(221,'Bye.');
	}
	/**
	* make dir
	*/
	private function _mkd($connection,$args)
	{
		$path = $connection->user->getRootPath($args);
		mkdir($path);
		return new Output(257,'"'.$path.'" create successful');
	}
	/**
	* delete file
	*/
	private function _dele($connection,$args)
	{
		unlink($connection->user->getRootPath($args));
		return new Output(250,'delete file successful');
	}
	/**
	* remove dir
	*/
	private function _rmd($connection,$args)
	{
		rmdir($connection->user->getRootPath($args));
		return new Output(250,'delete folder successful');
	}
	/**
	* begin renaming a file
	*/
	private function _rnfr($connection,$args)
	{
		$connection->user->need_rename = $connection->user->getRootPath($args);
		return new Output(350,'ok');
	}
	/**
	* finish renaming a file
	*/
	private function _rnto($connection,$args)
	{
		rename($connection->user->need_rename,$connection->user->getRootPath($args));
		$connection->user->need_rename = NULL;
		return new Output(250,'ok');
	}
	/**
	* upload
	*/
	private function _stor($connection,$args,$upload_append = FALSE)
	{
		$user = $connection->user;
		
		$connection->send(new Output(115,'Opening data connection'));
		
		$filePath = $user->getRootPath($args);
		if(!$upload_append)
		{
			if(is_file($filePath))
			{
				unlink($filePath);
			}
		}
		
		if($user->mode == 'port')
		{
			$port_connection = $user->port_connection;
			
			$port_connection->onClose = function($port_connection)use($connection)
			{
				$port_connection->close();
				$connection->user->port_connection = NULL;
			};
			$port_connection->onMessage = function($port_connection,$data)use($filePath)
			{
				file_put_contents($filePath,$data,FILE_APPEND);
			};
			
		}
		else
		{
			$pasv_connection = current($user->pasv_worker->connections);
			
			$pasv_connection->onClose = function($pasv_connection)use($connection)
			{
				$pasv_connection->close();
				$connection->user->pasv_worker->unlisten();
				$connection->user->pasv_worker = NULL;
			};
			$pasv_connection->onMessage = function($pasv_connection,$data)use($filePath)
			{
				file_put_contents($filePath,$data,FILE_APPEND);
			};
		}
		
		return new Output(226,'Transfer complete');
	}
	/**
	* broken point append upload
	*/
	private function _appe($connection,$args)
	{
		$this->_stor($connection,$args,TRUE);
	}
	/**
	* transfer type
	*/
	private function _type($connection,$args)
	{
		/*
		选择传输类型
			A为文本模式
			I为二进制模式
			E为EBCDIC
			N为Nonprint非打印模式
			T为Telnet格式控制符
		*/
		if($args == "I")
		{
			$connection->user->isBinary = TRUE;
			return new Output(200,'Type set to I(Binary)');
		}
		else
		{
			$connection->user->isBinary = FALSE;
			return new Output(200,'Type set to A(ASCII)');
		}
	}
	/**
	* file size
	*/
	private function _size($connection,$args)
	{
		$path = $connection->user->getRootPath().$args;
		$size = file_exists($path)?@filesize($path):0;
		return new Output(213,$size);
	}
	/**
	* file modification time
	*/
	private function _mdtm($connection,$args)
	{
		$time = @filemtime($connection->user->getRootPath().$args);
		return new Output(213,$time);
	}
	/**
	* transfer start position
	*/
	private function _rest($connection,$args)
	{
		if($args != 0 and $args != 100)
		{
			$connection->user->download_size = $args;
		}
		return new Output(213,'ok');
	}
}
