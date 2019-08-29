<?php
/**
 * Server Config
 *
 * @author zhusaidong <zhusaidong@gmail.com>
 */
namespace FTPServer\Config;

class Server
{
	/**
	 * @var array $config config
	 */
	private $config = [];
	
	/**
	 * __construct
	 */
	public function __construct()
	{
		$this->config = $this->readIni('server.ini');
	}
	
	/**
	 * read ini
	 *
	 * @param string $iniFile ini file
	 *
	 * @return array ini content
	 */
	private function readIni($iniFile)
	{
		if(!file_exists($iniFile))
		{
			return [];
		}
		
		return parse_ini_file($iniFile, TRUE);
	}
	
	/**
	 * get config
	 *
	 * @param string $key     key
	 * @param mixed  $default default
	 *
	 * @return mixed config
	 */
	public function getConfig($key, $default = NULL)
	{
		$config = $this->config;
		foreach(explode('.', $key) as $subKey)
		{
			$config = isset($config[$subKey]) ? $config[$subKey] : $default;
		}
		
		return $config;
	}
}
