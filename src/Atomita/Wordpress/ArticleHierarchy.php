<?php

namespace Atomita\Wordpress;

/**
 * article hierarchy
 */
class ArticleHierarchy
{
	var $name = 'article-hierarchy';

	var $list_template = <<<EOD
<li class="level-%d %s"><a href="%s">%s</a>%s</li>
EOD;

	var $wrap_template = <<<EOD
<ul class="level-%d-wrap %s">
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
		if (!empty($around) and is_string($around)){
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
		$def = array(
			'id'	   => 0,
			'url'	   => false,
			'title'    => false,
			'children' => null
		);

		$children = array();
		foreach ($pages as $page){
			$page = wp_parse_args($page, $def);
			
			$args = apply_filters(
				"{$this->name}-after-list-format-params",
				array(
					$this->list_template,
					$level,
					'',
					esc_url($page['url'] ? $page['url'] : get_permalink($page['id'])),
					esc_html($page['title'] ? $page['title'] : get_the_title($page['id'])),
					$this->templateApplied($page['children'], $level + 1)
				),
				array(
					'page'	=> $page,
					'level' => $level
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
			),
			array(
				'pages' => $pages,
				'level' => $level
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
	
		$ancestors = get_post_ancestors($id);
		$parent    = empty($ancestors) ? $id : end($ancestors);
		return array(array(
			'id'	   => $parent,
			'url'	   => get_permalink($parent),
			'title'    => get_the_title($parent),
			'children' => $this->children($parent)
		));
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

		if (is_null($post_type)){
			$post_type = get_post_type($parent);
		}
		
		$childs = get_posts(array(
			'post_type'   => $post_type,
			'post_parent' => $parent,
			'orderby'	  => 'menu_order date',
			'order'	  => 'ASC',
			'numberposts' => -1
		));

		$children = array();
		foreach ($childs as $child){
			$children[] =  array(
				'id'	   => $child->ID,
				'url'	   => get_permalink($child->ID),
				'title'    => get_the_title($child->ID),
				'children' => $this->children($child->ID, $post_type)
			);
		}
		return $children;
	}

}
