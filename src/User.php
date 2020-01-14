<?php
/**
 * User Object
 *
 * @author zhusaidong <zhusaidong@gmail.com>
 */
namespace FTPServer;

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
	 * set relative path
	 *
	 * @param string $path relative path
	 */
	public function setPath($path)
	{
		$path = str_replace('\\', '/', $path);
		if($path[0] !== '/')
		{
			$path = '/' . $path;
		}
		if(substr($path, -1) === '/')
		{
			$path = substr($path, 0, -1);
		}
		$this->path = $path;
	}
	
	/**
	 * set root path
	 *
	 * @param string $root set root path
	 */
	public function setRoot($root)
	{
		if(substr($root, -1) === '/')
		{
			$root = substr($root, 0, -1);
		}
		$this->root = $root;
	}
	
	/**
	 * get root path
	 *
	 * @param string $path
	 *
	 * @return string
	 */
	public function getRoot($path = '')
	{
		if($path !== '' && $path[0] !== '/')
		{
			$path = '/' . $path;
		}
		
		return $this->root . $path;
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
		if($path !== '' && $path[0] !== '/')
		{
			$path = '/' . $path;
		}
		
		return ($this->path . $path) ? : '/';
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
		if($path !== '' && $path[0] !== '/')
		{
			$path = '/' . $path;
		}
		if($this->path === '/')
		{
			$this->path = '';
		}
		
		return $this->root . $this->path . $path;
	}
}
