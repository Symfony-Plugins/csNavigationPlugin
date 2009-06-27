<?php
/* 
 * This file is part of the isicsBreadcrumbsPlugin package.
 * 
 * Copyright (C) 2007-2008 ISICS.fr <contact@isics.fr>
 * 
 * isicsBreadcrumbsPlugin is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * isicsBreadcrumbsPlugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public License
 * along with isicsBreadcrumbsPlugin.  If not, see <http://www.gnu.org/licenses/>.
 */

abstract class BasecsNavigationComponents extends sfComponents 
{
  public function executeBreadcrumbs()
  {
    if(csBreadcrumbNavigation::getInstance()->hasItems())
    {
      $nav = csBreadcrumbNavigation::getInstance();
    }
    else 
    {
      $nav = csNavigationHelper::getBreadcrumb($this->level);
      // $nav->generateBreadcrumbFromNavigation();      
    }
    
    
    
    if($nav)
    {
      if (isset($this->root))
      {
        $nav->setRoot($this->root['text'], $this->root['uri']);
      }

      if (!isset($this->offset))
      {
        $this->offset = 0;
      }
      $this->items = $nav->getItems($this->offset);
    }
    $this->class = isset($this->class) ? $this->class : '';
  }  
  public function executeTree()
  {
    $this->iterations = $this->iterations ? $this->iterations : 0;
    $this->level = $this->level ? $this->level : 0;

    $this->max_level = $this->iterations ? ($this->level ? $this->level + $this->iterations : $this->iterations) : null;
    if(!isset($this->items))
    {
      $nav = csNavigationHelper::getNavigationTree($this->level, $this->iterations);
      $this->title = isset($this->title) ? $this->title : $nav->getTitle();
      $this->items = $nav->getItems($this->offset);
    }
    $this->class = isset($this->class) ? $this->class : '';
    $this->id = isset($this->id) ? $this->id : '';
  }
}