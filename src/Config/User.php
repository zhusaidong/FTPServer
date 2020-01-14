<?php
/**
 * User Config
 *
 * @author zhusaidong <zhusaidong@gmail.com>
 */
namespace FTPServer\Config;

use FTPServer\User as UserObject;
use Workerman\Worker;

class User
{
	/**
	 * @var array $users users
	 */
	private $users = [];
	
	/**
	 * __construct
	 */
	public function __construct()
	{
		$users = $this->readJson('user.json');
		if($users === null)
		{
			Worker::safeEcho('user.json error:' . json_last_error_msg() . PHP_EOL);
			exit;
		}
		foreach($users as $user)
		{
			$this->users[$user['username']] = new UserObject($user);
		}
	}
	
	/**
	 * read json
	 *
	 * @param string $jsonFile json file
	 *
	 * @return null|array json content
	 */
	private function readJson($jsonFile)
	{
		if(!file_exists($jsonFile))
		{
			return [];
		}
		
		return json_decode(file_get_contents($jsonFile), true);
	}
	
	/**
	 * get user info by username
	 *
	 * @param string $userName username
	 *
	 * @return UserObject
	 */
	public function getInfoByUserName($userName)
	{
		return isset($this->users[$userName]) ? $this->users[$userName] : null;
	}
}
