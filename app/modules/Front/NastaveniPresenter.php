<?php

namespace Remit\Module\Front\Presenters;

use Nette\Application\UI;

class NastaveniPresenter extends \Remit\Module\Base\Presenters\BasePresenter
{
    public function beforeRender()
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect("Default:");
        }
    }

    public function actionDefault($change) {
        $this->template->change = $change;
    }
}
