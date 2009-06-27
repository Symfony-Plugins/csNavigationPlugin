<?php

class csBaseNavigation
{
	static protected $session_name = 'csBaseNavigation';
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
    $this->save();
  }

  /**
   * Delete all existings items
   *
   */
  public function clearItems()
  {
    $this->items = array();
    $this->save();
  }  
  
  /**
   * Get the unique csBaseNavigation instance (singleton)
   *
   */
	//   public static function getInstance($session_name = null)
	//   {
	//     if (is_null(self::$instance))
	//     {
	//       if (sfContext::getInstance()->getRequest()->getParameter($session_name))
	//       {
	//         self::$instance = sfContext::getInstance()->getRequest()->getParameter($session_name);
	//       }
	//       else
	//       {
	// 			self::createInstance();
	//       	sfContext::getInstance()->getRequest()->setParameter($session_name, self::$instance);
	// 		}
	//     }
	//     return self::$instance;
	//   }
	// 
	// public static function createInstance()
	// {
	// 	self::$instance = new csBaseNavigation();
	//     self::$instance->save();
	// }
  
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
  protected function save()
  {
    sfContext::getInstance()->getRequest()->setParameter(self::$session_name, $this);
  }
}