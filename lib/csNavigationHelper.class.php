<?php

class csNavigationHelper
{
  protected static $_settings = array();
  protected static $_navigation = array();
  
  /**
   * load
  * static method to pre-load the specified settings into memory. Use this
  * early in execution to avoid multiple SQL calls for individual settings.
  * Takes either a string or an array of strings as an argument.
   * 
   * @param mixed $settings 
   * @static
   * @access public
   * @return void
   */
  static function init($settings, $navigation)
  {
    // Implements Caching for navigation tree
    $cachePath = sfConfig::get('sf_cache_dir').'/navigation_tree.cache';
    if (!file_exists($cachePath))
    {
      // Populate the Navigation Tree
      if(isset($settings['database']['driven']) && $settings['database']['driven'])
      { 
        //Populate Tree from Database
        if(!Doctrine::getTable('csNavigation')->isPopulated())
        {
          //Build Database from Existing navigation.yml file
          self::initDatabaseFromYaml($navigation);
        }
        //Build from nested set
        $root = Doctrine::getTable('csNavigation')->getNavigationRoot();
        $nav = self::nestedSetToArray($root);
        $tree = self::getNavigationTreeFromArray($nav['children']);
      }
      else
      {
        //Populate tree from navigation.yml
        $tree = self::getNavigationTreeFromYaml($navigation);
      }
      
      // Run tree's setup() method for dynamically adding branches
      $tree->setup();
      
      // Cache Tree
      $serialized = serialize($tree);
      file_put_contents($cachePath, $serialized);
    } 
    self::$_settings = $settings;
  }
  
  
  static function initDatabaseFromYaml($navigation)
  {
    self::arrayToNestedSet($navigation);
  }
  
  // Generic Function that converts the YAML array to a doctrine nested set
  static function arrayToNestedSet($arr, $root = null)
  {
    foreach ($arr as $key => $value) 
    {
      if (!$root) 
      {
        $record = self::createRoot($key, $value);
        self::setItemAttributes($record, self::parseItemAttributes($value));        
        self::arrayToNestedSet($value, $record);
        continue;
      }
      $root->refresh();
      $record = new csNavigation();
      $record->name = $key;
      if(is_array($value))
      {
        self::setItemAttributes($record, self::parseItemAttributes($value));
        $record->getNode()->insertAsLastChildOf($root);
        self::arrayToNestedSet($value, $record);
      }
      else
      {
        $record->route = $value;
        $record->getNode()->insertAsLastChildOf($root);
      }
    }
  }
  
  static function createRoot($key, $value)
  {
    $root = new csNavigation();
    $root->name = $key;
    self::setItemAttributes($root, self::parseItemAttributes($value));
    $root->save();
    $treeObject = Doctrine::getTable('csNavigation')->getTree();
    $treeObject->createRoot($root);
    return $root;
  }
  
  /*
    TODO Refactor breadcrumb methods
  */
  static function getBreadcrumb($level = 0)
  {
    $tree = self::getTree();
    return $tree->getBreadcrumb($level);
    
    // $breadcrumb = new csBreadcrumbNavigation();
    // $breadcrumb->generateBreadcrumbFromNavigation();
    // return $breadcrumb;
  }
  
  /*
    TODO Deprecate this method
  */
  static function getNavigationItems($level = 0, $iterations = null)
  {
    $nav = self::getNavigationTree($level, $iterations);
    return $nav->getItems();
  }
  
  /**
   * getNavigationTree 
   * Returns array of the current navigation
   * @static
   * @access public
   * @return array
   */
  static function getNavigationTree($level = 0, $iterations = null)
  {
    $tree = self::getTree();
    return $tree->getSegment($level, $iterations);
  }
  
  /**
   * Create a navigation tree from the navigation.yml file
   *
   * @param string $arr 
   * @param string $level 
   * @param string $root 
   * @return void
   * @author Brent Shaffer
   */
  static function getNavigationTreeFromYaml($arr, $level = 0, &$root = null)
  {
    if(!$root)
    {
      $root = new csTreeNavigation();
    }
    foreach ($arr as $key => $value) {
      if(is_array($value))
      {
        $item = new csNavigationItem($key, null, $level);
        self::setItemAttributes($item, self::parseItemAttributes($value));
        if($value)
        {
          self::getNavigationTreeFromYaml($value, $level + 1, $item);         
        }
      }
      else
      {
        $item = new csNavigationItem($key, $value, $level);
      }
      $root->addItem($item);
      
      if ($level == 0) 
      {
        break; // Currently no multiple-root support
      }
    }
    return $root;
  }

  /**
   * Receives a nested set in array form, converts to generic NavigationTree
   *
   * @param string $arr 
   * @param string $level 
   * @param string $root 
   * @return void
   * @author Brent Shaffer
   */
  static function getNavigationTreeFromArray($arr, $level = 0, &$root = null)
  {
    $root = $root ? $root : new csTreeNavigation();
    foreach ($arr as $key => $value) 
    {
      $item = new csNavigationItem($value['name'], $value['route'], $level + 1);
      self::setItemAttributes($item, $value);
      if(isset($value['children']))
      {
        self::getNavigationTreeFromArray($value['children'], $level + 1, $item);
      }
      $root->addItem($item);
    }
    return $root;
  }
  
  /**
   * Converts nested set from Database into array form
   *
   * @param string $obj 
   * @return void
   * @author Brent Shaffer
   */
  static function nestedSetToArray($obj = null)
  {
    $arr = self::getObjArray($obj);
    if($children = $obj->getNode()->getChildren())
    {
      foreach ($children as $child_obj) {
        $arr['children'][$child_obj['id']] = self::nestedSetToArray($child_obj);
      }
    }
    return $arr;
  }
  
  /**
   * Pulls tree from cache directory (set by self::init())
   *
   * @return void
   * @author Brent Shaffer
   */
  static function getTree()
  {
    $cachePath = sfConfig::get('sf_cache_dir').'/navigation_tree.cache';
    if (!file_exists($cachePath))
    {
      throw new sfException('You must add the csNavigationFilter class to your routing.yml 
                              (see csNavigationPlugin\s README for more information)');
    }
    
    // Pull navigation tree from cache
    $serialized = file_get_contents($cachePath);

    return unserialize($serialized);
  }
  
  /**
   * Parses Navigation Item attributes in navigation.yml
   *
   * @param string $arr 
   * @return void
   * @author Brent Shaffer
   */
  static function parseItemAttributes(&$arr)
  {
    $attr = array();
    foreach ($arr as $key => $value) {
      if (strpos($key, '~') === 0) {
        $attr[substr($key, 1)] = $value;
        unset($arr[$key]);
      }
    }
    return $attr;
  }
  
  static function setItemAttributes(&$item, $arr)
  {
    foreach ($arr as $key => $value) {
      $item->$key = $value;
    }
  }
  
  /**
   * Available object attributes in database / navigation.yml
   *
   *  TODO Make Extensible - pull from csNavigation model
   *
   * @param string $obj 
   * @return void
   * @author Brent Shaffer
   */
  static function getObjArray($obj)
  {
    return  array(  'id' => $obj['id'],
                    'name' => $obj['name'], 
                    'route' => $obj['route'],
                    'left' => $obj['lft'],
                    'right' => $obj['rgt'],
                    'level' => $obj['level'],
                    'protected' => $obj['protected'],
                    'locked' => $obj['locked'],
    );
  }

}