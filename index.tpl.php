<!doctype html>
<!--[if lt IE 7]> <html class="no-js ie6 oldie" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js ie7 oldie" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js ie8 oldie" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

	<title></title>
	<meta name="description" content="">
	<meta name="author" content="">

	<meta name="viewport" content="width=device-width,initial-scale=1">

	<link rel="stylesheet" href="css/style.css">

</head>
<body>

	<div id="nav">
		<?php foreach($view->blogs as $blog_name => $posts): ?>
		<a href="#<?php print $blog_name ?>"><img class="avatar" src="blogs/<?php print $blog_name ?>/avatar.png" /></a>
	  <?php endforeach; ?>
	</div>

	<div id="main-container">
		<div id="main" class="wrapper clearfix">
			
			<?php foreach($view->blogs as $blog_name => $posts): ?>
			<article>

				<header>
                    <h1 id="<?php print $blog_name; ?>"><a href="http://<?php print $blog_name ?>.tumblr.com" target="_blank"><img class="avatar" src="blogs/<?php print $blog_name ?>/avatar.png" /><?php print $blog_name; ?>.tumblr.com</a></h1>
					<p></p>
				</header>

				<?php foreach($posts as $post): ?>
				<section>
					<?php if (isset($post->photos)):?>
					<ul>
					<?php foreach($post->photos as $i => $photo): ?>
					<li><img src="<?php $filepath = sprintf('%s/photo%03d.png', create_post_path($post), $i + 1);  print $filepath ?>" style="width: 500px"/></li>
					<?php endforeach ?>
					</ul>
					<?php endif ?>
					<div class="caption">
						<?php print isset($post->caption) ? $post->caption : '' ?>
					</div>
				</section>
				<?php endforeach ?>

			</article>
			<?php endforeach?>
		</div> <!-- #main -->
	</div> <!-- #main-container -->

	<div id="footer-container">
		<footer class="wrapper">
			<h3>footer</h3>
		</footer>
	</div>

</body>
</html>
