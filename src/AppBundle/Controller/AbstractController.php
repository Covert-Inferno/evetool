<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationList;

abstract class AbstractController extends Controller {

    protected function jsonResponse($data, $status = 200, $headers = []){
        return new Response($data, $status, array_merge([
            'Content-Type' => 'application/json'
        ],$headers));

    }

    protected function getErrorResponse(ConstraintViolationList $errors){
        return $this->jsonResponse(
            $this->get('serializer')->serialize($errors, 'json'),
            400);
    }
}