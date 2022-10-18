<?php
class StandardJsondecodeTest extends JsonencodeTestCase
{
	public function test_SimpleValues(): void
	{
		self::assertValueDecode([]);
		self::assertValueDecode('');
		self::assertValueDecode(null);
		self::assertValueDecode(0);
		self::assertValueDecode(false);
		
		
		self::assertValueDecode('a');
		self::assertValueDecode(true);
		self::assertValueDecode(123);
		self::assertValueDecode(0.1);
		self::assertValueDecode([1, 'a']);
		self::assertValueDecode((object)['a' => 'b']);
	}
	
	
	public function test_ComplexArray(): void
	{
		self::assertValueDecode((object)[1, 'a', '2' => 3, '4' => (object)['a' => 123], 'asd' => [1, 'a', true, null]]);
	}
	
	
	public function test_InvalidJson_ReturnFalse(): void
	{
		$res = jsondecode('"{"a":}"');
		self::assertNull($res);
	}
	
	public function test_InvalidUTFCharecterInInvalidJson_ReturnFalse(): void
	{
		$str = '{"a":"' . self::invalidStr() . '}';
		self::assertNull(jsondecode($str));
	}
	
	public function test_InvalidJson_RetainError(): void
	{
		jsondecode('{"a":}');
		self::assertEquals(json_last_error(), JSON_ERROR_SYNTAX);
	}
	
	public function test_InvalidUTFChar_LastErrorIsZero(): void
	{
		jsondecode('{"a":"' . self::INVALID_CHAR . '"}');
		self::assertEquals(0, json_last_error());
	}
	
	public function test_ComplexObjectWithInvalidString(): void
	{
		$obj = new \stdClass();
		$obj->a = 'b';
		$obj->b = [0 => 'b'];
		$obj->d = 1;
		$obj->e = null;
		$obj->f = '';
		$obj->h = 1.1;
		
		$dd = new \stdClass();
		$dd->aa = 'a';
		$dd->bb = [0 => [0 => 'a']];
		
		$obj->j = $dd;
		
		self::assertValueDecode($obj);
	}
	
	
	public function test_jsondecode_a(): void
	{
		self::assertNull(jsondecode_a("null"));
		
		$res = jsondecode_a("{\"a\":{\"b\":2}}");
		
		self::assertTrue(is_array($res));
		self::assertTrue(is_array($res['a'] ?? null));
	}
	
	public function test_jsondecode_std(): void
	{
		self::assertNull(jsondecode_std("null"));
		self::assertInstanceOf(stdClass::class, jsondecode_std("{\"a\":1}"));
		self::assertTrue(is_array(jsondecode_std("[1, 2]")));
	}
}


