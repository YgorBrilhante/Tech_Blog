<?php 

namespace Project\App\HTTP;

use PHPixie\HTTP\Request;

class Users extends Processor
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
                return $this->responses()->json(array(
                    'error' => 'missing_id'
                ));
            }
            return $this->updateAction($request);
        }
        if ($method === 'DELETE') {
            if ($id === null) {
                return $this->responses()->json(array(
                    'error' => 'missing_id'
                ));
            }
            return $this->deleteAction($request);
        }

        return $this->responses()->json(array(
            'error' => 'method_not_allowed'
        ));
    }

    public function listAction($request)
    {
        $orm = $this->components()->orm();
        $users = $orm->query('user')->find()->asArray(true);

        return $this->responses()->json(array(
            'users' => $users
        ));
    }

    public function showAction($request)
    {
        $id = $request->attributes()->get('id');
        $orm = $this->components()->orm();
        $user = $orm->query('user')->in($id)->findOne();

        return $this->responses()->json(array(
            'found' => $user !== null,
            'user'  => $user ? $user->asObject() : null
        ));
    }

    public function createAction($request)
    {
        $orm = $this->components()->orm();
        $name = $this->param($request, 'name');
        $email = $this->param($request, 'email');
        $password = $this->param($request, 'password');

        if ($name === null || $email === null || $password === null) {
            return $this->responses()->json(array(
                'created'  => false,
                'error'    => 'missing_params',
                'required' => array('name', 'email', 'password')
            ));
        }

        try {
            $user = $orm->createEntity('user');
            $user->id = $this->uuid();
            $user->name = $name;
            $user->email = $email;
            $user->password = $password;
            $user->save();

            return $this->responses()->json(array(
                'created' => true,
                'user'    => $user->asObject()
            ));
        } catch (\Exception $e) {
            return $this->responses()->json(array(
                'created' => false,
                'error'   => 'server_error',
                'message' => $e->getMessage()
            ));
        }
    }

    public function updateAction($request)
    {
        $id = $request->attributes()->get('id');
        if ($id === null) {
            $id = $this->param($request, 'id');
        }
        $orm = $this->components()->orm();
        $user = $orm->query('user')->in($id)->findOne();

        if (!$user) {
            return $this->responses()->json(array(
                'updated' => false,
                'id'      => $id,
                'error'   => 'not_found'
            ));
        }

        $name = $this->param($request, 'name');
        $email = $this->param($request, 'email');
        $password = $this->param($request, 'password');

        if ($name !== null) {
            $user->name = $name;
        }
        if ($email !== null) {
            $user->email = $email;
        }
        if ($password !== null) {
            $user->password = $password;
        }

        $user->save();

        return $this->responses()->json(array(
            'updated' => true,
            'id'      => $id,
            'user'    => $user->asObject()
        ));
    }

    public function deleteAction($request)
    {
        $id = $request->attributes()->get('id');
        if ($id === null) {
            $id = $this->param($request, 'id');
        }
        $orm = $this->components()->orm();
        $user = $orm->query('user')->in($id)->findOne();

        if (!$user) {
            return $this->responses()->json(array(
                'deleted' => false,
                'id'      => $id,
                'error'   => 'not_found'
            ));
        }

        $user->delete();

        return $this->responses()->json(array(
            'deleted' => true,
            'id'      => $id
        ));
    }
}