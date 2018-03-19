<?php
class StandardJsonencodeTest extends JsonencodeTestCase
{
	public const INVALID_STRINGS 				= "hello \0,,\u{d83d},😠,\\,\",\n,\r,' world";
	public const EXPECTED_INVALID_JSON_ENCODING = "hello \\u0000,\\u001f,\u{d83d},😠,\\\\,\\\",\\n,\\r,' world";
	
	
	private static function invalidStr(): string
	{
		return self::INVALID_STRINGS;
	}
	
	private static function invalidStrEncoding($into = null): string
	{
		$str = self::EXPECTED_INVALID_JSON_ENCODING;
		
		if ($into)
			$str = str_replace('{inv}', $str, $into);
		
		return $str;
	}
	
	
	public function test_EmptyArray()
	{
		self::assertValueEncode([]);
	}
	public function test_EmptyObject()
	{
		self::assertValueEncode(new \stdClass());
	}
	public function test_EmptyString()
	{
		self::assertValueEncode('');
	}
	public function test_Null()
	{
		self::assertValueEncode(null);
	}
	
	public function test_Float()
	{
		self::assertValueEncode(0.2);
	}
	
	public function test_Int()
	{
		self::assertValueEncode(12);
	}
	
	public function test_Bool()
	{
		self::assertValueEncode(true);
		self::assertValueEncode(false);
	}
	
	public function test_Array()
	{
		self::assertValueEncode(['a', 'b', 'c']);
	}
	
	
	public function test_Depth()
	{
		self::assertValueEncode(['a' => ['b' => 23]], null, 1);
		self::assertValueEncode(['a' => ['b' => 23]], null, 5);
	}
	
	
	public function test_FlagsPassed()
	{
		self::assertValueEncode('"&<>\'\'ה\'',
			JSON_HEX_QUOT | 
			JSON_HEX_TAG | 
			JSON_HEX_AMP | 
			JSON_HEX_APOS | 
			JSON_UNESCAPED_UNICODE);
	}
	
	
	public function test_InvalidUnicodeStringPassed()
	{
		self::assertSame(self::invalidStrEncoding('"{inv}"'), jsonencode(self::invalidStr()));
	}
	
	public function test_InvalidUnicodeStringAsKeyPassed()
	{
		self::assertSame(
			self::invalidStrEncoding('{"{inv}":"a"}'), 
			jsonencode([self::invalidStr() => 'a'])
		);
	}
	
	public function test_InvalidUnicodeStringInsideAnArrayPassed()
	{
		self::assertSame(
			self::invalidStrEncoding('{"a":"{inv}"}'), 
			jsonencode(['a' => self::invalidStr()])
		);
	}
	
	public function test_AssocArrayConvertedCorrectly()
	{
		self::assertValueEncode(['a' => 'a', 'b' => 'b', 'c' => 'c']);
	}
	
	public function test_InconsistentNumericArrayConvertedCorrectly()
	{
		self::assertValueEncode([0 => 'a', 2 => 'b', 1 => 'c']);
	}
	
	public function test_PartiallyNumericArrayConvertedCorrectly()
	{
		self::assertValueEncode([0 => 'a', 'a' => 'b', 2 => 'c']);
		self::assertValueEncode(['a' => 'a', 1 => 'b', 2 => 'c']);
	}
	
	public function test_NumericObjectNameWithInvalidValueParsedAsStringKey()
	{
		self::assertSame(
			self::invalidStrEncoding('{"1":"{inv}"}'), 
			jsonencode([1 => self::invalidStr()])
		);
	}
	
	
	public function test_sanity()
	{
		$rand = random_bytes(10240);
		$res = jsonencode($rand);
		
		self::assertNotFalse($res);
		self::assertNotNull($res);
		self::assertTrue(strlen($res) >= 10240);
	}
}


