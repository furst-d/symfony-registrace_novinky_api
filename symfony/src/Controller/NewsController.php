<?php

namespace App\Controller;

use App\Entity\News;
use App\Repository\NewsRepository;
use App\Repository\UserRepository;
use DateTime;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class NewsController extends AbstractController
{

    /**
     * @var JWTTokenManagerInterface
     */
    private $jwtManager;
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorageInterface;
    private $jwtAuthenticator;

    public function __construct(TokenStorageInterface $tokenStorageInterface, JWTTokenManagerInterface $jwtManager)
    {
        $this->jwtManager = $jwtManager;
        $this->tokenStorageInterface = $tokenStorageInterface;
    }

    /**
     * @param NewsRepository $newsRepository
     * @return JsonResponse
     */
    public function getNews(NewsRepository $newsRepository): JsonResponse
    {
        $data = $newsRepository->findAll();
        return $this->response($data);
    }

    /**
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param UserRepository $userRepository
     * @return JsonResponse
     */
    public function addNews(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository): JsonResponse
    {
        try{
            $request = $this->transformJsonBody($request);

            if (!$request || !$request->request->get('title') || !$request->request->get('description')){
                throw new Exception();
            }

            $news = new News();
            $news->setTitle($request->get('title'));
            $news->setDescription($request->get('description'));
            $news->setCreatedDate(new DateTime("now", new DateTimeZone('Europe/Prague')));
            /**
             * TODO
             * userID
             */
            $decodedJwtToken = $this->jwtManager->decode($this->tokenStorageInterface->getToken());
            $user = $userRepository->findOneBy(['id' => $decodedJwtToken['id']]);
            $news->setUserId($user);

            $entityManager->persist($news);
            $entityManager->flush();

            $data = [
                'status' => 200,
                'success' => "News added successfully",
            ];
            return $this->response($data);

        } catch (Exception $e){
            echo ($e->getMessage());
            $data = [
                'status' => 422,
                'errors' => "Data no valid",
            ];
            return $this->response($data, 422);

        }

    }

    /**
     * @param NewsRepository $newsRepository
     * @param $id
     * @return JsonResponse
     */
    public function getNewsById(NewsRepository $newsRepository, $id): JsonResponse
    {
        $news = $newsRepository->find($id);
        if (!$news){
            $data = [
                'status' => 404,
                'errors' => "News not found",
            ];
            return $this->response($data, 404);
        }
        return $this->response($news);
    }

    /**
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param NewsRepository $newsRepository
     * @param $id
     * @return JsonResponse
     */
    public function updateNews(Request $request, EntityManagerInterface $entityManager, NewsRepository $newsRepository, $id): JsonResponse
    {

        try{
            $news = $newsRepository->find($id);

            if (!$news){
                $data = [
                    'status' => 404,
                    'errors' => "News not found",
                ];
                return $this->response($data, 404);
            }

            $request = $this->transformJsonBody($request);

            if (!$request || !$request->get('title') || !$request->request->get('description')){
                throw new Exception();
            }

            $news->setTitle($request->get('title'));
            $news->setDescription($request->get('description'));
            $entityManager->flush();

            $data = [
                'status' => 200,
                'errors' => "News updated successfully",
            ];
            return $this->response($data);

        }catch (Exception $e){
            $data = [
                'status' => 422,
                'errors' => "Data no valid",
            ];
            return $this->response($data, 422);
        }

    }

    /**
     * @param EntityManagerInterface $entityManager
     * @param NewsRepository $newsRepository
     * @param $id
     * @return JsonResponse
     */
    public function deleteNews(EntityManagerInterface $entityManager, NewsRepository $newsRepository, $id): JsonResponse
    {
        $news = $newsRepository->find($id);

        if (!$news){
            $data = [
                'status' => 404,
                'errors' => "News not found",
            ];
            return $this->response($data, 404);
        }

        $entityManager->remove($news);
        $entityManager->flush();
        $data = [
            'status' => 200,
            'errors' => "News deleted successfully",
        ];
        return $this->response($data);
    }

    /**
     * @param $data
     * @param int $status
     * @param array $headers
     * @return JsonResponse
     */
    public function response($data, $status = 200, $headers = []): JsonResponse
    {
        return new JsonResponse($data, $status, $headers);
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
