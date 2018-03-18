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
		$len = mb_strlen($value);
		$chars = [];
		
		for ($i = 0; $i < $len; $i++)
		{
			$char = mb_substr($value, $i, 1);
			
			if (json_decode($char, $options['flag']) === $char)
			{
				$char[] = $char;
				continue;
			}
			
			$encodedChar = json_encode($char, $options['flag']);
			
			if ($encodedChar === false)
			{
				$chars[] = $char;
			}
			else
			{
				// Remove the generated " character by json_encode.
				$chars[] = substr($encodedChar, 1, strlen($char) - 2);
			}
		}
		
		return '"' . implode('', $chars) . '"';
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
			
			$key = jsonencode($key, $options);
			
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
	
	
	return false;
}