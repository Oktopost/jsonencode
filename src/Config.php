<?php
namespace JsonEncode;


use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;


class Config
{
	private static $logger;
	
	
	public static function setLogger(LoggerInterface $logger): void
	{
		self::$logger = $logger;
	}
	
	public static function getLogger(): LoggerInterface
	{
		return self::$logger ?? new NullLogger();
	}
}
