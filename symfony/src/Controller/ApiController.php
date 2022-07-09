<?php
namespace  App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApiController extends AbstractController
{
    /**
     * @var int HTTP status code 200 - OK (by default)
     */
    protected $statusCode = 200;

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @param int $statusCode
     */
    public function setStatusCode(int $statusCode): void
    {
        $this->statusCode = $statusCode;
    }

    /**
     * @param $data
     * @param array $headers
     * @return JsonResponse
     */
    public function response($data, $headers = []): JsonResponse
    {
        return new JsonResponse($data, $this->getStatusCode(), $headers);
    }

    /**
     * @param $errors
     * @param array $headers
     * @return JsonResponse
     */
    public function respondWithErrors($errors, $headers = []): JsonResponse
    {
        $data = [
            'status' => $this->getStatusCode(),
            'errors' => $errors,
        ];
        return new JsonResponse($data, $this->getStatusCode(), $headers);
    }

    /**
     * @param $success
     * @param array $headers
     * @return JsonResponse
     */
    public function respondWithSuccess($success, $headers = []): JsonResponse
    {
        $data = [
            'status' => $this->getStatusCode(),
            'success' => $success,
        ];
        return new JsonResponse($data, $this->getStatusCode(), $headers);
    }

    /**
     * @param string $message
     * @return JsonResponse
     */
    public function respondUnauthorized($message = 'Not authorized!'): JsonResponse
    {
        $this->setStatusCode(401);
        return $this->respondWithErrors($message);
    }

    /**
     * @param string $message
     * @return JsonResponse
     */
    public function respondValidationError($message = 'Validation error!'): JsonResponse
    {
        $this->setStatusCode(422);
        return $this->respondWithErrors($message);
    }

    /**
     * @param string $message
     * @return JsonResponse
     */
    public function respondNotFound($message = 'Not found!'): JsonResponse
    {
        $this->setStatusCode(404);
        return $this->respondWithErrors($message);
    }

    /**
     * @param array $data
     * @return JsonResponse
     */
    public function respondCreated($data = []): JsonResponse
    {
        $this->setStatusCode(201);
        return $this->response($data);
    }

    /**
     * @param Request $request
     * @return Request
     */
    protected function transformJsonBody(Request $request): Request
    {
        $data = json_decode($request->getContent(), true);

        if ($data === null) {
            return $request;
        }

        $request->request->replace($data);

        return $request;
    }


}