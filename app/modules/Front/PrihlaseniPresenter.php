<?php

namespace Remit\Module\Front\Presenters;

use Nette\Application\UI,
    App\User;

class PrihlaseniPresenter extends \Remit\Module\Base\Presenters\BasePresenter
{
    public function beforeRender()
    {
        if ($this->getUser()->isLoggedIn()) {
            $this->redirect("Default:");
        }
    }

    public function actionDefault($data = NULL, $token = NULL)
    {
        if (!is_null($data)) {
            $data = json_decode($data, true);
            $token = json_decode($token, true);
            $this->template->fbId = $data["id"];
            $this->template->fbToken = $token;
        } else {
            $this->template->fbId = false;
            $this->template->fbToken = false;
        }
    }

    public function actionFb($data, $token) {
        $this->template->data = $data;
        $this->template->token = $token;
    }
}
