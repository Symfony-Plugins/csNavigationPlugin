<?php


class csBaseTreeNavigation extends csBaseNavigation
{
  static      $instance = null;
  static protected  $session_name = 'csTreeNavigation';
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
        $new = $this->addItem($key, $url);
        unset($value['url']);
        $this->setWithArray($value, $new);
      }
      else
      { 
        $item->addItem($key, $value);
      }
    }
  }
  
  public function addItem($name, $route = null)
  {
    if($name instanceof csNavigationItem)
    {
      array_push($this->items, $name);
    }
    else
    {
      array_push($this->items, new csNavigationItem($name, $route));
    }
    $this->save();
  }

  public function getSegment($level, $iteration = null)
  {
    $root = $this->findActiveItemForLevel($level);
    if($root)
    {
      $this->items = $root->getSegment($iteration, $level);
      return $this;
    }
    $items = array();
    foreach ($this->items as $item) {
      $items = array_merge($items, $item->getSegment($level, $iteration));
    }
    $this->items = $items;
    return $this;
  }
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
  public function newInstance()
  {
    $tree = new csNavigationTree();
    $tree->items = $this->items;
    $tree->title = $this->title;
    return $tree;
  }
  public function toArray()
  {
    $arr = array();
    foreach ($this->getItems() as $item) {
      $arr[] = $item->toArray();
    }
    return $arr;
  }
  public static function getInstance()
  {
    if (is_null(self::$instance))
    {
      if (sfContext::getInstance()->getRequest()->getParameter(self::$session_name))
      {
        self::$instance = sfContext::getInstance()->getRequest()->getParameter(self::$session_name);
      }
      else
      {
        self::createInstance();
        sfContext::getInstance()->getRequest()->setParameter(self::$session_name, self::$instance);
      }
    }
    return self::$instance;
  }

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
      elseif($item->isActive())
      {
        return $item;
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

  public static function createInstance()
  {
    self::$instance = new csTreeNavigation();
    self::$instance->save();
  }
  
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
    return true;
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
    return false;
  }
}