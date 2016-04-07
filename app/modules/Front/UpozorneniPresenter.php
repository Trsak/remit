<?php

namespace Remit\Module\Front\Presenters;

use App\Notification;

class UpozorneniPresenter extends \Remit\Module\Base\Presenters\BasePresenter
{

    public function beforeRender()
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect("Default:");
        }
    }

    public function actionDefault()
    {
        $this->template->notificationsAll = $this->EntityManager->getRepository(Notification::class)->findBy(array('user' => $this->getUser()->identity->getId()), array('datetime' => 'DESC'));

        $token = new \Tmdb\ApiToken($this->context->parameters["movies"]["apiKey"]);
        $client = new \Tmdb\Client($token, ['secure' => false]);

        $movies = [];

        foreach ($this->template->notificationsAll as $key => $notification) {
            $data = json_decode($notification->data);
            if ($notification->type == 2) {
                $movies[$key] = $data->name;
            } else {
                $movies[$key] = $client->getMoviesApi()->getMovie($data->movie_id, array('language' => 'cs'));
            }
        }

        $this->template->movies = $movies;
    }

    public function handleRemoveNotification($id)
    {
        $notification = $this->EntityManager->getRepository(Notification::class)->findOneBy(array('id' => $id));

        if ($notification->user != $this->getUser()->id) {
            $this->flashMessage("Toto upozornění není tvoje!", "error");
        } else {
            $this->EntityManager->remove($notification);
            $this->EntityManager->flush();
            $this->flashMessage("Upozornění bylo úspěšně smazáno!", "success");
        }

        $this->redirect('this');
    }
}
