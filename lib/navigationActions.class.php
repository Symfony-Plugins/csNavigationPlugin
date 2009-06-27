<?php


class navigationActions extends sfActions
{
	protected $bc_array = array();
	protected $bc_base = null;
	
	protected $nav_array = array();
	
	public function postExecute()
	{
		if(!$this->disableBreadcrumbs)
		{
			$this->getBreadcrumbs();
		}
		$this->setLeftNavigation();
	}
	public function disableBreadcrumbs()
	{
		isicsBreadcrumbs::getInstance()->clearItems();
		$this->disableBreadcrumbs = true;
	}
	public function disableLeftNavigation()
	{
		csLeftNavigation::getInstance()->clearItems();
		$this->disableLeftNavigation = true;
	}
	public function getBreadcrumbs()
	{
		sfProjectConfiguration::getActive()->loadHelpers(array('Navigation'));
		$module = $this->getRequestParameter('module');
		$action = $this->getRequestParameter('action');
		$this->bc_base = ($this->bc_base ? $bc_base : pcase($module));
		if($action != 'index')
		{
			isicsBreadcrumbs::getInstance()->addItem($this->bc_base, $module);	
			$this->getLastBreadcrumb();
		}
		else
		{
			isicsBreadcrumbs::getInstance()->addItem($this->bc_base);			
		}
	}
	public function getLastBreadcrumb()
	{
		if(!$this->bc_array)
		{
			$title = pcase($this->getRequestParameter('action'));
			isicsBreadcrumbs::getInstance()->addItem($title, $this->getContext()->getRequest()->getURI());
		}
		else
		{
			foreach ($this->bc_array as $title => $link) {
				isicsBreadcrumbs::getInstance()->addItem($title, $link);
			}
		}
		sfProjectConfiguration::getActive()->loadHelpers(array('Navigation'));
	}
	public function setLeftNavigation($navArray = null, $title = '')
	{

		$module = $this->getRequestParameter('module');
		$navArray = $navArray ? $navArray : sfConfig::get('app_navigation_'.$module);
		if($navArray)
		{
			$title = $title ? $title : pcase($this->getRequestParameter('module'));
			csLeftNavigation::getInstance()->setTitle($title);
			csLeftNavigation::getInstance()->setWithArray($navArray);
		}
		else
		{
			$this->disableLeftNavigation();
		}
	}
	public function setNavArray($navArray = array())
	{
		$this->nav_array = $navArray;
	}
	public function setBreadcrumbArray($bcArray = array())
	{
		$this->bc_array = $bcArray;
	}
	public function addBreadcrumb($title, $link = null)
	{
		$this->bc_array[(string)$title] = $link; 
	}
	public function setBreadcrumbBase($bcBase = array())
	{
		$this->bc_base = $bcBase;
	}
}