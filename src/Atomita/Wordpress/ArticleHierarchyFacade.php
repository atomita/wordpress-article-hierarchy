<?php

/**
 * Facade of ArticleHierarchy
 */
class ArticleHierarchyFacade extends \Atomita\Facade
{
	static protected function facadeInstance()
	{
		static $instance;
		if (!isset($instance)){
			$instance = new ArticleHierarchy();
		}
		return $instance;
	}

}
