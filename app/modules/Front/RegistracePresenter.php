<?php

namespace Remit\Module\Front\Presenters;

use Nette\Application\UI,
    App\User,
    Nette\Security as NS;

class RegistracePresenter extends \Remit\Module\Base\Presenters\BasePresenter
{
    /**
     * @inject
     * @var \Kdyby\Doctrine\EntityManager
     */
    public $EntityManager;

    protected function createComponentRegistrationForm()
    {
        $form = new UI\Form;
        $form->addText('username', 'Uživatelské jméno')
             ->setRequired('Musíte zadat uživatelské jméno!');
        $form->addText('email', 'Email')
             ->setRequired('Musíte zadat Email!')
             ->addRule(UI\Form::EMAIL, 'Email není ve správném tvaru!');
        $form->addPassword('password', 'Heslo')
             ->setRequired('Musíte zadat heslo!')
             ->addRule(UI\Form::MIN_LENGTH, 'Heslo musí mít alespoň 3 znaky!', 3);
        $form->addPassword('passwordAgain', 'Heslo znovu')
             ->addRule(UI\Form::EQUAL, "Hesla se musí shodovat!", $form["password"]);
        $form->addReCaptcha('captcha', NULL, "Musíte potvrdit, že jste člověk!");
        $form->addSubmit('register', 'Registrovat');
        $form->onSuccess[] = array($this, 'registrationFormSucceeded');

        return $form;
    }

    public function registrationFormSucceeded(UI\Form $form, $values)
    {
        $username = $this->EntityManager->getRepository(User::class)->findOneBy(array('username' => $values["username"]));
        $email = $this->EntityManager->getRepository(User::class)->findOneBy(array('username' => $values["email"]));

        if (!is_null($username)) {
            $form["username"]->addError("Zadané uživatelské jméno již někdo využívá!");
        }
        elseif (!is_null($email)) {
            $form["email"]->addError("Zadaný Email již někdo využívá!");
        }

        if (!$form->hasErrors()) {
            $user = new User();
            $user->username = $values["username"];
            $user->email = $values["email"];
            $user->password = NS\Passwords::hash($values["password"]);
            $this->EntityManager->persist($user);
            $this->EntityManager->flush();

            $this->redirect('Prihlaseni:');
        }
    }
}
