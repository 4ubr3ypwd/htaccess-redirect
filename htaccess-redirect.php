<?php

/*
Plugin Name: .htaccess Redirect
Plugin URI: https://bitbucket.org/enethrie/.htaccess-redirect
Description: This plugin modifies your .htaccess file to redirect requests to new locations. This is especially useful (and intended) to redirect requests to web locations/pages outside of your WordPress installation to pages now in WordPress.
Author: Aubrey Portwood
Version: 0.1
Author URI: http://enethrie.com
*/

$htaccess = get_option('olr_htaccess').".htaccess";
$olr = get_option('olr');
$olr_comment = "#by .htaccess Redirect";

if($_GET['htaccess']){
	update_option('olr_htaccess',$_POST['htaccess']);
	header('location:tools.php?page=olr&deleted=true');	
}

if($_GET['delete']){
	$id = $_POST['id'];

	//Get which link and redirect to search for (same as below)
	foreach($olr as $olr_item){
		$j++;
			if($j == $id){
				$link=$olr_item['link'];
				$redirect=$olr_item['redirect'];
			}
	}
	
	//Remove it from htaccess
	$old_htaccess = file_get_contents($htaccess);
	$new_htaccess = str_replace("\n\n$olr_comment\nRedirect $link $redirect",'',$old_htaccess);

		//write the changes
		$htacces_error = "0";
		$fh = fopen($htaccess, 'w')
			or $htacces_error="1";
				fwrite($fh, $new_htaccess);
		fclose($fh);
		
	//Take it out of the DB
	if($htacces_error=="0"){
		foreach($olr as $olr_item){
			$c++;
				if($c != $id){ 
					$olr_new[]=$olr_item;
				}
		} update_option('olr',$olr_new); 
	}
		
	header('location:tools.php?page=olr&deleted=true&htaccess_error=$htacces_error');
}



if($_GET['save']){
	$link = $_POST['link'];
	$redirect = $_POST['redirect'];
	
	//strip domain/protocol from link
	$parsed = parse_url($link);
	$link = $parsed['path'];
	
	//validate $link if it's okay for htaccess
	if($link[0]!="/"){
		header('location:tools.php?page=olr&noturl=1');
		exit();
	}
	
	//valideate $redirect safe with htaccess
	$error_level = error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
	$fp = fopen($redirect, 'r');
	if (!$fp) {
		header('location:tools.php?page=olr&redirectnotactive=1');
		exit();
	}
	fclose($fp);
	error_reporting($error_level);
	
	//error checking
	if(!$parsed['path']){
		header('location:tools.php?page=olr&parsed=1');
		exit();
	}
	if($link=='' || $redirect==''){
		header('location:tools.php?page=olr&novalue=1');
		exit();
	}
	
	//Test if the redirect is already there
	foreach($olr as $olr_item){
		if($link == $olr_item['link']
		&& $redirect == $olr_item['redirect']
		) $exists=true; else $exists=false;
	} 
	
	if(!$exists){
			//Add the RedirectMatch to the .htaccess file
			$old_htaccess = file_get_contents($htaccess);
			$new_htaccess = $old_htaccess 
				. "\n\n$olr_comment\nRedirect $link $redirect";
			
					//write the changes
					$htacces_error = "0";
					$fh = fopen($htaccess, 'w')
						or $htacces_error="1";
							fwrite($fh, $new_htaccess);
					fclose($fh);

			// Save the record in the DB
			if($htacces_error=="0"){
				$olr_add['link']=$link;
				$olr_add['redirect']=$redirect;
				$olr[]=$olr_add; //add it to olr array
				update_option('olr',$olr);
			}

		header("location:tools.php?page=olr&saved=true&htaccess_error=$htacces_error");
	}else{
		header("location:tools.php?page=olr&exists=1");
	}
}

