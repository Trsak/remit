<?php

namespace Remit\Module\Front\Presenters;

use App\User;

class UzivatelPresenter extends \Remit\Module\Base\Presenters\BasePresenter
{
    public function actionDefault($id, $name)
    {
        $user = $this->EntityManager->getRepository(User::class)->findOneBy(array('id' => $id, 'username' => $name));
        if (is_null($user)) {
            $this->flashMessage("Uživatel nebyl nalezen!", "error");
            $this->redirect("Default:");
        } else {
            $this->template->userProfile = $user;
        }
    }
}