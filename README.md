Backup your Tumblr's liked photo posts
=====================================

I love using Tumblr to collect images for inspiration using the like 
feature. I created this simple PHP code to download my liked images while
saving all the metadata information. It will aditionally create an index.html
file for you to quickly view your images locally without connecting to 
the internet.

Install
-------

To be able to use this scripts you need PHP and the Imagemagick library (you don't
the PHP bindings since I use system() to execute the utilities). I haven't tried
this on anything other than Ubuntu Linux. To install Imagemagick just do:

       $ sudo apt-get install imagemagick

You also need to get your OAUTH API key. This is looks complicated but its quite simple. 
Go here: http://www.tumblr.com/oauth/register and complete the fields. Since we are not
going to log in most of the fields you can put random junk.

To create your backup:

 1. Edit backup\_liked.php and change your blogs URL and your API key.
 2. Call the script with: <code>php backup\_liked.php</code>


This will retrieve your posts in the directory blogs/. At the end it will store an index.html
file and a posts.json file with all the metadata of your posts. You can use your browser
to view the index.html and your images. If you have a lot of images this can consume a lot
of memory since you are basically opening all of them :-). I will probably have to
do something about this ... for now it works.
