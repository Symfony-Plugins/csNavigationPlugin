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
  * gets the child items
  *
  */
  public function getItems($offset = 0)
  {
    return array_slice($this->items, $offset);
  }
  
  
  /**
  * Sets the child items for the navigation item
  *
  */
  public function setItems($items)
  {
    $this->items = $items;
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
  * true if size of children is greater than zero
  *
  */
  public function hasItems()
  {
    return (sizeof($this->items) > 0);
  }
  
  /** 
  * true if this route matches the current one
  *
  */
  public function isActive()
  {
    $route = new csNavigationRoute($this->route);
    return $route->isCurrentUrl();
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
  * returns the active item if it is this item, 
  * or a child of this item
  *
  */
  public function findActiveItem()
  {
    if($this->isActive())
    {
      return $this;
    }
    foreach ($this->items as $item) {
      if ($item->isActiveBranch()) {
        return $item;
      }
    }
    return null;
  }
  
  /*
    TODO Refactor or Deprecate (this method seems silly)
  */
  public function findActiveItemForLevel($level)
  {
    if ($this->level == $level) 
    {
      return $this->isActiveBranch() ? $this : null;
    }
    foreach($this->items as $item)
    {
      if($active = $item->findActiveItemForLevel($level))
      {
        return $active;
      }
    }

    return null;
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
  
  /*
    TODO Deprecate
  */
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