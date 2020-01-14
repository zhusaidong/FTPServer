<?php
/**
 * User Object
 *
 * @author zhusaidong <zhusaidong@gmail.com>
 */
namespace FTPServer;

use FTPServer\Util\Os;

class User
{
	/**
	 * @var string $username username
	 */
	public $username;
	/**
	 * @var string $password password
	 */
	public $password;
	/**
	 * @var string $path relative path
	 */
	public $path = '';
	/**
	 * @var string $root root path
	 */
	public $root = '';
	/**
	 * @var bool $status user status
	 */
	public $status = true;
	
	/**
	 * __construct
	 *
	 * @param array $user user
	 */
	public function __construct($user)
	{
		foreach($user as $key => $value)
		{
			$this->{$key} = $value;
		}
	}
	
	/**
	 * get os real path
	 *
	 * @param $path
	 *
	 * @return string|string[]
	 */
	private function getOsPath($path)
	{
		return str_replace(Os::isWindows() ? '/' : '\\', DIRECTORY_SEPARATOR, $path);
	}
	
	/**
	 * set relative path
	 *
	 * @param string $path relative path
	 */
	public function setPath($path)
	{
		$this->path = trim($this->getOsPath($path), DIRECTORY_SEPARATOR);
	}
	
	/**
	 * set root path
	 *
	 * @param string $root set root path
	 */
	public function setRoot($root)
	{
		$root = trim($this->getOsPath($root), DIRECTORY_SEPARATOR);
		//windows dir like d:
		if(strlen($root) > 1 && $root[1] === ':')
		{
			$this->root = $root;
		}
		else
		{
			$this->root = __DIR__ . DIRECTORY_SEPARATOR . $root;
		}
	}
	
	/**
	 * get root path
	 *
	 * @return string
	 */
	public function getRoot()
	{
		return $this->root;
	}
	
	/**
	 * get relative path
	 *
	 * @param string $path
	 *
	 * @return string
	 */
	public function getPath($path = '')
	{
		return trim($this->path . DIRECTORY_SEPARATOR . trim($path, DIRECTORY_SEPARATOR), DIRECTORY_SEPARATOR);
	}
	
	/**
	 * get absolute path
	 *
	 * @param string $path
	 *
	 * @return string
	 */
	public function getRootPath($path = '')
	{
		return rtrim($this->getRoot() . DIRECTORY_SEPARATOR . $this->getPath($path), DIRECTORY_SEPARATOR);
	}
}
