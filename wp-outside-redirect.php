<?php
/**
 This should be causing a url request on a website, say http://example.com/some/location and redirect it by comparing it with a set of links and redirecting to the new location. URL's should be passed through index.php and ultimatly this .php file and redirected before the 404 is sent.
 */

/*
Plugin Name: Outside link redirect
Plugin URI: http://bitbucket.org/enethrie/outside-link-redirect
Description: Takes an incoming http request/url and redirects it to a new one.
Author: Aubrey Portwood
Version: 0.1
Author URI: http://enethrie.com
*/


$olr = get_option('olr');

	function olr_currentRequest() {
		$pageURL = 'http';
			if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
				$pageURL .= "://";
			if ($_SERVER["SERVER_PORT"] != "80") {
				$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
			} else {
				$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
			}
		return $pageURL;
	}
	
	//app
	foreach($olr as $olr_item){
		//if the current url == a link int he list,
		if(olr_currentRequest() == $olr_item['link']){
			//go to redirect
				//wp_redirect($olr_item['redirect']); exit();
		}
	}

if($_GET['reset']){
	update_option('olr','');
		header('location:tools.php?page=olr&reset=true');
}

if($_GET['delete']){
	$id = $_POST['id'];
	
	foreach($olr as $olr_item){
		$c++;
		if($c != $id) $olr_new[]=$olr_item;
	}

	update_option('olr',$olr_new);	
	header('location:tools.php?page=olr&deleted=true');
}

if($_GET['save']){
	$link = $_POST['link'];
	$redirect = $_POST['redirect'];
	
	$olr_add['link']=$link;
	$olr_add['redirect']=$redirect;
	
	$olr[]=$olr_add; //add it to olr array
	
	update_option('olr',$olr);
	header('location:tools.php?page=olr&saved=true');
}

add_action('admin_menu', 'olr_admin'); function olr_admin(){
	add_submenu_page(
		'tools.php', 
		'Outside Link Redirect', 
		'Outside Link Redirect', 
		'manage_options', 
		'olr', 
		'olr_options'
	);
}

function olr_options(){
	global $olr;
	?>
		<style scoped>
			.links input[type="text"]{
				width: 200px;
			}
		</style>
		<div class="wrap">
			<h2>Outside Link Redirect</h2>
			<p>
				Use this tool to direct an incoming request (URL) to a new one.<br>
				<em>Good for redirecting old locations and webpages to new ones.</em>
			</p>
			
			<h3>Redirects</h3>
			<?php foreach($olr as $olr_item): $c++; ?>
				<form action="tools.php?page=olr&delete=true" method="post" class="links">
					<p>
						<input type="text" name="link" id="link" value="<?php echo $olr_item['link']; ?>">
						 to 
						<input type="text" name="redirect" id="redirect" value="<?php echo $olr_item['redirect']; ?>">
						<input type="submit" value="Delete">
						<input type="hidden" name="id" id="id" value="<?php echo $c; ?>">
					</p>
				</form>
			<?php endforeach; ?>				
			
				
			<form action="tools.php?page=olr&save=true" method="post" class="links">
				<p>
					<input type="text" name="link" id="link"> to 
					<input type="text" name="redirect" id="redirect">
					<input type="submit" value="Add">	
				</p>
			</form>
			
		</div>
	<?php
}

?>