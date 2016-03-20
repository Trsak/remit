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
}
