<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationList;

abstract class ApiController extends AbstractController
{
    /**
     * Provides a request payload
     * @param Request $request
     *
     * @return array
     */
    protected function getRequestPayload(Request $request): array
    {
        if ('json' !== $request->getContentTypeFormat()) {
            return [];
        }

        return $request->toArray();
    }

    /**
     * Returns error response with error code and message
     *
     * @param string $message
     * @param array $errors
     * @param int $statusCode
     * @return Response
     */
    protected function errorResponse(
        string $message = 'Error',
        array $errors = [],
        int $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR
    ): Response
    {
        return $this->json([
            'message' => $message,
            'errors' => $errors,
        ], $statusCode);
    }

    /**
     * Returns success response with success code and message
     *
     * @param string $message
     * @param array $data
     * @param int $statusCode
     * @return Response
     */
    protected function successResponse(
        string $message = 'OK',
        array $data = [],
        int $statusCode = Response::HTTP_OK
    ): Response
    {
        return $this->json([
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    /**
     * @param ConstraintViolationList $violations
     * @return Response
     */
    protected function checkViolations(ConstraintViolationList $violations): Response
    {
        $errors = [];
        foreach ($violations as $violation) {
            $errors[] = $violation->getPropertyPath() . ': ' . $violation->getMessage();
        }

        return $this->errorResponse(
            message: 'Bad request',
            errors: $errors,
            statusCode: Response::HTTP_BAD_REQUEST
        );
    }
}