add_action('admin_menu', 'olr_admin'); function olr_admin(){
	add_submenu_page(
		'tools.php', 
		'.htaccess Redirect', 
		'.htaccess Redirect', 
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
		
			<?php if($_GET['htaccess_error']=="1"): ?>
				<div class="error"><p>I couldn't write to your <code>.htaccess</code> file, please make sure it's writable, go back, and try again.</p></div>
			<?php endif; ?>
			
			<?php if($_GET['exists']=="1"): ?>
				<div class="error"><p>Sorry, but that redirect already exists.</p></div>
			<?php endif; ?>

			<?php if($_GET['novalue']=="1"): ?>
				<div class="error"><p>Please make sure to provide values for both fields.</p></div>
			<?php endif; ?>
			
			<?php if($_GET['redirectnotactive']=="1"): ?>
				<div class="error"><p>The resource you are trying to redirect to is unavailable, or you did not supply a URL. Redirect locations must be supplied in URL format.</p></div>
			<?php endif; ?>

			<?php if($_GET['noturl']=="1"): ?>
				<div class="error"><p>Either one of the URL's you are trying to use does not exist, or you didn't enter a valid URL. Please check the resource and try again.</p></div>
			<?php endif; ?>
			
			<?php if($_GET['parsed']=="1"): ?>
				<div class="error"><p>You are trying to redirect a whole URL, .htaccess Redirect doesn't do that. Please provide a URL with a path, such as <code>http://example.com/my/path/to/file.html</code></p></div>
			<?php endif; ?>		
			<h2>.htaccess Redirect</h2>
			<p>
				This plugin modifies your <code>.htaccess</code> file to redirect requests to new locations. This is especially useful (and intended) to redirect requests to web locations/pages outside of your WordPress installation to pages now in WordPress.
			</p>
			
			<p>
				For instance, you could redirect <code>http://example.com/old/raw/web/user/enethrie/my_web_page.html</code> to <code>http://example.com/enethrie/</code>
			</p>
			
			
			<h3>Direct path to .htaccess</h3>
			<form action="tools.php?page=olr&htaccess=true" method="post" class="links">
				<p>
					<input type="text" id="htaccess" name="htaccess" value="<?php echo get_option('olr_htaccess'); ?>">
					<input type="submit" value="Save">
					<small>Example: <?php echo str_replace("wp-content/plugins","",dirname(__FILE__)); ?> (keep trailing slash)</small>
				</p>
				<?php if(!file_exists(get_option('olr_htaccess').'.htaccess')): ?>
					<div class="error"><p>Couldn't find your <code>.htaccess</code> file, please check your settings.</p></div>					
				<?php else: ?>
					<div class="updated"><p>Found your <code>.htaccess</code> file, please make sure it's writeable (775).</p></div>
				<?php endif; ?>
			</form>
			
			<h3>Redirects</h3>

			<?php foreach($olr as $olr_item): $c++; ?>
				<form action="tools.php?page=olr&delete=true" method="post" class="links">
					<p>
						<input type="text" name="link" id="link" disabled value="<?php echo $olr_item['link']; ?>">
						 to 
						<input type="text" name="redirect" id="redirect" disabled value="<?php echo $olr_item['redirect']; ?>">
						<input type="hidden" name="id" id="id" value="<?php echo $c; ?>">
						<input type="submit" value="Delete">						
					</p>
				</form>
			<?php endforeach; ?>
			
			<form action="tools.php?page=olr&save=true" method="post" class="links" style="border-top: 1px dotted #dadada">

				<p>										
					<input type="text" name="link" id="link" title="Can either be in path format (/location/of/file.html) or URL format including protocol, domain, and path (http://example.com/location/)."> to 
					<input type="text" name="redirect" id="redirect" title="Must be in URL format, including protocol, domain and path (http://example.com/location/).">
					<input type="submit" value="Add">	
				</p>
				<p>
					<input type="text" name="link1" id="link1" value="/location/of/file.html" disabled title=""> to 
					<input type="text" name="redirect2" id="redirect2" value="http://example.com/location/of/redirect" disabled title=""> How to format
				</p>
				</form>
			
			<?php if(file_exists(get_option('olr_htaccess').'.htaccess')): ?>
				<h3>Your .htaccess</h3>
				<p>
					<pre style="height:200px;overflow:auto;background:#eee;padding:10px;"><small><?php global $htaccess; echo $ht = htmlentities(file_get_contents($htaccess)); ?><small></pre>
				</p>
			<?php endif; ?>
			
		</div>
	<?php
}

?>