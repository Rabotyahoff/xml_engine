<?php

  /**
   * Функции для работы с массивом
   */

  function make_array($dta){
    if (is_array($dta)) return $dta;
    if (empty($dta)) return array();
    return array($dta);
  }

  /**
   * создаёт массив, где ключами становятся поля id
   * @param array $arr
   * @param string $id
   */
  function make_array_by_id($arr,$id){
    $new_arr=array();
    foreach ($arr as $itm){
      $new_arr[$itm[$id]]=$itm;
    }
    return $new_arr;
  }

	/**
	 * Function array_insert().
	 *
	 * Returns the new number of the elements in the array.
	 *
	 * @param array $array Array (by reference)
	 * @param mixed $value New element
	 * @param int $offset Position
	 * @return int
	 */
	function array_insert(&$array, $value, $offset)
	{
	    if (is_array($array)) {
	        $array  = array_values($array);
	        $offset = intval($offset);
	        if ($offset < 0 || $offset >= count($array)) {
	            array_push($array, $value);
	        } elseif ($offset == 0) {
	            array_unshift($array, $value);
	        } else {
	            $temp  = array_slice($array, 0, $offset);
	            array_push($temp, $value);
	            $array = array_slice($array, $offset);
	            $array = array_merge($temp, $array);
	        }
	    } else {
	        $array = array($value);
	    }
	    return count($array);
	}

