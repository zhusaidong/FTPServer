<?php
/**
* FTP Input
* 
* @author zhusaidong [zhusaidong@gmail.com]
*/
namespace FTPServer\Command;

class Input
{
	/**
	* @var string $command ftp command
	*/
	private $command = '';
	/**
	* @var string $parameter ftp command parameter
	*/
	private $parameter = '';
	
	/**
	* __construct
	* 
	* @param string $data ftp original  command
	*/
	public function __construct($data = NULL)
	{
		if($data != NULL)
		{
			set_error_handler(function(){});
			@list($command,$parameter) = explode(' ',$data,2);
			restore_error_handler();
			$this->command = $command;
			$this->parameter = $parameter;
		}
	}
	/**
	* get command
	* 
	* @return string
	*/
	public function getCommand()
	{
		return $this->command;
	}
	/**
	* get command parameter
	* 
	* @return string
	*/
	public function getParameter()
	{
		return $this->parameter;
	}
	/**
	* __toString
	* 
	* @return string
	*/
	public function __toString()
	{
		return $this->command.':'.$this->parameter;
	}
}
