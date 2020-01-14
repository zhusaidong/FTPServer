<?php
/**
 * FTP Output
 *
 * @author zhusaidong <zhusaidong@gmail.com>
 */
namespace FTPServer\Command;

class Output
{
	/**
	 * @var int $code response code
	 */
	private $code;
	/**
	 * @var string $msg response msg
	 */
	private $msg;
	
	/**
	 * __construct
	 *
	 * @param int    $code response code
	 * @param string $msg  response msg
	 */
	public function __construct($code = 0, $msg = '')
	{
		$this->code = $code;
		$this->msg  = $msg;
	}
	
	/**
	 * response code
	 *
	 * @return int
	 */
	public function getCode()
	{
		return $this->code;
	}
	
	/**
	 * response msg
	 *
	 * @return string
	 */
	public function getMsg()
	{
		return $this->msg;
	}
	
	/**
	 * __toString
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->code . ':' . $this->msg;
	}
}
