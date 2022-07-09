<?php
namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use DateTime;
use DateTimeZone;
use Exception;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class AuthController extends ApiController
{

    /**
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @param UserRepository $repository
     * @return JsonResponse
     * @throws Exception
     */
    public function register(Request $request, UserPasswordEncoderInterface $encoder, UserRepository $repository): JsonResponse
    {
        $em = $this->getDoctrine()->getManager();
        $request = $this->transformJsonBody($request);
        $firstName = $request->get('firstName');
        $lastName = $request->get('lastName');
        $password = $request->get('password');
        $email = $request->get('email');

        if(empty($firstName) || !$this->checkChars($firstName)) {
            return $this->respondValidationError("Invalid first name");
        }
        if(empty($lastName) || !$this->checkChars($lastName)) {
            return $this->respondValidationError("Invalid last name");
        }
        if(empty($password)) {
            return $this->respondValidationError("Invalid password");
        }
        if (strlen($password) < 5) {
            return $this->respondValidationError("Password must be at least 5 character long!");
        }
        if (!$this->checkNumbers($password)) {
            return $this->respondValidationError("Password must include at least one number!");
        }
        if (!$this->checkChars($password)) {
            return $this->respondValidationError("Password must include at least one letter!");
        }
        if(empty($email) || !$this->checkEmail($email)) {
            return $this->respondValidationError("Invalid email");
        }
        if($repository->emailAlreadyExists($email)) {
            return $this->respondValidationError("Email already exists");
        }

        $user = new User();
        $user->setPassword($encoder->encodePassword($user, $password));
        $user->setEmail($email);
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setCreatedDate(new DateTime("now", new DateTimeZone('Europe/Prague')));
        $em->persist($user);
        $em->flush();
        return $this->respondWithSuccess(sprintf('User %s successfully created', $user->getEmail()));
    }

    /**
     * @param $message
     * @return bool
     */
    private function checkChars($message): bool
    {
        return preg_match("#[a-zA-Z]+#", $message);
    }

    /**
     * @param $message
     * @return bool
     */
    private function checkNumbers($message): bool
    {
        return preg_match("#[0-9]+#", $message);
    }

    /**
     * @param $email
     * @return bool
     */
    private function checkEmail($email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
}