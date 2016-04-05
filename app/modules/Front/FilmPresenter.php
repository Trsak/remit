<?php

namespace Remit\Module\Front\Presenters;

use App\Notification,
    Nette\Application\UI;

class FilmPresenter extends \Remit\Module\Base\Presenters\BasePresenter
{
    public $premiere;

    public function actionDefault($id, $page = 'info')
    {
        $token = new \Tmdb\ApiToken($this->context->parameters["movies"]["apiKey"]);
        $client = new \Tmdb\Client($token, ['secure' => false]);

        $this->template->addFilter('afterPremiere', function ($release_date) {
            $today = date("Y-m-d");
            $today = strtotime($today);
            $release_date = strtotime($release_date);

            if ($today > $release_date) {
                return true;
            }

            return false;
        });

        $this->template->movie = $client->getMoviesApi()->getMovie($id, array('language' => 'cs'));
        $this->template->movieVideos = $client->getMoviesApi()->getVideos($id);
        $this->template->movieImages = $client->getMoviesApi()->getImages($id);
        $this->template->movieAlternatives = $client->getMoviesApi()->getSimilar($id, array('language' => 'cs'));
        $this->template->movieCredits = $client->getMoviesApi()->getCredits($id, array('language' => 'cs'));

        $this->template->page = $page;
        $this->premiere = $this->template->movie["release_date"];

        $this->template->notifMaxDate = date('Y-m-d', strtotime('-1 day', strtotime($this->premiere)));
        $this->template->notifMinDate = date('Y-m-d', time());
    }


    protected function createComponentPremiereNotificationForm() //TODO: Zkontrolovat, jestli si uživatel může poslat sms, facebook apod.
    {
        $form = new UI\Form;
        $form->addText('datetime')
            ->setRequired('Musíte vybrat datum a čas upozornění!');
        $form->addCheckbox('emailNotif', 'Emailu');
        $form->addCheckbox('facebookNotif', 'facebooku');
        $form->addCheckbox('smsNotif', 'SMS');
        $form->addSubmit('addNotification', 'Přidat upozornění');
        $form->onSuccess[] = array($this, 'premiereNotificationFormSucceeded');

        return $form;
    }

    public function premiereNotificationFormSucceeded(UI\Form $form, $values)
    {
        $time = strtotime($values->datetime);
        $timePremiere = strtotime($this->premiere);

        if ($time < time()) {
            $this->flashMessage('Zadané datum a čas pro upozornění již bylo!', 'error');
        } elseif ($time > $timePremiere) {
            $this->flashMessage('Zadané datum a čas pro upozornění je až po premiéře filmu!', 'error');
        } else {
            $data = [];
            $data["movie_id"] = $this->template->movie["id"];

            $notification = new Notification();
            $notification->user = $this->getUser()->id;
            $notification->type = 1;
            $notification->data = json_encode($data);
            $notification->datetime = new \DateTime($values->datetime);
            $notification->email = $values->emailNotif;
            $notification->facebook = $values->facebookNotif;
            $notification->sms = $values->smsNotif;

            $this->EntityManager->persist($notification);
            $this->EntityManager->flush();

            $this->flashMessage('Upozornění na premiéru filmu bylo úspěšně přidáno!', 'success');
        }

        $this->redirect('this');
    }
}