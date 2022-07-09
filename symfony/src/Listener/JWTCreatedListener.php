<?php
namespace App\Listener;
use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationFailureResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
class JWTCreatedListener
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;
    /**
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct( TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }
    /**
     * Adds additional data to the generated JWT
     *
     * @param JWTCreatedEvent $event
     *
     * @return void
     */
    public function onJWTCreated(JWTCreatedEvent $event)
    {
        /** @var $user User */
        $user = $event->getUser();
        // add new data
        $payload['id'] = $user->getId();
        $payload['username'] = $user->getUsername();
        $payload['roles'] = $user->getRoles();
        $event->setData($payload);
    }
    public function onAuthenticationFailureResponse(AuthenticationFailureEvent $event)
    {
        $data = [
            'status'  => '401 Unauthorized',
            'message' => 'Špatné přihlašovací údaje, prosím ujistěte se zda používáte správné přihlašovací jméno/heslo, po případě zda jste potvrdil/a aktivační email.',
        ];
        $response = new JWTAuthenticationFailureResponse($data);
        $event->setResponse($response);
    }
}