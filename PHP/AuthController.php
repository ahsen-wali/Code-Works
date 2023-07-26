<?php 

namespace App;

use App\Models\Post;

class AuthController{


    private $currentPath;

    public function __construct(){

        $this->currentPath = currentUrl();

    }


    public function handle(){
        
        if ( !$this->checkAuth() ){
            return include("layouts/login.php");
        }

        /**
         * check if not post slug given than return all posts
         */
        if ( $this->isCreatePostPath() ){
            return $this->handleCreatePostRoute();
        }

        if ( $this->isListPostPath() ){
            return $this->handleListPostRoute();
        }

        if ( $this->isEditPostPath() ){
            return $this->handleEditPostRoute();
        }

        if ( $this->isDeletePostPath() ){
            return $this->handleDeletePostRoute();
        }
        
        $posts = Post::all();
        return include("layouts/blog.php");

    }

    /**
     * Handle routes functions
     */
    function handleListPostRoute(){
        /**
         * Check if post slug is given find post by slug
         */
        $posts = Post::all();

        return include("layouts/list-posts.php");
    }

    function handleCreatePostRoute(){
        /**
         * Check if post slug is given find post by slug
         */
        return include("layouts/create_post.php");
    }

    function handleEditPostRoute(){
        /**
         * Check if post slug is given find post by slug
         */
        $post = Post::find( $this->getPostId() );

        // print_r($post);
        // exit;
        /**
         * If no post found retur post not found error
         */
        if ( !$post ){
            include ("layouts/noPostFound.php");
            return;
        }

        return include("layouts/edit-post.php");
    }

    function handleDeletePostRoute(){

    }

    /**
     * Private functions
     */

    //  Check if path is create post
    public function isCreatePostPath(){
        return isset($_GET['create']) ? true: false;
    }

    // Check if path is list post
    function isListPostPath(){
        return isset($_GET['list']) || "/admin.php" == $_SERVER['REQUEST_URI'] ? true: false;
    }

    function isEditPostPath(){
        return isset($_GET['edit']) ? true: false;
    }

    function isDeletePostPath(){
        return isset($_GET['delete']) ? true: false;
    }

    public function getPostSlug(){
        return isset($_GET['name']) && $_GET['name']!=null ? $_GET['name'] : null;
    }

    public function getPostId(){
        return isset($_GET['edit']) && $_GET['edit']!=null ? $_GET['edit'] : null;
    }

    public function checkAuth(){
        return Session::has('user');
    }
}