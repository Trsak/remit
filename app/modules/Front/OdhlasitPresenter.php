<?php

namespace Remit\Module\Front\Presenters;

class OdhlasitPresenter extends \Remit\Module\Base\Presenters\BasePresenter
{
    public function beforeRender()
    {
        $this->getUser()->logout();
        $this->redirect("Default:");
    }
}
