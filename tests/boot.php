<?php
class JsonencodeTestCase extends PHPUnit\Framework\TestCase
{
	public static function assertValueEncode($value, $options = null, int $depth = 512)
	{
		$a = json_encode($value, $options, $depth);
		$aError = json_last_error();
		
		$b = jsonencode($value, $options, $depth);
		$bError = json_last_error();
		
		self::assertSame($a, $b);
		self::assertEquals($aError, $bError);
	}
}