<?php

/**
 * article hierarchy
 */
class ArticleHierarchy
{
	var $name = 'article-hierarchy';

	var $list_template = <<<EOD
<li><a href="%s">%s</a>%s</li>
EOD;

	var $wrap_template = <<<EOD
<ul class="level-%d %s">
	%s
</ul>
EOD;

	/**
	 * make article hierarchy
	 * @param	$id	post id
	 * @return	string
	 */
	function get($id = null)
	{
		do_action("{$this->name}-before-get");

		$around = apply_filters("{$this->name}-around-get", '', $id);
		if (!empty($around) or is_string($around)){
			$value = $around;
		}
		else{
			if (is_array($id)){
				$tree = $id;
			}
			else{
				$tree = $this->tree($id);
			}
			$value = $this->templateApplied($tree);
		}
		return apply_filters("{$this->name}-after-get", $value, $id);
	}
	
	
	/**
	 * @return string
	 */
	protected function templateApplied(array $pages = null, $level = 1)
	{
		if (empty($pages)){
			return '';
		}

		$children = array();
		foreach ($pages as $parent => $childs){
			$args = apply_filters(
				"{$this->name}-after-list-format-params",
				array(
					$this->list_template,
					esc_url(get_permalink($parent)),
					esc_html(get_the_title($parent)),
					$this->templateApplied($childs, $level + 1)
				)
			);
			$children[] = call_user_func_array('sprintf', $args);
		}
		$wrap_args = apply_filters(
			"{$this->name}-after-wrap-format-params",
			array(
				$this->wrap_template,
				$level,
				'',
				implode(PHP_EOL, $children)
			)
		);
		return call_user_func_array('sprintf', $wrap_args);
	}

	/**
	 * make article hierarchy tree
	 * @param	$id	post id
	 * @return	array
	 */
	function tree($id = null)
	{
		if (is_null($id)){
			$id = get_the_ID();
		}
	
		if (is_singler($id)){
			$ancestors = get_post_ancestors($id);
			$parent    = empty($ancestors) ? $id : end($ancestors);
			return array(
				$parent => $this->children($parent)
			);
		}
		return array();
	}

	/**
	 * make article hierarchy children
	 * @param	int	parent post id
	 * @param	string	post type
	 * @return	array
	 */
	function children($parent = null, $post_type = null)
	{
		if (is_null($parent)){
			$parent = get_the_ID();
		}

		$childs = array();

		if (is_singler($parent)){
			if (is_null($post_type)){
				$post_type = get_post_type($parent);
			}
		
			$children = get_posts(array(
				'post_type'   => $post_type,
				'post_parent' => $parent,
				'orderby'	  => 'menu_order date',
				'order'	  => 'ASC',
			));

			foreach ($children as $child){
				$childs[$child->ID] = $this->children($child->ID, $post_type);
			}
		}
		return $childs;
	}

}
