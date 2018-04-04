<?php
class StandardJsondecodeTest extends JsonencodeTestCase
{
	public function test_SimpleValues()
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
	
	
	public function test_ComplexArray()
	{
		self::assertValueDecode((object)[1, 'a', '2' => 3, '4' => (object)['a' => 123], 'asd' => [1, 'a', true, null]]);
	}
	
	public function test_InvalidString()
	{
		self::assertValueDecode(self::INVALID_STRINGS);
	}
	
	
	public function test_InvalidJson_ReturnFalse()
	{
		$res = jsondecode('"{"a":}"');
		self::assertNull($res);
	}
	
	public function test_InvalidUTFCharecterInInvalidJson_ReturnFalse()
	{
		$str = '{"a":"' . self::invalidStr() . '}';
		self::assertNull(jsondecode($str));
	}
	
	public function test_InvalidJson_RetainError()
	{
		jsondecode('{"a":}');
		self::assertEquals(json_last_error(), JSON_ERROR_SYNTAX);
	}
	
	public function test_InvalidUTFChar_LastErrorIsZero()
	{
		jsondecode('{"a":"' . self::INVALID_CHAR . '"}');
		self::assertEquals(0, json_last_error());
	}
	
	public function test_ComplexObjectWithInvalidString()
	{
		$obj = new \stdClass();
		$obj->a = 'b';
		$obj->b = [0 => 'b'];
		$obj->c = self::INVALID_STRINGS;
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
}


