<?php
namespace JsonEncode;


// 1. VALID			00 to 7f					| 0000 0000 										to 0111 1111										| ASCII
// 2. INVALID		80 to bf					| 1000 0000 										to 1011 1111										| Control bytes
// 3. INVALID		c0 to c1					| 1100 0000 										to 1100 0001										| Overloading of 0xxx xxxx
// 4. VALID			c2 80 to df bf				| 1100 0010 . 1000 0000 							to 1101 1111 . 1011 1111							| 
// 5. INVALID		e0 80 80 to e0 9f bf		| 1110 0000 . 1000 0000 . 1000 0000					to 1110 0000 . 1001 1111 . 1011 1111				| Overloading of 110x xxxx
// 6. VALID			e0 a0 80 to ed 9f bf		| 1110 0000 . 1010 0000 . 1000 0000					to 1110 1101 . 1001 1111 . 1011 1111				| 
// 7. INVALID		ed a0 80 to ed bf bf		| 1110 1101 . 1010 0000 . 1000 0000					to 1110 1101 . 1011 1111 . 1011 1111				| Invalid sequence u+d800 u+dfff
// 8. VALID			ee 80 80 to ef bf bf		| 1110 1110 . 1000 0000 . 1000 0000					to 1110 1111 . 1011 1111 . 1011 1111				| 
// 9. INVALID 		f0 80 80 80 to f0 8f bf bf	| 1111 0000 . 1000 0000 . 1000 0000 . 1000 0000		to 1111 0000 . 1000 1111 . 1011 1111 . 1011 1111	| Overloading of 110x xxxx and 111x xxxx
// 10. VALID		f0 90 80 80 to f4 8f bf be	| 1111 0000 . 1001 0000 . 1000 0000 . 1000 0000		to 1111 0100 . 1000 1111 . 1011 1111 . 1011 1110	|
// 11. INVALID		f4 8f bf bf to f7 bf bf bf	| 1111 0100 . 1000 1111 . 1011 1111 . 1011 1111		to 1111 0111 . 1011 1111 . 1011 1111 . 1011 1111	| Out of allowed range u+10ffff and higher


const UTF_SPACE = [
	
	// 1. Valid: ASCII
	[[0x00], [0x7f]],
	
	// 2. Invalid: Leading Bytes, 3. Overloading of 0xxx/xxxx
	// Combined
	// [[0x80], [0xbf]],
	// [[0xc0], [0xc1]],
	// [[0x80], [0xc1]],
	
	// 4. Valid: 
	[[0xc2, 0x80], [0xdf, 0xbf]],
	
	// 5. Invalid: Overloading of 110x/xxxx and lower
	// [[0xe0, 0x80, 0x80], [0xe0, 0x9f, 0xbf]],
	
	// 6. Valid: 
	[[0xe0, 0xa0, 0x80], [0xec, 0xbf, 0xbf]],
	[[0xed, 0x80, 0x80], [0xed, 0x9f, 0xbf]],
	
	// 7. Invalid: sequence u+d800 u+dfff
	// [[0xed, 0xa0, 0x80], [0xef, 0xbf, 0xbf]],
	
	// 8. Valid: 
	[[0xee, 0x80, 0x80], [0xef, 0xbf, 0xbf]],
	
	// 9. Invalid: Overloading of 1110/xxxx and lower
	// [[0xf0, 0x80, 0x80, 0x80], [0xf0, 0x8f, 0xbf, 0xbf]],
	
	// 10. Valid: until U+10ffff (excluding)
	[[0xf0, 0x90, 0x80, 0x80], [0xf3, 0xbf, 0xbf, 0xbf]],
	[[0xf4, 0x80, 0x80, 0x80], [0xf4, 0x8f, 0xbf, 0xbe]],
	
	// 11. Invalid
	// [[0xf4, 0x8f, 0xbf, 0xbf], [0xf7, 0bf, 0xbf, 0xbf]],
];


function getValidString(string $source, string $replace = ''): string
{
	$index		= 0;
	$last		= strlen($source) - 1;
	$stream		= '';
	$isFound	= false;
	
	while ($index <= $last)
	{
		$exp = null;
		
		foreach (UTF_SPACE as $range)
		{
			$lower		= $range[0];
			$upper		= $range[1];
			
			$exp		= '';
			$stop 		= false;
			$isFound	= false;
			$count		= count($lower);
			
			for ($bitIndex = 0; $bitIndex < $count; $bitIndex++)
			{
				$chrIndex = $index + $bitIndex;
				
				if ($chrIndex > $last)
				{
					$isFound = false;
					$stop = true;
					break;
				}
				
				$chr = $source[$index + $bitIndex];
				$val = ord($chr);
				
				if ($val < $lower[$bitIndex])
				{
					$isFound = false;
					$stop = true;
					break;
				}
				else if ($val > $upper[$bitIndex])
				{
					$isFound = false;
					break;
				}
				else
				{
					$isFound = true;
					$exp .= $chr;
				}
			}
			
			if ($stop)
			{
				break;
			}
			else if ($isFound)
			{
				$stream .= $exp;
				$index += $count;
				break;
			}
		}
		
		if (!$isFound)
		{
			$stream .= $replace;
			$index++;
		}
	}
	
	return $stream;
}