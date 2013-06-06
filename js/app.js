$(window).load(function() {

	$('.flexslider').flexslider({
	    animation: "slide",
	    animationLoop: false,
	    slideshow: false,
	    controlNav: false,
	    mouseWheel: false,
	    itemWidth: 60,
	    itemMargin: 5
	});

	$('.blog-icon').hover(function() {
		var blogName = $(this).data('blog');
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
		html += '<p><a href="' + info.url + '" target="_blank">' + info.url + '</a></p>';
		$('#blog-info').html(html);
		$('#blog-info').show();
	});

	$('.blog-icon').click(function() {
		var blogName = $(this).data('blog');
		var info = window.blogs[blogName].info;

		var html = '<article>';
		for(var i = 0; i < window.blogs[blogName].posts.length; i++) {
			html += '<section>';
			html += '<ul>';
			var post = window.blogs[blogName].posts[i];
			for(var j = 0; j < post.photos.length; j++) {
				html += '<li>';
				html += '<img src="' + post.photos[j] + '" style="width: 250px"/>';
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
	});

	$(window).resize(function() {
		// force width
		$('#main article').width($('#main').width());
	});

});