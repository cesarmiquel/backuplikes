<?php

//
// Retrieves liked posts (photos) for the blog provided and saves
// images in PNG format.
// 


// This are global so that all functions can use them to pull info from
// Tumblr.
$api_key    = '<your api key here>';
$blog       = '<your blog here>.tumblr.com';

if (count($argv) != 2) {
    print "Usage: $argv[0] <dest-dir>\n\n";
    exit(0);
}

$dest_dir = $argv[1];

// Get number of likes in this blog
$url = sprintf('http://api.tumblr.com/v2/blog/%s/info?api_key=%s',
    $blog,
    $api_key);

$result    = json_decode(file_get_contents($url));

// There seems to be an upper limit on thenumber of likes available to the API: 1000. :-(
// https://groups.google.com/forum/#!searchin/tumblr-api/likes|sort:relevance/tumblr-api/exUSE_ySM1g/om4zWgRlnsEJ
$num_likes = max($result->response->blog->likes, 1000);

$page_count = 20;
for($offset = 0; $offset < $num_likes; $offset += $page_count) {

    // Create API url
    $url = sprintf('http://api.tumblr.com/v2/blog/%s/likes?api_key=%s&limit=' . $page_count . '&offset=%d',
        $blog,
        $api_key,
        $offset);

    echo "url: $url\n";

    // Call API and retrieve posts in JSON
    $content = file_get_contents($url);
    $result  = json_decode($content);

    save_posts($result->response->liked_posts);
}

generate_webapp($dest_dir);

exit(0);


function save_posts($posts) {

    global $dest_dir;

    // Loop through results
    foreach($posts as $post) {

        // Only download photo posts
        if ($post->type != 'photo') {
            continue;
        }

        // Save avatar
        $blog_path   = "$dest_dir/blogs/$post->blog_name";
        $avatar_path = "$blog_path/avatar.png";
        if (!file_exists($avatar_path)) {
            if (!file_exists($blog_path)) {
                mkdir($blog_path, 0777, TRUE);
            }
            copy("http://api.tumblr.com/v2/blog/$post->blog_name.tumblr.com/avatar", $avatar_path);    
        }

        // Path were this post will be stored
        $path = create_post_path($post);

        // Download and save photos if we haven't done so
        if (!file_exists($dest_dir . '/' . $path)) {
            echo "Processing $path...\n";

            mkdir($dest_dir . '/' . $path, 0777, TRUE);
            $count = 1;
            // caption and comment to add for all photos
            $comment = get_comment($post);
            $caption = get_caption($post);
            foreach($post->photos as $photo) {
                $filepath = sprintf('%s/%s/photo%03d', $dest_dir, $path, $count++);
                copy($photo->original_size->url, $filepath);

                // if GIF just move otherwise convert to PNG
                $output = exec("/usr/bin/identify $filepath");
                if (stripos($output, 'GIF') !== false) {
                    rename($filepath, $filepath . '.gif');
                } else {
                    $png = $filepath . '.png';
                    exec("convert $filepath $png");
                    unlink($filepath);

                    // add caption
                    $cmd = "mogrify -comment \"$comment\" -caption \"$caption\" $png";
                    exec($cmd);
                }
            }
        }
        else {
            echo "Skipping $path. Exists.\n";
        }

        // Save post json if not already saved
        if (!file_exists($dest_dir . '/' . $path . '/post.json')) {
            // save post json
            file_put_contents($dest_dir . '/' . $path . '/post.json', json_encode($post));            
        }
    }
}

// This generates a webapp to view posts
function generate_webapp($posts) {
    global $api_key;
    global $dest_dir;

    $view_data = array(
        'blogs' => array(),
    );

    // Group results by blog
    //foreach($posts as $post) {
    $blogs = my_scan_dir($dest_dir . '/blogs');
    foreach($blogs as $blog_name) {

        if ($blog_name == '.' || $blog_name == '..')
            continue;

        $posts_dirs = my_scan_dir($dest_dir . '/blogs/' . $blog_name);
        foreach($posts_dirs as $post_dir) {

            if ($post_dir == '.' || $post_dir == '..')
                continue;

            $post = json_decode(file_get_contents($dest_dir . '/blogs/' . $blog_name . '/' . $post_dir . '/post.json'));

            if ($post->type != 'photo') {
                continue;
            }

            if (!isset($view_data['blogs'][$post->blog_name])) {
                $view_data['blogs'][$post->blog_name] = (object) array('info' => '', 'posts' => array());

                $url = sprintf('http://api.tumblr.com/v2/blog/%s.tumblr.com/info?api_key=%s',
                    $post->blog_name,
                    $api_key);

                $result    = json_decode(file_get_contents($url));
                $view_data['blogs'][$post->blog_name]->info = $result->response->blog;
                $view_data['blogs'][$post->blog_name]->avatar = 'blogs/' . $post->blog_name . '/avatar.png';
            }

            // get path to photos
            $photos = array();
            foreach($post->photos as $i => $photo) {
                $filepath = sprintf('%s/photo%03d.png', create_post_path($post), $i + 1);
                $photos[] = $filepath;
            }

            $view_data['blogs'][$post->blog_name]->posts[] = (object) array(
                'photos' => $photos,
                'caption' => $post->caption,
            );
        }
    }

    // Send blogs to template (index.tpl.php)
    $view = (object) $view_data;
    ob_start();
    include 'index-webapp.tpl.php';
    $result = ob_get_contents();
    ob_end_clean();
    
    // write index.html from parsed template
    file_put_contents($dest_dir . '/index-webapp.html', $result);
}

// ------------------------------------------------------------------------
// Helpers
// ------------------------------------------------------------------------

function get_comment($post) {

    $vars = array(
        'blog_name', 'link_url', 'post_url',
        'source_url', 'source_title', 'image_permalink'
    );
    $comment = array();
    foreach($vars as $var) {
        if (isset($post->{$var})) {
            $comment[] = $var . ': ' . str_replace("\"", "'", $post->{$var});
        }
    }
    if (isset($post->tags)) {
        $comment[] = 'tags: ' . implode(',', $post->tags);
    }

    return implode("\n", $comment);
}

function get_caption($post) {

    $caption = isset($post->caption) ? $post->caption : '';
    return str_replace("\"", "'", $caption);
}

function create_post_path($post) {

    if (strlen(trim($post->slug)) > 0) {
       $slug = '-' . trim($post->slug);
    }
    else {
        $slug = '';
    }
    return 'blogs/' . $post->blog_name . '/' . $post->id . $slug;
}

// Scan directories and filter files and unwanted directories (. and ..)
function my_scan_dir($source_dir) {

    $dirs = scandir($source_dir);

    $result = array();
    foreach($dirs as $dir) {
        // skip . and ..
        if ($dir == '.' || $dir == '.') {
            continue;
        }

        // skip any files
        if (!is_dir($source_dir . '/' . $dir)) {
            continue;
        }

        $result[] = $dir;
    }
    return($result);
}
