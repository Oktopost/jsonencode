<?php
class JsonencodeTestCase extends PHPUnit\Framework\TestCase
{
	public const INVALID_STRINGS 				= "hello \0,,\u{d83d},😠,\\,\",\n,\r,' world";
	public const INVALID_CHAR 					= "\u{d83d}";
	public const EXPECTED_INVALID_JSON_ENCODING = "hello \\u0000,\\u001f,\u{d83d},😠,\\\\,\\\",\\n,\\r,' world";
	
	
	public static function invalidStr(): string
	{
		return self::INVALID_STRINGS;
	}
	
	public static function invalidStrEncoding($into = null): string
	{
		$str = self::EXPECTED_INVALID_JSON_ENCODING;
		
		if ($into)
			$str = str_replace('{inv}', $str, $into);
		
		return $str;
	}
	
	public static function assertValueEncode($value, $options = null, int $depth = 512)
	{
		$a = json_encode($value, $options, $depth);
		$aError = json_last_error();
		
		$b = jsonencode($value, $options, $depth);
		$bError = json_last_error();
		
		self::assertSame($a, $b);
		self::assertEquals($aError, $bError);
	}
	
	public static function assertValueDecode($value)
	{
		if ($value instanceof \stdClass)
		{
			self::assertEquals($value, jsondecode(jsonencode($value)));
		}
		else
		{
			self::assertSame($value, jsondecode(jsonencode($value)));
		}
	}
}