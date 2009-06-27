<?php


class csBaseTreeNavigation extends csBaseNavigation
{
  static      $instance = null;
  protected   $title    = null;
  
  public function __construct()
  {
    // $this->setRoot('Home', '@homepage');
  }
  public function setTitle($title)
  {
    $this->title = $title;
  }
  public function getTitle()
  {
    return $this->title;
  }
  public function setWithArray($array, $item = null)
  {
    foreach ($array as $key => $value) {
      if(is_array($value))
      {
        $url = isset($value['url']) ? $value['url'] : null;
        $this->addItem($key, $url);
        unset($value['url']);
        $this->setWithArray($value, $this->root);
      }
      else
      { 
        $item->addItem($key, $value);
      }
    }
  }

  // public function setRoot($name, $route = null)
  // {
  //   $this->root = ($name instanceof csNavigationItem) ?
  //         $name : new csNavigationItem($name, $route);
  // }

  public function getBreadcrumb($level = 0)
  {
    $breadcrumb = array($this);
    foreach ($this->items as $item) {
      if ($crumb = $item->getBreadcrumbArray($level)) {
        return new csBreadcrumbNavigation($crumb);
      }
    }
    return false;
  }

  /**
   * 
   *
   * @param string $root_level - The level of the root node for this segment
   *                            (lowest possible is zero)
   * @param string $iteration - number of levels displayed (0 displays all levels)
   * @return void
   * @author Brent Shaffer
   */
  public function getSegment($root_level, $iterations = null, $root = null)
  {
    $items = $root_level ? $this->getItemsForLevel($root_level) : $this->getItems();
    $segment = $this->getSegmentItems($items, $root_level, $iterations);
    $root = clone $this;  //Currently only supports one root
    $root->items = array_filter($segment);
    return $root;
  }
  
  public function getSegmentItems($items, $root_level = 0, $iterations = null)
  {
    $segment = array();
    
    foreach ($items as $item) 
    {
      $children = $this->getSegmentItems($item->getItems(), $root_level, $iterations);
      
      // If the level is beneath this one, return the children of this object
      if ($item->level <= $root_level) 
      {
        $segment = array_merge($segment, $children);
      }
      // if iterations are not set, or the item is within the iteration
      elseif(!$iterations || $item->getLevel() <= ($root_level + $iterations))
      {
        $new_segment = clone $item;
        $new_segment->setItems($children);
        $segment[] = $new_segment;
      }
    }
    
    return array_filter($segment);
  }
  
  /**
   * determines what branch of the segment root 
   * to display
   *
   * @param string $level 
   * @return void
   * @author Brent Shaffer
   */
  public function getItemsForLevel($level)
  {
    foreach($this->items as $item)
    {
      if($active = $item->findActiveItemForLevel($level))
      {
        break;
      }
    }
    if ($active) 
    {
      return $active->getItems();
    }
    return array();
  }
  public function findActiveItem()
  {
    foreach ($this->items as $item) 
    {
      if ($ret = $item->findActiveItem()) 
      {
        return $ret;
      }
    }
    return null;
  }

  /**
   * For adding dynamic elements to a branch of a tree.
   * Use in the setup() method.
   *
   * @param string $parentName - Name of the navigation item to add to
   * @param string $item - mixed, either the string name of the new item, or the route object
   * @param string $route (optional) - the route if $item is the string name
   * @return void
   * @author Brent Shaffer
   */
  public function appendItem($parentName, $item, $route = null)
  {
    $parent = $this->matchItemByName($parentName);
    if(!$parent)
    {
      return false;
    }
    if(!$item instanceof csNavigationItem)
    {
      $item = new csNavigationItem($item, $route);
    }
    if(!$item->getLevel())
    {
      $item->level = ($parent->getLevel() + 1);
    }
    $parent->items[] = $item;
    return $parent;
  }
  
  public function matchItemByName($name)
  {
    foreach ($this->items as $item) 
    {
      if ($ret = $item->matchItemByName($name)) 
      {
        return $ret;
      }
    }
    return null;
  }
 
  // =====================
  // = Singleton Functions =
  // =====================
   
  public static function getInstance()
  {
    if (is_null(self::$instance))
    {
      self::createInstance();
    }
    return self::$instance;
  }
 
  public static function createInstance()
  {
    self::$instance = new csTreeNavigation();
  }
}