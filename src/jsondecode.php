<?php
/**
 * @param mixed $value
 * @param array|int|null $options
 * @param int $depth
 * @return string|bool
 */
function jsonencode($value, $options = null, ?int $depth = null)
{
	static $opt = [
		'flag' => null
	];
	
	// Setup options
	{
		if (is_null($options))
			$options = $opt;
		else if (is_array($options))
			$options = array_merge($opt, $options);
		else if (is_int($options))
			$options = array_merge($opt, ['flag' => $options]);
		else 
			$options = $opt;
		
		$options['depth'] = $depth ?? 512;
	}
	
	$result = json_encode($value, $options['flag'], $options['depth']);
	
	if ($result !== false || json_last_error() != JSON_ERROR_UTF8)
		return $result;
	
	if (is_string($value))
	{
		/**
		 * Following RFC standard.
 		 * @link https://tools.ietf.org/html/rfc7159
		 */
		$value = str_replace(
			[
				'\\',
				'"',
				"\0",
				'',
				
				// Escaped for easier debugging. Make sure new lines will not break logs output.
				"\n",
				"\r"
			],
			[
				'\\\\',
				'\"',
				'\u0000',
				'\u001f',
				
				'\n',
				'\r'
			],
			$value);
		
		return '"' . $value . '"';
	}
	else
	{
		$isNumeric = true;
		$corrected = [];
		$expectedNumericKey = 0;
		
		$options['depth']--;
		
		foreach ($value as $key => $res)
		{
			$isNumeric = $isNumeric && ($key === $expectedNumericKey++);
			
			$key = jsonencode((string)$key, $options);
			
			if ($key === false)
				return false;
			
			$res = jsonencode($res, $options);
			
			if ($res === false)
				return false;
			
			$corrected[$key] = $res;
		}
		
		if ($isNumeric && ($options['flag'] & JSON_FORCE_OBJECT) === 0)
		{
			return '[' . implode(',', $corrected) . ']'; 
		}
		else
		{
			$items = [];
			
			foreach ($corrected as $key => $res)
			{
				$items[] = "$key:$res";
			}
			
			return '{' . implode(',', $items) . '}'; 
		}
	}
}