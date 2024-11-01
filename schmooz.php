<?php
/*
 Plugin Name: Schmooz comment system 
 Plugin URI: http://www.youschmooz.com/
 Version: x1.00
 Author: <a href="http://www.youschmooz.com/">Dhruvil Patel</a>
 Description: The next generation of online commenting.
 
*/

session_start();
error_reporting (E_ALL ^ E_NOTICE);

$siteurl = get_option('siteurl');

define('PLUGIN_URL', $siteurl.'/wp-content/plugins/schmooz/');

class schmooz
{	
	var $temp = "schmooz";
	
	function addHeaderCode()
	{
	?>
		<script type="text/javascript" src="http://www.youschmooz.com/schmoozapi/js/jquery-1.7.2.js"></script>
		<script type="text/javascript" src="http://www.youschmooz.com/schmoozapi/js/jquery.pagination.js"></script>
		<script type="text/javascript" src="http://www.youschmooz.com/schmoozapi/js/jquery.timeago.js"></script>
		<script type="text/javascript" src="http://www.youschmooz.com/schmoozapi/js/mypagination.js"></script>
	<?php
	}
	
	function Dashboard()
	{
		echo "<h2>Schmooz comment system</h2>";
		include "main.php";
	}

	function SubCemment()
	{
		include "main.php";
	}

	function Subsetting()
	{
		include "main.php";
	}

	function CreateJsoneFile()
	{
		if(is_single())
		{
			$current_user = wp_get_current_user();
			global $post;

			if ( 0 == $current_user->ID ) {
				echo '<input type="hidden" value="" name="user_hash" id="user_hash" />';
			} else {
				$user_hash = base64_encode($current_user->user_email."_".$current_user->user_login);
				echo '<input type="hidden" value="'.$user_hash.'" name="user_hash" id="user_hash" />';
			}
		?>
			<input type="hidden" value="<?php echo get_permalink(); ?>" name="param_link" id="param_link" />
			<input type="hidden" value="<?php echo get_bloginfo('url'); ?>" name="post_url" id="post_url" />
			<input type="hidden" value="<?php echo $post->post_name;?>" name="slug" id="slug" />
			<input type="hidden" value="comments" name="container_id" id="container_id" />
			<input type="hidden" value="<?php echo get_option("schmooz_short_name", ''); ?>" name="shortname" id="shortname" />
			<input type="hidden" value="<?php echo $post->ID ?>" name="artical_id" id="artical_id" />
			<input type="hidden" value="<?php echo $post->post_date ?>" name="created_date" id="created_date" />
			<input type="hidden" value="<?php echo get_the_title($post->ID);?>" name="artical_title" id="artical_title" />
			<script type="text/javascript">
			var url = "http://www.youschmooz.com/schmoozapi/js/embed.js";
			var script = document.createElement('script');
			script.setAttribute('type', "text/javascript");
			script.setAttribute('src', url);
			document.getElementsByTagName('head')[0].appendChild(script);
			</script>
		<?php
		}
	}
}

if (class_exists("schmooz"))
{
	$obj_schmooz = new schmooz;
}

function custom_template()
{
	return dirname(__FILE__) . '/comments.php';
}

add_filter('comments_template', 'custom_template');

add_action('wp_footer',array(&$obj_schmooz,'CreateJsoneFile'));
add_action('wp_head',array(&$obj_schmooz, 'addHeaderCode'), 1);
add_filter('comments_popup_link',array(&$obj_schmooz,'Customecomments_popup_link'),1);
add_action('admin_menu', 'comment_sub_menu');
add_action('admin_menu', 'setting_sub_menu');

function comment_sub_menu()
{
	global $obj_schmooz;
	add_comments_page('Schmooz Comments','Schmooz','read','schmooz-comment', array(&$obj_schmooz,'SubCemment'));
}

function setting_sub_menu()
{
	global $obj_schmooz;
	add_options_page('Schmooz Comments','Schmooz','read','schmooz-setting', array(&$obj_schmooz,'Subsetting'));
}
?>