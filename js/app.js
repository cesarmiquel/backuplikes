$(window).load(function() {

	window.selectedBlog = '';

	$('.flexslider').flexslider({
	    animation: "slide",
	    animationLoop: false,
	    slideshow: false,
	    controlNav: false,
	    mouseWheel: false,
	    itemWidth: 60,
	    itemMargin: 5
	});

	// hide blog info on start
	$('#blog-info').hide();

	$('.blog-icon').hover(function() {
		var blogName = $(this).data('blog');
		showBlogInfo(blogName);
	});

	$('.blog-icon').click(function() {
		var blogName = $(this).data('blog');
		var info = window.blogs[blogName].info;

		var html = '<article class="blog-posts">';
		for(var i = 0; i < window.blogs[blogName].posts.length; i++) {
			html += '<section class="blog-post">';
			html += '<ul>';
			var post = window.blogs[blogName].posts[i];
			for(var j = 0; j < post.photos.length; j++) {
				html += '<li>';
				html += '<img src="' + post.photos[j] + '" style="width: 450px"/>';
				html += '</li>';
			}
			html += '</ul>';
			html += '<div class="caption">';
			html += post.caption;
			html += '</div>';
			html += '</section>';
		}
		html += '</article>';

		$('#main').html(html);

		// force width
		$('#main article').width($('#main').width());

		// Run masonry
		var $container = $('.blog-posts');
		$container.imagesLoaded(function(){
			$container.masonry({
				itemSelector : '.blog-post',
				columnWidth : 500
			});
		});

		// Save blog name
		window.selectedBlog = blogName;
	});

	$(window).resize(function() {
		// force width
		$('#main article').width($('#main').width());

		// Run masonry
		var $container = $('.blog-posts');
		$container.imagesLoaded(function(){
			$container.masonry({
				itemSelector : '.blog-post',
				columnWidth : 500,
				isFitWidth: true,
				containerStyle: {position: 'absolute'},
			});
		});
	});

	// show hide icons
	$(document).mousemove(function(e) {
		window.barVisible = window.barVisible == undefined  ? true : window.barVisible;
		window.barAnimating = window.barAnimating == undefined  ?  false : window.barAnimating;
		var y = e.pageY;
		y += $('html').offset().top;
		if (window.barVisible && y > 90 && !window.barAnimating) {
			window.barVisible = false;
			window.barAnimating = true;
			$('.flexslider').animate({'margin-top': '-=65'}, 250, function() {window.barAnimating = false});
			// show selected blog
			if (window.selectedBlog != '') {
				showBlogInfo(window.selectedBlog);
			}
		}
		else if (window.barVisible == false && y < 20 && window.barAnimating == false) {
			window.barVisible = true;
			window.barAnimating = true;
			$('.flexslider').animate({'margin-top': '+=65'}, 250, function() {window.barAnimating = false});
		}
	});

	// helpers
	function showBlogInfo(blogName) {
		var info = window.blogs[blogName].info;
		var title = '';
		if (info.title) {
			title = info.title;
		}
		else {
			title = blogName + '.tumblr.com';
		}

		// truncate title
		if (title.length > 30) {
			title = title.substr(0, 30) + '...';
		}
		var html = '<img src="' + window.blogs[blogName].avatar + '" />' + '<h2>' + title + '</h2>';
		html += '<p><a href="' + info.url + '" target="_blank">' + info.url + '</a> - <a href="' + info.url + 'archive" target="_blank">archive</a></p>';
		$('#blog-info').html(html);
		$('#blog-info').show();
	}
});
