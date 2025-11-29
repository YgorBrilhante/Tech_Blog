<?php

namespace Project\App\HTTP;

use PHPixie\HTTP\Request;

class Hello extends Processor
{
    /**
     * POST /hello/post -> {"message":"hello world"}
     *
     * @param Request $request
     * @return \PHPixie\HTTP\Responses\Response
     */
    public function postAction($request)
    {
        return $this->responses()->json(array(
            'message' => 'hello world'
        ));
    }
}