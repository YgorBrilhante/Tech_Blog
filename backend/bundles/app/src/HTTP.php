<?php

namespace Project\App;

class HTTP extends \PHPixie\DefaultBundle\HTTP
{
    protected $classMap = array(
        'greet' => 'Project\App\HTTP\Greet',
        'hello' => 'Project\App\HTTP\Hello',
        'users' => 'Project\App\HTTP\Users',
        'posts' => 'Project\App\HTTP\Posts',
        'tags' => 'Project\App\HTTP\Tags',
        'post_tags' => 'Project\App\HTTP\PostTags',
        'comments' => 'Project\App\HTTP\Comments'
    );
}