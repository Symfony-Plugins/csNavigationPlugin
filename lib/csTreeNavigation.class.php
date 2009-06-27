<?php


class csTreeNavigation extends csBaseTreeNavigation
{
  public function setup()
  {
    // Extend this class here to dynamically add branches to your tree using
    // the appendItem() method.  These results will be cached, so you must 
    // implement cache clearing in your code:
    
    /*
    $cachePath = sfConfig::get('sf_cache_dir').'/navigation_tree.cache';
    if (file_exists($cachePath)) 
    {
      unlink($cachePath);
    }
    */
  }
}