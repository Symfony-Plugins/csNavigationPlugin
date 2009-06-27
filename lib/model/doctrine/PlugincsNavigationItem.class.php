<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
abstract class PlugincsNavigationItem extends BasecsNavigationItem
{
  public $_children = array();
  
  /**
  * true if item is not protected or user is authenticated
  *
  */
  public function isAuthenticated()
  {
    return !$this->protected or sfContext::getInstance()->getUser()->isAuthenticated();
  }
  
  /**
   * Methods to access item children while bypassing the doctrine
   * Nested Set implementation
   *
   * @return void
   * @author Brent Shaffer
   */
  public function getChildren()
  {
    return $this->_children;
  }
  
  public function setChildren($children, $commit = false)
  {
    $this->_children = $children;
    
    if ($commit) 
    {
      foreach ($children as $child) 
      {
        $this->getNode()->addChild($child);
        $this->save();
      }
    }
  }
  
  public function addChild($child)
  {
    if (!$this->_children) 
    {
      $this->_children = array();
    }
    $this->_children[] = $child;
  }
  
  /** 
  * true if size of children is greater than zero
  *
  */
  public function hasChildren()
  {
    return (sizeof($this->_children) > 0);
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
    foreach ($this->_children as $child) {
      if ($child->isActiveBranch()) {
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
  public function findActiveChild()
  {
    if($this->isActive())
    {
      return $this;
    }
    foreach ($this->_children as $child) {
      if ($child->isActiveBranch()) {
        return $child;
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
    foreach($this->_children as $child)
    {
      if($active = $child->findActiveItemForLevel($level))
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
  public function addItem($name, $route = null, $level = null)
  {
    if($name instanceof csNavigationItem)
    {
      $new = $name;
    }
    else
    {
      $new = new csNavigationItem();
      $new->name = $name;
      $new->route = $route;
      $new->level = $level;
    }
    array_push($this->_children, $new);
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
      foreach ($this->_children as $child) 
      {
        if($child->matchItemByName($name))
        {
          return $child;
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
    foreach($this->getChildren() as $child)
    {
      if ($child->isExpanded())
      {
        return true;
      }
    }
    return false;
  }
  
  public function toArray($deep = true, $prefixKey = false)
  {
    $arr = parent::toArray($deep, $prefixKey);
    foreach ($this->_children as $child) 
    {
      $arr['children'][] = $child->toArray($deep, $prefixKey);
    }
    return $arr;
  }

  public function fromArray(array $array, $deep = true)
  {
    if (isset($array['children'])) 
    {
      $children = $array['children'];
      foreach ($children as $child) 
      {
        $new = new csNavigationItem();
        $new->fromArray($child);
        $this->addChild($new);
      }
      unset($array['children']);
    }
    parent::fromArray($array, $deep);

  }
  
  // For Database-driven navigations to remove cached navigation when
  // an item is updated
  public function postSave($event)
  {
    $cachePath = sfConfig::get('sf_cache_dir').'/navigation_tree.cache';
    if (file_exists($cachePath)) 
    {
      unlink($cachePath);
    }
  }
}