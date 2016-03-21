<?php

namespace Remit\Module\Base\Presenters;

use Nette,
    Nette\Application\UI,
    App\User;


abstract class BasePresenter extends Nette\Application\UI\Presenter
{
    /** @var \Kdyby\Facebook\Facebook */
    private $facebook;

    /**
     * @inject
     * @var \Kdyby\Doctrine\EntityManager
     */
    public $EntityManager;

    public function __construct(\Kdyby\Facebook\Facebook $facebook)
    {
        parent::__construct();
        $this->facebook = $facebook;
    }

    /** @return \Kdyby\Facebook\Dialog\LoginDialog */
    protected function createComponentFbLogin() //TODO: facebook login
    {
        $dialog = $this->facebook->createDialog('login');
        /** @var \Kdyby\Facebook\Dialog\LoginDialog $dialog */

        $dialog->onResponse[] = function (\Kdyby\Facebook\Dialog\LoginDialog $dialog) {
            $fb = $dialog->getFacebook();

            if (!$fb->getUser()) {
                $this->flashMessage("Sorry bro, facebook authentication failed.");
                return;
            }

            try {
                $me = $fb->api('/me', NULL, ['fields' => ['id', 'name', 'email']]);

                $existing = $this->EntityManager->getRepository(User::class)->findOneBy(array('facebookId' => $fb->getUser()));

                if (is_null($existing)) {
                    $user = new User();
                    $user->username = $me["name"];
                    $user->email = "";
                    $user->password = 0;
                    $user->facebookId = $fb->getUser();
                    $user->facebookToken = $fb->getAccessToken();
                    $this->EntityManager->persist($user);
                    $this->EntityManager->flush();

                    $this->getUser()->login($me["name"], 0);
                } else {
                    $existing->facebookToken = $fb->getAccessToken();
                    $this->EntityManager->flush();
                    $this->getUser()->login($existing->username, 0);
                }

            } catch (\Kdyby\Facebook\FacebookApiException $e) {
                $this->flashMessage("Sorry bro, facebook authentication failed hard.");
            }

            $this->redirect('this');
        };

        return $dialog;
    }

    protected function createComponentLoginForm()
    {
        $form = new UI\Form;
        $form->addText('username', 'Uživatelské jméno')
            ->setRequired('Musíte zadat uživatelské jméno!');
        $form->addPassword('password', 'Heslo')
            ->setRequired('Musíte zadat heslo!');
        $form->addCheckbox('remember', 'Zapamatovat');
        $form->addSubmit('login', 'Přihlásit');
        $form->onSuccess[] = array($this, 'loginFormSucceeded');

        return $form;
    }

    public function loginFormSucceeded(UI\Form $form, $values)
    {
        try {
            $this->getUser()->login($values["username"], $values["password"]);
        } catch (Nette\Security\AuthenticationException $e) {
            $form->addError($e->getMessage());
        }

        if ($values["remember"]) {
            $this->getUser()->setExpiration('1 year', FALSE);
        }
        else {
            $this->getUser()->setExpiration('30 minutes', TRUE);
        }
    }

}
