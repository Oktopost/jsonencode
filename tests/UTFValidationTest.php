<?php
use function JsonEncode\getValidString;


require_once __DIR__ . '/../src/UTFValidation.php';


class UTFValidationTest extends JsonencodeTestCase
{
	public function test_ValidCharacters(): void
	{
		$chars = ['😠', hex2bin('00')];
		
		foreach ($chars as $char)
		{
			self::assertEquals($char, getValidString($char));
		}
	}
	
	
	public function test_InvalidCharacters(): void
	{
		$chars = [
			hex2bin('80'),
			hex2bin('c0'),
			hex2bin('cf'),
			hex2bin('e0'),
			hex2bin('ef'),
			hex2bin('e48f'),
			hex2bin('f184'),
			hex2bin('f18484'),
			"\u{c000}",
			"\u{c0ff}",
			"\u{c100}",
			"\u{c1ff}",
			"\u{d800}",
			"\u{dfff}",
			"\u{10ffff}"
		];
		
		foreach ($chars as $char)
		{
			$invalid = str_repeat('a', strlen($char));
			self::assertEquals($invalid, getValidString($char, 'a'));
		}
	}
}