<?php

namespace Remit\Module\Front\Presenters;

use Nette\Application\UI,
    Nette\Mail\SendmailMailer,
    Nette\Mail\Message;

class KontaktPresenter extends \Remit\Module\Base\Presenters\BasePresenter
{
    protected function createComponentContactForm()
    {
        $form = new UI\Form;//TODO: Email aktuálně přilhášeného uživatele
        $form->addText('email', 'Email')
            ->setRequired('Musíte zadat Email!')
            ->addRule(UI\Form::EMAIL, 'Email není ve správném tvaru!');
        $form->addText('subject', 'Předmět')
            ->setRequired('Musíte zadat předmět!');
        $form->addTextArea('message', 'Zpráva')
            ->setRequired('Musíte zadat text zprávy!');
        $form->addReCaptcha('captcha', NULL, "Musíte potvrdit, že jste člověk!");
        $form->addSubmit('send', 'Odeslat zprávu');
        $form->onSuccess[] = array($this, 'contactFormSucceeded');

        return $form;
    }

    public function contactFormSucceeded(UI\Form $form, $values)
    {
        $mail = new Message;
        $mail->setFrom($values["email"])
            ->addTo('trsak1@seznam.cz')
            ->setSubject($values["subject"])
            ->setBody($values["message"]);

        $mailer = new SendmailMailer;
        $mailer->send($mail);

        $this->redirect("Kontakt:odeslano");
    }
}
