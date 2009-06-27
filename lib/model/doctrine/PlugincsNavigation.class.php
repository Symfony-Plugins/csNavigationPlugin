<?php

/**
 * BasesfSettings 
 * 
 * @package 
 * @version $id$
 * @copyright 2006-2007 Chris Wage
 * @author Chris Wage <cwage@centresource.com> 
 * @license See LICENSE that came packaged with this software
 */
abstract class PlugincsNavigation extends BasecsNavigation
{
  public function postSave($event)
  {
    $cachePath = sfConfig::get('sf_cache_dir').'/navigation_tree.cache';
    if (file_exists($cachePath)) 
    {
      unlink($cachePath);
    }
  }
}

?>
