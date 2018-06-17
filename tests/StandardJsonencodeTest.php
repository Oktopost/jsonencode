<?php
class StandardJsonencodeTest extends JsonencodeTestCase
{
	public function test_EmptyArray(): void
	{
		self::assertValueEncode([]);
	}
	
	public function test_EmptyObject(): void
	{
		self::assertValueEncode(new \stdClass());
	}
	
	public function test_EmptyString(): void
	{
		self::assertValueEncode('');
	}
	
	public function test_Null(): void
	{
		self::assertValueEncode(null);
	}
	
	public function test_Float(): void
	{
		self::assertValueEncode(0.2);
	}
	
	public function test_Int(): void
	{
		self::assertValueEncode(12);
	}
	
	public function test_Bool(): void
	{
		self::assertValueEncode(true);
		self::assertValueEncode(false);
	}
	
	public function test_Array(): void
	{
		self::assertValueEncode(['a', 'b', 'c']);
	}
	
	
	public function test_InvalidStringWithArray_WithForceJsonObject(): void
	{
		self::assertSame(
			self::invalidStrEncoding('{"0":"a","1":"{inv}"}'), 
			jsonencode(['a', self::INVALID_STRINGS], JSON_FORCE_OBJECT)
		);
	}
	
	public function test_InvalidStringWithArray_WithoutForceJsonObject(): void
	{
		self::assertSame(
			self::invalidStrEncoding('["a","{inv}"]'), 
			jsonencode(['a', self::INVALID_STRINGS])
		);
	}
	
	
	public function test_Depth(): void
	{
		self::assertValueEncode(['a' => ['b' => 23]], null, 1);
		self::assertValueEncode(['a' => ['b' => 23]], null, 5);
	}
	
	
	public function test_FlagsPassed(): void
	{
		self::assertValueEncode('"&<>\'\'ה\'',
			JSON_HEX_QUOT | 
			JSON_HEX_TAG | 
			JSON_HEX_AMP | 
			JSON_HEX_APOS | 
			JSON_UNESCAPED_UNICODE);
	}
	
	
	public function test_InvalidUnicodeStringPassed(): void
	{
		self::assertSame(self::invalidStrEncoding('"{inv}"'), jsonencode(self::invalidStr()));
	}
	
	public function test_InvalidUnicodeStringAsKeyPassed(): void
	{
		self::assertSame(
			self::invalidStrEncoding('{"{inv}":"a"}'), 
			jsonencode([self::invalidStr() => 'a'])
		);
	}
	
	public function test_InvalidUnicodeStringInsideAnArrayPassed(): void
	{
		self::assertSame(
			self::invalidStrEncoding('{"a":"{inv}"}'), 
			jsonencode(['a' => self::invalidStr()])
		);
	}
	
	public function test_AssocArrayConvertedCorrectly(): void
	{
		self::assertValueEncode(['a' => 'a', 'b' => 'b', 'c' => 'c']);
	}
	
	public function test_InconsistentNumericArrayConvertedCorrectly(): void
	{
		self::assertValueEncode([0 => 'a', 2 => 'b', 1 => 'c']);
	}
	
	public function test_PartiallyNumericArrayConvertedCorrectly(): void
	{
		self::assertValueEncode([0 => 'a', 'a' => 'b', 2 => 'c']);
		self::assertValueEncode(['a' => 'a', 1 => 'b', 2 => 'c']);
	}
	
	public function test_NumericObjectNameWithInvalidValueParsedAsStringKey(): void
	{
		self::assertSame(
			self::invalidStrEncoding('{"1":"{inv}"}'), 
			jsonencode([1 => self::invalidStr()])
		);
	}
	
	
	public function test_Sanity(): void
	{
		$rand = random_bytes(10240);
		$res = jsonencode($rand);
		
		self::assertNotFalse($res);
		self::assertNotNull($res);
		self::assertTrue(strlen($res) >= 10240 / 2);
	}
}