<style scoped>
	.links input[type="text"]{
		width: 200px;
	}
</style>
<div class="wrap">

	<?php if(isset($_GET['htaccess_error']) && $_GET['htaccess_error']=="1"): ?>
		<div class="error"><p>.htaccess Redirect couldn't write to your <code>.htaccess</code> file, please make sure it's writable and try again.</p></div>
	<?php endif; ?>

	<?php if(isset($_GET['exists']) && $_GET['exists']=="1"): ?>
		<div class="error"><p>Sorry, but that redirect already exists.</p></div>
	<?php endif; ?>

	<?php if(isset($_GET['novalue']) && $_GET['novalue']=="1"): ?>
		<div class="error"><p>Please make sure to provide valid values in both fields.</p></div>
	<?php endif; ?>

	<?php if(isset($_GET['noturl']) && $_GET['noturl']=="1"): ?>
		<div class="error"><p>One of the fields is not formatted properly. Please make sure and supply properly formatted URL's.<br><strong>Please do not use realtive paths, please use absolute URL's</strong>, they will be coverted automatically. <em>See tooltips on fields for help.</em></p></div>
	<?php endif; ?>

	<?php if(isset($_GET['parsed']) && $_GET['parsed']=="1"): ?>
		<div class="error"><p>You are trying to redirect a domain, .htaccess Redirect doesn't do that. Please provide a URL with a path, such as <code>http://example.com/my/path/to/file.html</code></p></div>
	<?php endif; ?>

	<h2>.htaccess Redirect</h2>
	<p>
		This plugin modifies your <code>.htaccess</code> file to redirect requests to new locations. This is especially useful (and intended) to redirect requests to web locations and pages outside of your WordPress installation to pages now in WordPress.

		For instance, you could redirect <code>http://example.com/old/raw/web/user/enethrie/my_web_page.html</code> to <code>http://example.com/enethrie/</code> or <code>http://somewhereelse.com/</code>
	</p>

	<h3>Direct path to .htaccess</h3>
	<form action="tools.php?page=olr&htaccess=true" method="post" class="links">
		<div class="error">
			<p><strong>Warning:</strong> Please only use this plugin if you know what you're doing and can edit your <code>.htaccess</code> file manually. .htaccess Redirect is currently in beta, and may cause problems for your install.</p>
		</div>
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
	<?php $c=0; if(is_array($olr) && sizeof($olr>0)) foreach($olr as $olr_item): $c++; ?>
		<form action="tools.php?page=olr&delete=true" method="post" class="links">
			<p>
				From <input type="text" name="link" id="link" disabled value="<?php echo $olr_item['link']; ?>" title="Note: the URL was reduced to a direct path on your site.">
				 to
				<input type="text" name="redirect" id="redirect" disabled value="<?php echo $olr_item['redirect']; ?>">
				<input type="hidden" name="id" id="id" value="<?php echo $c; ?>">
				<input type="submit" value="Delete">
			</p>
		</form>
	<?php endforeach; else echo "<p>No redirects</p>"; ?>

	<form action="tools.php?page=olr&save=true" method="post" class="links" style="border-top: 1px dotted #dadada">

		<p>
			From <input type="text" name="link" id="link" title="Examples: http://example.com/location/, http://example.com/location/file.php" required type="url" value="<?php if(isset($_GET['link'])) echo $_GET['link'] ?>">

			to

			<input type="text" name="redirect" id="redirect" title="Examples: http://example.com, http://example.com/location/, http://example.com/location/file.html" required type="url" value="<?php if(isset($_GET['redirect']))  echo $_GET['redirect'] ?>">
			<input type="submit" value="Add">

			<small><a id="fphelpt" href="javascript:jQuery('#fhelp').toggle();">Formatting?</a></small>
		</p>

	</form>

	<div style="">

		<div id="fhelp" style="display:none;">
			<p style="margin-left:20px;">
				<small>
					<strong>From:</strong> the from field must be a URL with a path to a directory or file, you <em>cannot</em> use URL's of domains like <code>http://example.com</code>, you must supply URL's like <code>http://example.com/path/to</code> or <code>http://example.com/path/to/file.html</code>. URL's in the from field will be automatically coverted to relative paths.
				</small>
			</p>
			<p style="margin-left:20px;">
				<small>
					<strong>To:</strong> the to field must be a URL, but does <strong>not</strong> have to supply a path. Domains can be used, like <code>http://example.com</code>.
				</small>
			</p>
		</div>
	</div>

	<?php if(file_exists(get_option('olr_htaccess').'.htaccess')): ?>
		<h3>Your .htaccess</h3>
		<p>
			<pre style="height:200px;overflow:auto;background:#eee;padding:10px;"><small><?php global $htaccess; echo $ht = htmlentities(file_get_contents($htaccess)); ?><small></pre>
		</p>
	<?php endif; ?>

</div>
