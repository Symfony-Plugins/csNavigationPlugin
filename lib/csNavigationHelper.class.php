<?php

class csNavigationHelper
{
	protected static $_settings = array();
	protected static $_navigation = array();
	protected static $_route;
	private static $current_obj;
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
  		if(isset($settings['database_driven']) && $settings['database_driven'])
  		{	
  			if(!Doctrine::getTable('csNavigation')->isPopulated())
  			{
  				self::initDatabaseFromYaml($navigation);
  			}
  			$root = Doctrine::getTable('csNavigation')->getNavigationRoot();
  			$nav = self::nestedSetToArray($root);
  			$tree = self::getNavigationTreeFromArray($nav['children']);
  		}
  		else
  		{
        $tree = self::getNavigationTreeFromYaml($navigation);
  		}
  		$serialized = serialize($tree);
  		file_put_contents($cachePath, $serialized);
  	} else {
  	  $serialized = file_get_contents($cachePath);
  	  $tree = unserialize($serialized);
  	}
  	self::setTree($tree);
		self::$_settings = $settings;
  }
	static function initDatabaseFromYaml($navigation)
	{
		$root = self::createRoot();
		self::arrayToNestedSet($navigation, $root);
	}

	static function getBreadcrumb($level = 0)
  {
		if($tree = self::getTree())
		{
			return $tree->getBreadcrumb($level);
		}
		else
		{
			$breadcrumb = new csBreadcrumbNavigation();
			$breadcrumb->generateBreadcrumbFromNavigation();
			return $breadcrumb;
		}
	}
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
		if($tree = self::getTree())
		{
			if($level > 0)
			{
				return $tree->getSegment($level, $iterations);
			}
			else
			{
				return $tree;
			}
		}
	}
	static function getNavigationTreeFromYaml($arr, $level = 0, &$root = null)
	{
		if(!$root)
		{
			$root = new csTreeNavigation();
			$level++;
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
		}
		return $root;
	}
	static function getNavigationTreeFromArray($arr, $level = 0, &$root = null)
	{
		if(!$root)
		{
			$root = new csTreeNavigation();
		}
		foreach ($arr as $key => $value) {
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
	static function getTree()
	{
		return clone sfContext::getInstance()->getUser()->getAttribute('csNavigation_navigationTree');	
	}
	static function setTree($tree)
	{
		sfContext::getInstance()->getUser()->setAttribute('csNavigation_navigationTree', $tree);
	}
	static function arrayToNestedSet($arr, $root)
	{
			foreach ($arr as $key => $value) {
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
	
	static function getObjArray($obj)
	{
		return 	array(	'id' => $obj['id'],
										'name' => $obj['name'], 
										'route' => $obj['route'],
										'left' => $obj['lft'],
										'right' => $obj['rgt'],
										'level' => $obj['level'],
										'protected' => $obj['protected'],
										'locked' => $obj['locked'],
		);
	}
	static function hasRoute()
	{
		return self::$_route ? true : false;
	}
	static function getRoute()
	{
		return self::$_route;
	}
	static function getRouteUrl()
	{
		sfProjectConfiguration::getActive()->loadHelpers(array('Url'));
		try
		{
			return self::getRoute() ? url_for(self::getRoute(), 'absolute=true') : false; 
		}
		catch(Exception $e)
		{
			return false;
		}
	}
	static function setRoute($route)
	{
		self::$_route = $route;
	}
	static function createRoot()
	{
		$root = new csNavigation();
		$root->name = 'Root';
		$root->save();
		$treeObject = Doctrine::getTable('csNavigation')->getTree();
		$treeObject->createRoot($root);
		return $root;
	}
	static function explode_with_key($str, $groupglue = '&', $setglue = '=')
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
}