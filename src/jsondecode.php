<?php
/**
 * @param mixed $value
 * @param bool|array $options
 * @param int $depth
 * @param int $flags
 * @return mixed|null
 */
function jsondecode($value, $options = false, ?int $depth = null, int $flags = 0)
{
	static $opt = [
		'flag' => null
	];
	
	// Setup options
	{
		if (is_array($options))
			$options = array_merge($opt, $options);
		else 
			$options = array_merge($opt, ['assoc' => $options]);
		
		$options['depth'] = $depth ?? 512;
		$options['flag']  = $flags;
	}
	
	$result = json_decode($value, $options['assoc'], $options['depth'], $options['flag']);
	
	if (!is_null($result) || json_last_error() != JSON_ERROR_UTF8)
		return $result;
	
	// Generate prefix
	{
		$range = 0;
		$rand = '-p-' . mt_rand(-$range, $range) . '-';
		
		while (strpos($value, $rand) !== false)
		{
			$range += 10;
			$rand = '-p-' . mt_rand(-$range, $range) . '-';
		}
	}
		
	$decoded = [];
	$success = [];
	
	$i = 1;
	$lastPos = 0;
	
	while ($lastPos < (strlen($value) - 1))
	{
		$firstPos = strpos($value, '"', $lastPos);
		
		if ($firstPos === false)
			break;
		
		$lastPos = strpos($value, '"', $firstPos + 1);
		
		if ($lastPos === false)
			break;
		
		$part = substr($value, $firstPos, $lastPos - $firstPos + 1);
		$result = json_decode($part, $options['assoc'], $options['depth'], $options['flag']);
		
		if (!is_null($result) || json_last_error() != JSON_ERROR_UTF8)
		{
			$decoded[] = $part;
		}
		else
		{
			$key =  $rand . "-{$i}-";
			$success[$key] = substr($part, 1, -1);
		}
		
		$i++;
	}
	
	foreach ($success as $key => $part)
	{
		$value = str_replace('"' . $part . '"', '"' . $key . '"', $value);
	}
	
	$result = json_decode($value, $options['assoc'], $options['depth'], $options['flag']);
		
	if ($success && !is_null($result) && json_last_error() != JSON_ERROR_UTF8)
	{
		$iterator = function ($data, array $success) use (&$iterator)
		{
			if (is_string($data))
			{
				return str_replace(array_keys($success), array_values($success), $data);
			}

			$keys = array_keys($success);
			
			foreach ($data as $key => &$value)
			{
				if (is_object($value) || is_array($value) ||
					(is_string($value) && in_array($value, $keys)))
				{
					$value = $iterator($value, $success);
				}
			}
			
			return $data;
		};
		
		$result = $iterator($result, $success);
	}
	
	return $result;
}