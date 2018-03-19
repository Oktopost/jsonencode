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
	
	// State machine 
	
	// OR Mb
	$firstPost = mb_strpos($value, '"', $pos);
	$lastPost = mb_strpos($value, '"', $firstPost + 1);
	
	$key = $rand . '-1-';
	$decoded = [$key => ''];
	$success = [];

	return false;
}