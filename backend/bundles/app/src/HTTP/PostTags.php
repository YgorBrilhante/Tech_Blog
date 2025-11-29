<?php

namespace Project\App\HTTP;

use PHPixie\HTTP\Request;

class PostTags extends Processor
{
    public function defaultAction($request)
    {
        $method = strtoupper($request->method());
        if ($method === 'GET') {
            $postId = $this->param($request, 'post_id');
            $tagId  = $this->param($request, 'tag_id');
            if ($postId !== null) {
                return $this->list_by_postAction($request);
            }
            if ($tagId !== null) {
                return $this->list_by_tagAction($request);
            }
            return $this->responses()->json(array('error' => 'missing_filter'));
        }
        if ($method === 'POST') {
            return $this->addAction($request);
        }
        if ($method === 'DELETE') {
            return $this->removeAction($request);
        }

        return $this->responses()->json(array('error' => 'method_not_allowed'));
    }

    public function addAction($request)
    {
        $orm = $this->components()->orm();
        $postId = $this->param($request, 'post_id');
        $tagId  = $this->param($request, 'tag_id');

        $post = $orm->query('post')->in($postId)->findOne();
        $tag  = $orm->query('tag')->in($tagId)->findOne();

        if (!$post || !$tag) {
            return $this->responses()->json(array(
                'created'  => false,
                'error'    => 'not_found',
                'post_id'  => $postId,
                'tag_id'   => $tagId
            ));
        }

        $post->tags->add($tag);

        return $this->responses()->json(array(
            'created'   => true,
            'post_id'   => $postId,
            'tag_id'    => $tagId
        ));
    }

    public function removeAction($request)
    {
        $orm = $this->components()->orm();
        $postId = $this->param($request, 'post_id');
        $tagId  = $this->param($request, 'tag_id');

        $post = $orm->query('post')->in($postId)->findOne();
        $tag  = $orm->query('tag')->in($tagId)->findOne();

        if (!$post || !$tag) {
            return $this->responses()->json(array(
                'deleted'  => false,
                'error'    => 'not_found',
                'post_id'  => $postId,
                'tag_id'   => $tagId
            ));
        }

        $post->tags->remove($tag);

        return $this->responses()->json(array(
            'deleted'   => true,
            'post_id'   => $postId,
            'tag_id'    => $tagId
        ));
    }

    public function list_by_postAction($request)
    {
        $postId = $request->attributes()->get('id');
        if ($postId === null) {
            $postId = $this->param($request, 'post_id');
        }
        $orm = $this->components()->orm();
        $post = $orm->query('post')->in($postId)->findOne();

        if (!$post) {
            return $this->responses()->json(array(
                'post_id' => $postId,
                'found'   => false,
                'tags'    => array()
            ));
        }

        $tags = $orm->query('tag')->relatedTo('posts', $post)->find()->asArray(true);

        return $this->responses()->json(array(
            'post_id' => $postId,
            'found'   => true,
            'tags'    => $tags
        ));
    }

    public function list_by_tagAction($request)
    {
        $tagId = $request->attributes()->get('id');
        if ($tagId === null) {
            $tagId = $this->param($request, 'tag_id');
        }
        $orm = $this->components()->orm();
        $tag = $orm->query('tag')->in($tagId)->findOne();

        if (!$tag) {
            return $this->responses()->json(array(
                'tag_id' => $tagId,
                'found'  => false,
                'posts'  => array()
            ));
        }

        $posts = $orm->query('post')->relatedTo('tags', $tag)->find()->asArray(true);

        return $this->responses()->json(array(
            'tag_id' => $tagId,
            'found'  => true,
            'posts'  => $posts
        ));
    }
}