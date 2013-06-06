<?php

//
// Retrieves liked posts (photos) for the blog provided and saves
// images in PNG format.
// 


// This are global so that all functions can use them to pull info from
// Tumblr.
$api_key    = '33j1VK6U6qgjzHyjkZvpbfS3ECy4R4bWgUrK20RfnYqLu7Hnhu';
$blog       = 'hypro.tumblr.com';

// Get number of likes in this blog
$url = sprintf('http://api.tumblr.com/v2/blog/%s/info?api_key=%s',
    $blog,
    $api_key);

$result    = json_decode(file_get_contents($url));
$num_likes = $result->response->blog->likes;

$all_posts = array();
for($offset = 0; $offset < $num_likes; $offset += 20) {

    // Create API url
    $url = sprintf('http://api.tumblr.com/v2/blog/%s/likes?api_key=%s&limit=20&offset=%d',
        $blog,
        $api_key,
        $offset);

    echo "url: $url\n";

    // Call API and retrieve posts in JSON
    $content = file_get_contents($url);
    $result  = json_decode($content);

    save_posts($result->response->liked_posts);

    $all_posts = array_merge($all_posts, $result->response->liked_posts);
}

// Save all info a .json file
file_put_contents('posts.json', json_encode($all_posts));

// Generate index.html
//generate_index($all_posts);

generate_webapp($all_posts);

exit(0);


function save_posts($posts) {

    // Loop through results
    foreach($posts as $post) {

        // Only download photo posts
        if ($post->type != 'photo') {
            continue;
        }

        // Save avatar
        $blog_path   = "blogs/$post->blog_name";
        $avatar_path = "$blog_path/avatar.png";
        if (!file_exists($avatar_path)) {
            if (!file_exists($blog_path)) {
                mkdir($blog_path, 0777, TRUE);
            }
            copy("http://api.tumblr.com/v2/blog/$post->blog_name.tumblr.com/avatar", $avatar_path);    
        }

        // Check if we already downloaded this post and download
        // if we haven't.
        $path = create_post_path($post);
        if (!file_exists($path)) {
            echo "Processing $path...\n";
            mkdir($path, 0777, TRUE);
            $count = 1;
            // caption and comment to add for all photos
            $comment = get_comment($post);
            $caption = get_caption($post);
            foreach($post->photos as $photo) {
                $filepath = sprintf('%s/photo%03d', $path, $count++);
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
    }
}

function generate_index($posts) {

    $view_data = array(
        'title' => 'This is my blog',
        'blogs' => array(),
    );

    // Group results by blog
    foreach($posts as $post) {

        if ($post->type != 'photo') {
            continue;
        }

        if (!isset($view_data['blogs'][$post->blog_name])) {
            $view_data['blogs'][$post->blog_name] = array();
        }

        $view_data['blogs'][$post->blog_name][] = $post;
    }

    // Send blogs to template (index.tpl.php)
    $view = (object) $view_data;
    ob_start();
    include 'index.tpl.php';
    $result = ob_get_contents();
    ob_end_clean();
    
    // write index.html from parsed template
    file_put_contents('index-flat.html', $result);
}

// This generates a webapp to view posts
function generate_webapp($posts) {
    global $api_key;

    $view_data = array(
        'blogs' => array(),
    );

    // Group results by blog
    foreach($posts as $post) {

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

    // Send blogs to template (index.tpl.php)
    $view = (object) $view_data;
    ob_start();
    include 'index-webapp.tpl.php';
    $result = ob_get_contents();
    ob_end_clean();
    
    // write index.html from parsed template
    file_put_contents('index-webapp.html', $result);
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