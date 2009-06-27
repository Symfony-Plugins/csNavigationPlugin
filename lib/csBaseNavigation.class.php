<?php

class csBaseNavigation
{
  protected $items    = array();
  static    $instance = null;
  
  /**
   * Add an item
   *
   * @param mixed $name
   * @param string $route
   */
  public function addItem($name, $route = null)
  {
    if($name instanceof csNavigationItem)
    {
      $item = $name;
    }
    else
    {
      $item = new csNavigationItem($name, $route);
    }
    array_push($this->items, $item);
  }

  /**
   * Delete all existings items
   *
   */
  public function clearItems()
  {
    $this->items = array();
  }  
  
  public function toArray()
  {
    $arr = array();
    foreach ($this->getItems() as $item) {
      $arr[] = $item->toArray();
    }
    return $arr;
  }
  
  /**
   * Retrieve an array of csNavigationItems
   *
   * @param int $offset
   */
  public function getItems($offset = 0)
  {
    return array_slice($this->items, $offset);
  }
  public function hasItems()
  {
    return (sizeof($this->getItems()) > 0) ;
  }
}