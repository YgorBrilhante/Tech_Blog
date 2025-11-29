<?php

namespace Project\App\HTTP;

use PHPixie\HTTP\Request;

class Posts extends Processor
{
    public function defaultAction($request)
    {
        $method = strtoupper($request->method());
        $id = $request->attributes()->get('id');
        if ($id === null) {
            $id = $this->param($request, 'id');
        }

        if ($method === 'GET') {
            return $id ? $this->showAction($request) : $this->listAction($request);
        }
        if ($method === 'POST') {
            return $this->createAction($request);
        }
        if ($method === 'PUT') {
            if ($id === null) {
                return $this->responses()->json(array('error' => 'missing_id'));
            }
            return $this->updateAction($request);
        }
        if ($method === 'DELETE') {
            if ($id === null) {
                return $this->responses()->json(array('error' => 'missing_id'));
            }
            return $this->deleteAction($request);
        }

        return $this->responses()->json(array('error' => 'method_not_allowed'));
    }

    public function listAction($request)
    {
        $orm = $this->components()->orm();
        $posts = $orm->query('post')->find()->asArray(true);

        return $this->responses()->json(array(
            'posts' => $posts
        ));
    }

    public function showAction($request)
    {
        $id = $request->attributes()->get('id');
        $orm = $this->components()->orm();
        $post = $orm->query('post')->in($id)->findOne();

        return $this->responses()->json(array(
            'found' => $post !== null,
            'post'  => $post ? $post->asObject() : null
        ));
    }

    public function list_by_userAction($request)
    {
        $userId = $request->attributes()->get('id');
        $orm = $this->components()->orm();
        $posts = $orm->query('post')
            ->where('user_id', $userId)
            ->find()
            ->asArray(true);

        return $this->responses()->json(array(
            'user_id' => $userId,
            'posts'   => $posts
        ));
    }

    public function createAction($request)
    {
        $orm = $this->components()->orm();
        $post = $orm->createEntity('post');
        $post->id = $this->uuid();
        $post->titulo = $this->param($request, 'titulo');
        $post->conteudo = $this->param($request, 'conteudo');
        $post->user_id = $this->param($request, 'user_id');

        $post->save();

        return $this->responses()->json(array(
            'created' => true,
            'post'    => $post->asObject()
        ));
    }

    public function updateAction($request)
    {
        $id = $request->attributes()->get('id');
        if ($id === null) {
            $id = $this->param($request, 'id');
        }
        $orm = $this->components()->orm();
        $post = $orm->query('post')->in($id)->findOne();

        if (!$post) {
            return $this->responses()->json(array(
                'updated' => false,
                'id'      => $id,
                'error'   => 'not_found'
            ));
        }

        $titulo = $this->param($request, 'titulo');
        $conteudo = $this->param($request, 'conteudo');

        if ($titulo !== null) {
            $post->titulo = $titulo;
        }
        if ($conteudo !== null) {
            $post->conteudo = $conteudo;
        }

        $post->save();

        return $this->responses()->json(array(
            'updated' => true,
            'id'      => $id,
            'post'    => $post->asObject()
        ));
    }

    public function deleteAction($request)
    {
        $id = $request->attributes()->get('id');
        if ($id === null) {
            $id = $this->param($request, 'id');
        }
        $orm = $this->components()->orm();
        $post = $orm->query('post')->in($id)->findOne();

        if (!$post) {
            return $this->responses()->json(array(
                'deleted' => false,
                'id'      => $id,
                'error'   => 'not_found'
            ));
        }

        $post->delete();

        return $this->responses()->json(array(
            'deleted' => true,
            'id'      => $id
        ));
    }
}