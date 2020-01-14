<?php
/**
 *
 * @author zhusaidong <zhusaidong@gmail.com>
 */

namespace FTPServer\Util;

class Os
{
	/**
	 * is windows system
	 *
	 * @return bool
	 */
	public static function isWindows()
	{
		return strpos(PHP_OS, 'WIN') !== false;
	}
}
