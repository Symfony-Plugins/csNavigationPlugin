<?php

class BasecsNavigationItem
{
  public $name;
  public $route;
  public $level;
  public $id;
  public $protected = false;
  public $items = array();
    
  /**
   * Constructor
   *
   */    
  public function __construct($name, $route = null, $level = null)
  {
    $this->route  = $route;
    $this->name   = $name;
    $this->level  = $level;
  }
  
  public function __toString()
  {
    return $this->getName();
  }
  
   /**
    * Retrieve the text of the item
    *
    */  
   public function getName()
   {
     return $this->name;
   }

  /**
    * Retrieve the text of the item
    *
    */  
   public function getLevel()
   {
     return $this->level;
   }
  
  /**
   * Retrieve the route of the item
   *
   */  
  public function getRoute()
  {
    return $this->route;
  }
  
  /**
  * Sets the route for the item
  *
  */
  public function setRoute($route)
  {
    $this->route = $route;
  }
  
  
  /**
  * true if item is not protected or user is authenticated
  *
  */
  public function isAuthenticated()
  {
    return !$this->protected or sfContext::getInstance()->getUser()->isAuthenticated();
  }
  
  /**
  * true if item or item's child is active
  *
  */
  public function isActiveBranch()
  {
    if($this->isActive())
    {
      return true;
    }
    foreach ($this->items as $item) {
      if ($item->isActiveBranch()) {
        return true;
      }
    }
    return false;
  }
  /**
   * Adds an item as a child of the item.  This is good for dynamically adding 
   * items to your navigation.
   *
   * @param mixed $text can also be an instance of a csNavigationItem
   * @param string $route 
   * @return the item added
   * @author Brent Shaffer
   */
  public function addItem($name, $route = null)
  {
    if($name instanceof csNavigationItem)
    {
      $new = $name;
    }
    else
    {
      $new = new csNavigationItem($name, $route);
    }
    array_push($this->items, $new);
    return $new;
  }
  public function getItems($offset = 0)
  {
    return array_slice($this->items, $offset);
  }
  
  public function hasItems()
  {
    return (sizeof($this->items) > 0);
  }
  
  public function isActive()
  {
    $route = new csNavigationRoute($this->route);
    return $route->isCurrentUrl();
  }
  
  /**
   * matches a nav item by its title
   * useful when adding items dynamically
   *
   * @param string $name 
   * @return void
   * @author Brent Shaffer
   */
  public function matchItemByName($name)
  {
    if($this->name == $name)
    {
      return $this;
    }
    else
    {
      foreach ($this->items as $item) 
      {
        if($item->matchItemByName($name))
        {
          return $item;
        }
      }
    }
    return false;
  }
  
  /**
   * returns true if this is active or one of its children is active
   *
   * @return void
   * @author Brent Shaffer
   */
  public function isExpanded()
  {
    if($this->isActive())
    {
      return true;
    }
    foreach($this->getItems() as $item)
    {
      if ($item->isExpanded())
      {
        return true;
      }
    }
    return false;
  }
  
  public function toArray()
  {
    $arr = array(
      'name' => $this->getName(), 
      'route' => $this->getRoute(), 
      'level' => $this->getLevel(), 
    );
    foreach ($this->getItems() as $item) {
      $arr['items'][] = $item->toArray();
    }
    return $arr;
  }
  
  /*
    TODO Deprecate
  */
  public function getSegment($iteration = null, $level = null)
  { 
    if($level === null)
    {
      $level = $this->level - 1;
    }
    if($this->level > $level && (!$iteration || ($this->level < $level + $iteration)))
    {
      if($this->level == $level)
      {
        return array();
      }
      return array($this);
    }
    $items = array();
    foreach ($this->items as $item) {
      $items = array_merge($items, $item->getSegment($iteration, $level));
    }
    return $items;
  }
  
  /*
    TODO Deprecate
  */
  public function findActiveItemForLevel($level)
  {
    foreach($this->items as $item)
    {
      if($item->level == $level)
      {
        if($item->isActiveBranch())
        {
          return $item;
        }
      }
      else
      {
        if($active = $item->findActiveItemForLevel($level))
        {
          return $active;
        }
      } 
    }

    return false;
  }
  
  /*
    TODO Deprecate
  */
  public function getBreadcrumb($level = 0)
  {
    $breadcrumb = array($this);
    if($this->isActive())
    {
      return $breadcrumb;
    }
    foreach ($this->items as $item) {
      if ($crumb = $item->getBreadcrumb()) {
        return array_merge(array($this), $crumb);
      }
    }
    return false;
  }
  
  public function getBreadcrumbArray()
  {
    if($this->isActive())
    {
      $crumb = $this;
      // $crumb->items = array();
      return array($crumb);
    }
    foreach ($this->items as $item) 
    {
      if($branch = $item->getBreadcrumbArray())
      {
        // $this->items = array();
        array_unshift($branch, $this);
        return $branch;
      }
    }
    return false;
  }
}