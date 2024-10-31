<?php
/*
Plugin Name: Seo URL Pages
Plugin URI: http://www.ajcrea.com/plugins/wordpress/plugin-wordpress-de-jolies-urls-pour-vos-pages.html
Author: AJcrea
Author URI: http://ajcrea.com
Version: 0.4.3
*/

add_filter('_get_page_link', 'seo_get_page_link',11,2);
add_filter('user_trailingslashit', 'seo_user_trailingslashit',11,2);
add_filter('category_link', 'seo_category_link',11,2);
add_action('init', 'seo_init');
add_action('template_redirect', 'seo_template_redirect',11,2);

add_filter('page_rewrite_rules','seo_page_rewrite_rules',11,1);

add_filter('redirect_canonical','seo_redirect_canonical',11,2);
add_filter('sanitize_title','seo_sanitize_title',9,2);


function seo_sanitize_title($title, $fallback_title = ''){
	return str_replace("'",'-',$title);
}

function seo_template_redirect($requested_url=null, $do_redirect=true){
	if ( !$requested_url ) {
		$requested_url  = ( !empty($_SERVER['HTTPS'] ) && strtolower($_SERVER['HTTPS']) == 'on' ) ? 'https://' : 'http://';
		$requested_url .= $_SERVER['HTTP_HOST'];
		$requested_url .= $_SERVER['REQUEST_URI'];
	}
	if(is_page()){
		if(is_front_page()) return $requested_url;
		global $wp_query;
		$bRedirectRequire = false;
		$page_obj = $wp_query->get_queried_object();
		$page_id = $page_obj->ID;
		if(substr($requested_url,-1)=='/'){
			if(count(get_pages('child_of='.$page_id))==0){
				$requested_url = substr($requested_url,0,-1);
				$requested_url .= '.html';
				$bRedirectRequire = true;
			}		
		}
		elseif(substr($requested_url,-5)=='.html'){
			if(count(get_pages('child_of='.$page_id))>0){
				$requested_url = substr($requested_url,0,-5);
				$requested_url .= '/';
				$bRedirectRequire = true;
			}
		}
		else{
			if(count(get_pages('child_of='.$page_id))==0){
				$requested_url .= '.html';
			}	
			else{
				$requested_url .= '/';
			}
			$bRedirectRequire = true;
		}
		if ( $do_redirect && $bRedirectRequire) {
			wp_redirect($requested_url, 301);
			exit();
		}	
	}
	return $requested_url;
}

function seo_redirect_canonical($redirect_url, $requested_url){
	if(substr($requested_url,-1) == '/' && substr($redirect_url,-1) != '/'){
		return false;
	}
	else{
		return $redirect_url;
	}	
}

function seo_page_rewrite_rules($rules){
	$return = $rules;
	foreach($rules as $key=>$rule){
	
		$aPattern = array(
			"/attachment/([^/]+)/?$",
			"/attachment/([^/]+)/trackback/?$",
			"/attachment/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$",
			"/attachment/([^/]+)/(feed|rdf|rss|rss2|atom)/?$",
			"/attachment/([^/]+)/comment-page-([0-9]{1,})/?$",
			"/trackback/?$",
			"/feed/(feed|rdf|rss|rss2|atom)/?$",
			"feed|rdf|rss|rss2|atom)/?$",
			"/page/?([0-9]{1,})/?$",
			"/comment-page-([0-9]{1,})/?$",
			"(/[0-9]+)?/?$");
		foreach($aPattern as $pattern){
			$akey = explode($pattern,$key);
			if(count($akey)==2){
				$return = $return + array($akey[0].'.html'.$pattern=>$rule);
				continue;
			}
		}
	}
	return $return;
}

function seo_init(){
	// si la variable existe, je suis sur la page de configuration des permaliens, je ne fais rien pour ne pas agir avant leur mise à jour.
	if (!isset($_POST['permalink_structure']) ) {
		$structure = get_option('permalink_structure');
		if(!preg_match('/\%category\%/', $structure)){
			update_option('permalink_structure','/%category%'.$structure);
		}
	}
	$firstTime = get_option('permalink_firstTime');
	if(!$firstTime){
		global $wp_rewrite;
		$wp_rewrite->flush_rules();
		add_option('permalink_firstTime',1);
	}
}

function seo_get_page_link($link, $id ){
	if ( !$id )
		$id = (int) $post->ID;

	if(count(get_pages('child_of='.$id))==0){
		$link = user_trailingslashit($link, 'page');
		$link.= '.html';
	}
	else{
		$link .= '/';
	}
	return $link;
}

function seo_user_trailingslashit($string, $type_of_url){
	if ($type_of_url == 'category')
		$string = trailingslashit($string);
	else
		$string = untrailingslashit($string);
	return $string;
}

function seo_category_link($catlink, $category_id){
		$catlink = str_replace('category/', '', $catlink);
		return $catlink;
}

?>