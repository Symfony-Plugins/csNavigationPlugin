<?php
/* 

 * The csBaseNavigation and csNavigationItem classes borrow heavily from the 
 * isicsBreadcrumbs class.  Both the isicsBreadcrumbs class and the csNavigation
 * plugin package cohere to the license below
 * 
 * 
 * csNavigationPlugin is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * csNavigationPlugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public License
 * along with isicsBreadcrumbsPlugin.  If not, see <http://www.gnu.org/licenses/>.
 */
class csBreadcrumbNavigation extends csBaseNavigation
{
  static    $instance = null;
  protected     $appended_items = array();
  static protected $session_name = 'csBreadcrumbNavigation';
  /**
   * Constructor
   *
   */
  public function __construct($items = null)
  {
    $this->setRoot('Home', '@homepage');
    if($items)
    {
      $this->items = array_merge($this->items, $items);
    }
  }
    
  /**
   * Redefine the root item
   *
   */
  public function setRoot($name, $route)
  {
    $this->items[0] = new csNavigationItem($name, $route);
    $this->save();
  }
  public function generateBreadcrumbFromNavigation()
  {
    $nav = csNavigationHelper::getNavigationItems();
    foreach ($nav as $item) {
      if($breadcrumb = $item->getBreadcrumbArray())
      {
        $this->appendItems($breadcrumb);      
        return; 
      }
    }
  }
  static function appendItem($item)
  {
    self::getInstance()->appended_items[] = $item;
  } 
  static function appendItems($items)
  {
    foreach ($items as $item) {
      self::appendItem($item);
    }
  }
  public function getItems($offset = 0)
  {
    return array_merge($this->items, $this->appended_items);
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

  public static function createInstance()
  {
    self::$instance = new csBreadcrumbNavigation();
    self::$instance->save();
  }
  public function hasItems()
  {
    if(sizeof($this->getItems()) == 1)
    {
      return !($this->items[0]->name == 'Home' && $this->items[0]->route == '@homepage');
    }
    return parent::hasItems();
  }
}