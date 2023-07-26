<?php

namespace App;

use App\Models\Post;

class BlogController{

    private $currentPath;

    public function __construct(){

        $this->currentPath = currentUrl();

    }

    public function handle(){
      $posts = Post::all();

        /**
         * check if not post slug given than return all posts
         */
        if ( !$this->isPostPath() ){
            // $posts = Post::all();
            return include("layouts/blog.php");
        }

        /**
         * Check if post slug is given find post by slug
         */
        $post = Post::findPostBySlug( $this->getPostSlug() );

        /**
         * If no post found retur post not found error
         */
        if ( !$post ){
            include ("layouts/noPostFound.php");
            return;
        }

        include("layouts/post.php");

    }

    public function isPostPath(){
        return isset($_GET['name']) && $this->getPostSlug() ? true: false;
    }

    public function getPostSlug(){
        return isset($_GET['name']) && $_GET['name']!=null ? $_GET['name'] : null;
    }


}
