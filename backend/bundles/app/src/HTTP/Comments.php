<?php

namespace Project\App\HTTP;

use PHPixie\HTTP\Request;

class Comments extends Processor
{
    public function defaultAction($request)
    {
        $method = strtoupper($request->method());
        if ($method === 'GET') {
            $postId = $this->param($request, 'post_id');
            $id = $request->attributes()->get('id');
            if ($id === null) {
                $id = $this->param($request, 'id');
            }

            if ($postId !== null) {
                return $this->list_by_postAction($request);
            }
            if ($id !== null) {
                return $this->showAction($request);
            }
            return $this->responses()->json(array('error' => 'missing_filter'));
        }
        if ($method === 'POST') {
            return $this->createAction($request);
        }
        if ($method === 'PUT') {
            $id = $request->attributes()->get('id');
            if ($id === null) {
                $id = $this->param($request, 'id');
            }
            if ($id === null) {
                return $this->responses()->json(array('error' => 'missing_id'));
            }
            return $this->updateAction($request);
        }
        if ($method === 'DELETE') {
            $id = $request->attributes()->get('id');
            if ($id === null) {
                $id = $this->param($request, 'id');
            }
            if ($id === null) {
                return $this->responses()->json(array('error' => 'missing_id'));
            }
            return $this->deleteAction($request);
        }
        return $this->responses()->json(array('error' => 'method_not_allowed'));
    }

    public function list_by_postAction($request)
    {
        $postId = $request->attributes()->get('id');
        if ($postId === null) {
            $postId = $this->param($request, 'post_id');
        }
        $orm = $this->components()->orm();
        $comments = $orm->query('comment')
            ->where('post_id', $postId)
            ->find()
            ->asArray(true);

        return $this->responses()->json(array(
            'post_id'  => $postId,
            'comments' => $comments
        ));
    }

    public function showAction($request)
    {
        $id = $request->attributes()->get('id');
        if ($id === null) {
            $id = $this->param($request, 'id');
        }
        $orm = $this->components()->orm();
        $comment = $orm->query('comment')->in($id)->findOne();

        return $this->responses()->json(array(
            'found'   => $comment !== null,
            'comment' => $comment ? $comment->asObject() : null
        ));
    }

    public function createAction($request)
    {
        $orm = $this->components()->orm();
        $comment = $orm->createEntity('comment');
        $comment->id = $this->uuid();
        $comment->post_id = $this->param($request, 'post_id');
        $comment->user_id = $this->param($request, 'user_id');
        $comment->content = $this->param($request, 'content');
        $comment->save();

        return $this->responses()->json(array(
            'created'  => true,
            'comment'  => $comment->asObject()
        ));
    }

    public function updateAction($request)
    {
        $id = $request->attributes()->get('id');
        if ($id === null) {
            $id = $this->param($request, 'id');
        }
        $orm = $this->components()->orm();
        $comment = $orm->query('comment')->in($id)->findOne();

        if (!$comment) {
            return $this->responses()->json(array(
                'updated' => false,
                'id'      => $id,
                'error'   => 'not_found'
            ));
        }

        $postId = $this->param($request, 'post_id');
        $userId = $this->param($request, 'user_id');
        $content = $this->param($request, 'content');

        if ($postId !== null) {
            $comment->post_id = $postId;
        }
        if ($userId !== null) {
            $comment->user_id = $userId;
        }
        if ($content !== null) {
            $comment->content = $content;
        }

        $comment->save();

        return $this->responses()->json(array(
            'updated' => true,
            'id'      => $id,
            'comment' => $comment->asObject()
        ));
    }

    public function deleteAction($request)
    {
        $id = $request->attributes()->get('id');
        if ($id === null) {
            $id = $this->param($request, 'id');
        }
        $orm = $this->components()->orm();
        $comment = $orm->query('comment')->in($id)->findOne();

        if (!$comment) {
            return $this->responses()->json(array(
                'deleted' => false,
                'id'      => $id,
                'error'   => 'not_found'
            ));
        }

        $comment->delete();

        return $this->responses()->json(array(
            'deleted' => true,
            'id'      => $id
        ));
    }
}