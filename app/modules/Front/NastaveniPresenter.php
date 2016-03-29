<?php

namespace Remit\Module\Front\Presenters;

use Nette\Application\UI,
    App\User,
    Nette\Security as NS,
    Remit\Sms,
    Remit\Codes;

class NastaveniPresenter extends \Remit\Module\Base\Presenters\BasePresenter
{
    public function beforeRender()
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect("Default:");
        }
    }

    public function actionDefault($change)
    {
        $this->template->change = $change;
    }

    protected function createComponentEmailChangeForm()
    {
        $form = new UI\Form;
        $form->addText('email', 'Email')
            ->setRequired('Musíte zadat Email!')
            ->addRule(UI\Form::EMAIL, 'Email není ve správném tvaru!');
        $form->addPassword('password', 'Heslo')
            ->setRequired('Musíte zadat heslo!')
            ->addRule(UI\Form::MIN_LENGTH, 'Heslo musí mít alespoň 3 znaky!', 3);
        $form->addSubmit('change', 'Změnit email');
        $form->onSuccess[] = array($this, 'emailChangeFormSucceeded');

        return $form;
    }

    public function emailChangeFormSucceeded(UI\Form $form, $values)
    {
        $email = $this->EntityManager->getRepository(User::class)->findOneBy(array('email' => $values["email"]));
        $user = $this->EntityManager->getRepository(User::class)->findOneBy(array('id' => $this->getUser()->identity->getId()));

        if (!is_null($email)) {
            $form["email"]->addError("Zadaný Email již někdo využívá!");
        }

        if (!NS\Passwords::verify($values["password"], $this->template->userData->password)) {
            $form["password"]->addError("Špatně zadané heslo!");
        }

        if (!$form->hasErrors()) {
            $user->email = $values["email"];
            $this->EntityManager->merge($user);
            $this->EntityManager->flush();

            $this->flashMessage("Email byl úspěšně změněn!", "success");
        }
    }

    protected function createComponentPasswordChangeForm()
    {
        $form = new UI\Form;
        $form->addPassword('password', 'Heslo')
            ->setRequired('Musíte zadat heslo!')
            ->addRule(UI\Form::MIN_LENGTH, 'Heslo musí mít alespoň 3 znaky!', 3);
        $form->addPassword('passwordNew', 'Heslo znovu')
            ->setRequired('Musíte zadat nové heslo!')
            ->addRule(UI\Form::MIN_LENGTH, 'Heslo musí mít alespoň 3 znaky!', 3);
        $form->addPassword('passwordNewAgain', 'Heslo znovu')
            ->addRule(UI\Form::EQUAL, "Hesla se musí shodovat!", $form["passwordNew"]);
        $form->addSubmit('change', 'Změnit heslo');
        $form->onSuccess[] = array($this, 'passwordChangeFormSucceeded');

        return $form;
    }

    public function passwordChangeFormSucceeded(UI\Form $form, $values)
    {
        $user = $this->EntityManager->getRepository(User::class)->findOneBy(array('id' => $this->getUser()->identity->getId()));

        if (!NS\Passwords::verify($values["password"], $this->template->userData->password)) {
            $form["password"]->addError("Špatně zadané heslo!");
        }

        if (!$form->hasErrors()) {
            $user->password = NS\Passwords::hash($values["passwordNew"]);
            $this->EntityManager->merge($user);
            $this->EntityManager->flush();

            $this->flashMessage("Heslo bylo úspěšně změněno!", "success");
        }
    }

    protected function createComponentPhoneChangeForm()
    { //TODO: Zpracování a ověření telefonu
        $form = new UI\Form;
        $form->addText('phone', 'Telefon')
            ->setRequired('Musíte zadat telefon!')
            ->addRule(UI\Form::PATTERN, 'Prosím zadejte telefon ve správném tvaru!', '^(\+420)? ?[1-9][0-9]{2}?[0-9]{3}?[0-9]{3}');
        $form->addPassword('password', 'Heslo')
            ->setRequired('Musíte zadat heslo!')
            ->addRule(UI\Form::MIN_LENGTH, 'Heslo musí mít alespoň 3 znaky!', 3);
        $form->addSubmit('change', 'Změnit telefon');
        $form->onSuccess[] = array($this, 'phoneChangeFormSucceeded');

        return $form;
    }

    public function phoneChangeFormSucceeded(UI\Form $form, $values)
    {
        if (!NS\Passwords::verify($values["password"], $this->template->userData->password)) {
            $form["password"]->addError("Špatně zadané heslo!");
        }

        if (!$form->hasErrors()) {
            $phone = explode(" ", $values->phone);

            Sms::send("Vas kod pro potvrzeni telefonu na webu remit: " . Codes::randomCode() ." ", $phone[1]);
            $this->flashMessage("Byl vám zaslán kód pro potvrení vašeho telefonu!");
        }
    }
}
