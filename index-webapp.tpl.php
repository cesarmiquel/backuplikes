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

	<link rel="stylesheet" href="css/style.css" />
	<link rel="stylesheet" href="css/flexslider.css" />

	<script type="text/javascript" src="js/jquery.min.js"></script>
	<script type="text/javascript" src="js/jquery.flexslider.js"></script>
	<script type="text/javascript" src="js/jquery.masonry.min.js"></script>

</head>
<body>

	<div id="nav">
		<div class="flexslider">
		  	<ul class="slides">
			<?php foreach($view->blogs as $blog_name => $posts): ?>
			    <li>
					<a href="#<?php print $blog_name ?>" data-blog="<?php print $blog_name ?>" class="blog-icon"><img class="avatar" src="blogs/<?php print $blog_name ?>/avatar.png" /></a>
			    </li>
		  	<?php endforeach; ?>
			</ul>
		</div>

		<div id="blog-info"></div>
	</div>

	<div id="main-container">
		<div id="main" class="wrapper clearfix">
		</div> <!-- #main -->
	</div> <!-- #main-container -->

	<script type="text/javascript">
		// Blog data.
	  	window.blogs = <?php print json_encode($view->blogs); ?>;
	</script>
	<script type="text/javascript" src="js/app.js"></script>

</body>
</html>