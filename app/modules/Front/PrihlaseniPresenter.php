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

    public function actionDefault($data = NULL)
    {
        if (!is_null($data)) {
            $data = json_decode($data, true);
            $this->template->fbId = $data["id"];
        } else {
            $this->template->fbId = false;
        }
    }

    public function actionFb($data) {
        $this->template->data = $data;
    }
}
