<?php
function utf8_strcut($str, $start, $length=null) {
	$str = strip_tags($str);
	preg_match_all('/./us', $str, $match);
	$chars = is_null($length)? array_slice($match[0], $start ) : array_slice($match[0], $start, $length);

	unset($str);

	return implode('', $chars);
}