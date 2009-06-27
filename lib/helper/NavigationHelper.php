<?php 

function pcase($string)
{
  $string = substr_replace($string, strtoupper(substr($string, 0, 1)), 0, 1);
  return $string;
}
function getLevel($level, $arr, $current = 0)
{
  $ret = array();
  foreach ($arr as $key => $value) {
    if($current >= $level)
    {
      $ret[$key] = is_array($value) ? getLevel($level, $value, $current + 1) : $value;
    }
    else
    {
      if(is_array($value))
      {
        $ret = array_merge($ret, getLevel($level, $value, $current + 1));
      }
    }
  }
  return $ret;
}

// explodes an array into a querystring
function explode_with_key($str, $groupglue = '&', $setglue = '=')
{
 $arr1=explode($groupglue, $str);
 $arr2 = array();
 foreach (array_filter($arr1) as $clip) 
 {
    $assoc = explode($setglue, $clip);
    $arr2[$assoc[0]] = $assoc[1];
 }
 return $arr2;
}