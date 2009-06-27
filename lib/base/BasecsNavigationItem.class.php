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
    $this->route 	= $route;
    $this->name  	= $name;
    $this->level  = $level;
  }
  
	// public function __toString()
	// {
	// 	return $this->getName();
	// }
  /**
   * Retrieve the route of the item
   *
   */  
  public function getRoute()
  {
    return $this->route;
  }

	public function getBaseRoute()
	{
		if($this->isTokenRoute())
		{
			if(stripos($this->getRoute(), '?') !== false)
			{
				return substr($this->getRoute(), 0, stripos($this->getRoute(), '?'));
			}
		}
		return $this->getRoute();
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

	public function isCurrentUrl() 
	{ 
			return $this->matchUrl($this->getCurrentUrl(true), $this->getRouteUrl(true)); 
	}
	public function getRootSiteUrl()
	{
		$request = sfContext::getInstance()->getRequest();
		return $request->getUriPrefix().$request->getScriptName();
	}
	public function matchUrl($url1, $url2)
	{
		// echo "URL1: $url1 & URL2: $url2";
		if($url1 == $url2)
		{
			return true;
		}
		elseif(stripos($url1, '*') !== false || stripos($url2, '*') !== false)
		{
			return $this->matchWildcardRoutes($url1, $url2);
		}
		return false;
	}
	public function matchWildcardRoutes($url1, $url2)
	{
		$url1_arr = explode('/', $url1);
		$url2_arr = explode('/', $url2);
		if(sizeof($url1_arr) == sizeof($url2_arr))
		{
			for ($i = 0; $i < sizeof($url1_arr); $i++) 
			{
				if($url1_arr[$i] != $url2_arr[$i] && $url1_arr[$i] != '*' &&  $url2_arr[$i] != '*')
				{
					return false;
				}
			}
			return true;
		}
		return false;
	}
	public function routeHasParameter($key)
	{
		$params = csNavigationHelper::explode_with_key(substr(strstr($this->route, '?'), 1));
		if(isset($params[$key]))
		{
			return $params[$key];
		}
		return false;
	}
	public function fillParams()
	{
		$routing = sfContext::getInstance()->getRouting()->getRoutes();
		if(!isset($routing[substr($this->getBaseRoute(), 1)]))
		{
			throw new sfException('Undefined Route: ' . $this->getBaseRoute());
		}
		$route = $routing[substr($this->getBaseRoute(), 1)];
		foreach ($route->getVariables() as $key => $value) 
		{
			if($param = $this->routeHasParameter($key))
			{
				$this->addRouteParam($key, $param);
			}
			elseif(method_exists(new csNavigation(), sfInflector::camelize('get_default_'.$key)))
			{
				$method = sfInflector::camelize('get_default_'.$key);
				$this->addRouteParam($key, csNavigation::$method($this));
			}
		}
	}
	public function addRouteParam($key, $value)
	{
		$route = $this->getRoute();
		$route .= stripos($route, '?') === false ? '?' : '&';
		$this->setRoute($route .= "$key=$value");
	}
	public function isTokenRoute()
	{
		$route = $this->getRoute();
		return ($route && $route[0] == '@');
	}
	
	public function getRouteUrl($relative = false) 
	{ 
		if($this->isTokenRoute())
		{
			$this->fillParams();
		}
		return $relative ? $this->makeUrlRelative($this->getRenderedRoute()) : $this->getRenderedRoute();
	}
	public function getRenderedRoute()
	{
		if($this->isTokenRoute())
		{
			sfProjectConfiguration::getActive()->loadHelpers(array('Url'));
			return url_for($this->getRoute(), 'absolute=true'); 
		}
    return stripos($this->getRoute(), '://') === false ? $this->getRootSiteUrl() .$this->getRoute() : $this->getRoute();		
	}
	public function getCurrentUrl($relative = false)
	{	
		return $relative ? $this->makeUrlRelative($this->getCurrentUri()) : $this->getCurrentUri();
	}
	public function getCurrentUri()
	{
		return sfContext::getInstance()->getRequest()->getUri();
	}
	public function makeUrlRelative($url)
	{
		$url = str_replace('index.php/', '', $url);
		$url = str_replace('index.php', '', $url);
		$url = str_replace($this->getRootSiteUrl(), '', $url);
		$url = stripos($url, '?') ? substr($url, 0, stripos($url, '?')) : $url;
		return isset($url[0]) &&$url[0] == '/' ? substr($url, 1) : $url;
	}
	/**
	 * Adds an item as a child of the item
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
		return $this->isCurrentUrl();
	}
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
	public function isAuthenticated()
	{
		if(!$this->protected)
		{
			return true;
		}
		else
		{

			return sfContext::getInstance()->getUser()->isAuthenticated();
		}
	}
	
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
	public function setRoute($route)
	{
		$this->route = $route;
	}
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