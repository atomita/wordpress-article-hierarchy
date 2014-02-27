<?php

/**
 *
 */
class ArticleHierarchy
{
	var $list_template = <<<EOD
<li><a href="%s">%s</a>%s</li>
EOD;

	var $wrap_template = <<<EOD
<ul class="level-%d %s">
	%s
</ul>
EOD;

	/**
	 *
	 * @param	$id	post id
	 * @return	string
	 */
	function get($id = null)
	{
		if (is_array($id)){
			$tree = $id;
		}
		else{
			$tree = $this->tree($id);
		}
		return $this->_get(1, $tree);
	}
	
	
	/**
	 * @return string
	 */
	function _get($level = 1, array $pages = null)
	{
		if (empty($pages)){
			return '';
		}

		$children = array();
		foreach ($pages as $parent => $childs){
			$children[] = sprintf(
				$this->list_template,
				esc_url(get_permalink($parent)),
				esc_html(get_the_title($parent)),
				$this->_get($level + 1, $childs));
		}
		return sprintf($this->wrap_template, $level, '', implode(PHP_EOL, $children));
	}

	function tree($id = null)
	{
		if (is_null($id)){
			$id = get_the_ID();
		}
	
		if (is_singler($id)){
			$ancestors = get_post_ancestors($id);
			$parent    = empty($ancestors) ? $id : end($ancestors);
			return array(
				$parent => $this->getChildren($parent)
			);
		}
		return array();
	}

	function getChildren($parent, $post_type = null)
	{
		if (is_null($post_type)){
			$post_type = get_post_type($parent);
		}
		
		$children = get_posts(array(
			'post_type'   => $post_type,
			'post_parent' => $parent,
			'orderby'	  => 'menu_order date',
			'order'	  => 'ASC',
		));

		$childs = array();
		foreach ($children as $child){
			$childs[$child->ID] = $this->getChildren($child->ID, $post_type);
		}
		return $childs;
	}
	
	
}
