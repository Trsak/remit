<?php

namespace Remit\Module\Front\Presenters;

use Nette\Application\UI,
    App\User,
    App\Code,
    Nette\Security as NS,
    Remit\Sms,
    Remit\Codes,
    Nette\Utils\Image;

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
        $this->template->code = false;

        if ($this->getParameter("change") == "telefon") {
            $code = $this->EntityManager->getRepository(Code::class)->findOneBy(array('user' => $this->getUser()->id));
            if (!is_null($code)) {
                $this->template->code = true;
            }
        }
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
    {
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
        if (!NS\Passwords::verify($values->password, $this->template->userData->password)) {
            $form["password"]->addError("Špatně zadané heslo!");
        }

        if (!$form->hasErrors()) {
            $phone = explode(" ", $values->phone);

            $code = new Codes($this->getUser()->id, 1, $phone[1], $this->EntityManager);

            Sms::send("Vas kod pro potvrzeni telefonu na webu remit: " . $code->code . " ", $phone[1]);
            $this->flashMessage("Byl vám zaslán kód pro potvrení vašeho telefonu!");
            $this->redirect("this");
        }
    }

    protected function createComponentPhoneConfirmForm()
    {
        $form = new UI\Form;
        $form->addText('code', 'Potvrzovací kód')
            ->setRequired('Musíte zadat potvrzovací kód!');
        $form->addSubmit('confirm', 'Potvrdit telefon');
        $form->onSuccess[] = array($this, 'phoneConfirmFormSucceeded');

        return $form;
    }

    public function phoneConfirmFormSucceeded(UI\Form $form, $values)
    {
        $code = $this->EntityManager->getRepository(Code::class)->findOneBy(array('user' => $this->getUser()->id, 'code' => $values->code));

        if (is_null($code)) {
            $form["code"]->addError("Špatně zadaný kód!");
        }

        if (!$form->hasErrors()) {
            $user = $this->EntityManager->getRepository(User::class)->findOneBy(array('id' => $this->getUser()->id));
            $user->phone = $code->data;

            $this->EntityManager->remove($code);
            $this->EntityManager->flush();

            $this->flashMessage("Váš telefon byl úspěšně změněn!", "success");
            $this->redirect("this");
        }
    }

    public function handleRemoveCode()
    {
        $code = $this->EntityManager->getRepository(Code::class)->findOneBy(array('user' => $this->getUser()->id));
        $this->EntityManager->remove($code);
        $this->EntityManager->flush();

        $this->flashMessage("Potvrzení telefonu bylo zrušeno!");
        $this->redirect("this");
    }

    protected function createComponentAvatarForm()
    {
        $form = new UI\Form;
        $form->addUpload('avatar', 'Avatar')
            ->addRule(UI\Form::IMAGE, 'Avatar musí být JPEG, PNG nebo GIF.')
            ->addRule(UI\Form::MAX_FILE_SIZE, 'Maximální velikost souboru je 1 MB.', 1 * 1024 * 1024);
        $form->onSuccess[] = array($this, 'avatarFormSucceeded');

        return $form;
    }

    public function avatarFormSucceeded(UI\Form $form, $values)
    {
        $image = Image::fromFile($values->avatar);
        $image->resize(160, 160, Image::STRETCH);
        $image->sharpen();

        $image->save('img/user/'.$this->getUser()->id.'.jpg', 100, Image::JPEG);
    }
}
