<?php

namespace Remit\Module\Front\Presenters;

use App\TvChannel,
    App\TvGuide,
    App\Notification,
    Nette\Application\UI;

class TelevizePresenter extends \Remit\Module\Base\Presenters\BasePresenter
{
    public function actionDefault($channel = "false")
    {
        $channels = $this->EntityManager->getRepository(TvChannel::class)->findAll();
        $this->template->channels = $channels;

        if (!$channel) {
            $channel = "513.dvb.guide";
        }

        $guides = $this->EntityManager->getRepository(TvGuide::class)->findBy(array('channel' => $channel), array('start' => 'ASC'));
        $this->template->guides = $guides;
        $this->template->channel = $channel;

        $channelSelected = $this->EntityManager->getRepository(TvChannel::class)->findOneBy(array('id' => $channel));
        $this->template->channelSelected = $channelSelected;
    }

    protected function createComponentTvNotificationForm()
    {
        $form = new UI\Form;
        $form->addText('datetime')
            ->setRequired('Musíte vybrat datum a čas upozornění!');
        $form->addCheckbox('emailNotif', 'Emailu');
        $form->addCheckbox('facebookNotif', 'facebooku');
        $form->addCheckbox('smsNotif', 'SMS');
        $form->addHidden('name');
        $form->addHidden('start');
        $form->addHidden('tv');
        $form->addSubmit('addNotification', 'Přidat upozornění');
        $form->onSuccess[] = array($this, 'tvNotificationFormSucceeded');

        return $form;
    }


    public function tvNotificationFormSucceeded(UI\Form $form, $values)
    {
        $time = strtotime($values->datetime);
        $timeStart = strtotime($values->start);

        if ($time < time()) {
            $this->flashMessage('Zadané datum a čas pro upozornění již bylo!', 'error');
        } elseif ($time > $timeStart) {
            $this->flashMessage('Zadané datum a čas pro upozornění je až po odvysílání filmu!', 'error');
        } else {
            $data = [];
            $data["name"] = $values->name;
            $data["start"] = $values->start;
            $data["tv"] = $values->tv;

            $notification = new Notification();
            $notification->user = $this->getUser()->id;
            $notification->type = 2;
            $notification->data = json_encode($data);
            $notification->datetime = new \DateTime($values->datetime);
            $notification->email = $values->emailNotif;
            $notification->facebook = $values->facebookNotif;
            $notification->sms = $values->smsNotif;

            $this->EntityManager->persist($notification);
            $this->EntityManager->flush();

            $this->flashMessage('Upozornění na vysílání filmu v TV bylo úspěšně přidáno!', 'success');
        }

        $this->redirect('this');
    }
}
