<?php

namespace Project\App\HTTP;

use PHPixie\HTTP\Request;

class Tags extends Processor
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
        $tags = $orm->query('tag')->find()->asArray(true);

        return $this->responses()->json(array(
            'tags' => $tags
        ));
    }

    public function showAction($request)
    {
        $id = $request->attributes()->get('id');
        $orm = $this->components()->orm();
        $tag = $orm->query('tag')->in($id)->findOne();

        return $this->responses()->json(array(
            'found' => $tag !== null,
            'tag'   => $tag ? $tag->asObject() : null
        ));
    }

    public function createAction($request)
    {
        $orm = $this->components()->orm();
        $tag = $orm->createEntity('tag');
        $tag->id = $this->uuid();
        $tag->name = $this->param($request, 'name');
        $tag->save();

        return $this->responses()->json(array(
            'created' => true,
            'tag'     => $tag->asObject()
        ));
    }

    public function updateAction($request)
    {
        $id = $request->attributes()->get('id');
        if ($id === null) {
            $id = $this->param($request, 'id');
        }
        $orm = $this->components()->orm();
        $tag = $orm->query('tag')->in($id)->findOne();

        if (!$tag) {
            return $this->responses()->json(array(
                'updated' => false,
                'id'      => $id,
                'error'   => 'not_found'
            ));
        }

        $name = $this->param($request, 'name');
        if ($name !== null) {
            $tag->name = $name;
        }

        $tag->save();

        return $this->responses()->json(array(
            'updated' => true,
            'id'      => $id,
            'tag'     => $tag->asObject()
        ));
    }

    public function deleteAction($request)
    {
        $id = $request->attributes()->get('id');
        if ($id === null) {
            $id = $this->param($request, 'id');
        }
        $orm = $this->components()->orm();
        $tag = $orm->query('tag')->in($id)->findOne();

        if (!$tag) {
            return $this->responses()->json(array(
                'deleted' => false,
                'id'      => $id,
                'error'   => 'not_found'
            ));
        }

        $tag->delete();

        return $this->responses()->json(array(
            'deleted' => true,
            'id'      => $id
        ));
    }
}