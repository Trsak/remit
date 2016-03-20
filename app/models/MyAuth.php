<?php
use Nette\Security as NS,
    App\User;

class MyAuth extends Nette\Object implements NS\IAuthenticator
{
    /**
     * @inject
     * @var \Kdyby\Doctrine\EntityManager
     */
    public $EntityManager;

    function __construct(Kdyby\Doctrine\EntityManager $EntityManager)
    {
        $this->EntityManager = $EntityManager;
    }

    function authenticate(array $credentials)
    {
        list($username, $password) = $credentials;
        $user = $this->EntityManager->getRepository(User::class)->findOneBy(array('username' => $username));

        if (is_null($user)) {
            throw new NS\AuthenticationException('Uživatel nebyl nalezen!');
        }

        if ($password != 0) {
            if (!NS\Passwords::verify($password, $user->password)) {
                throw new NS\AuthenticationException('Špatně zadané heslo!');
            }
        }

        return new NS\Identity($user->id, 'user', array('username' => $user->username));
    }
}