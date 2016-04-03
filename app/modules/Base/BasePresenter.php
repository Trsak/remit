<?php

namespace Remit\Module\Base\Presenters;

use Nette,
    Nette\Application\UI,
    App\User,
    App\Newsletter,
    App\MovieGenres;


abstract class BasePresenter extends Nette\Application\UI\Presenter
{
    /** @var \Kdyby\Facebook\Facebook */
    private $facebook;

    /**
     * @inject
     * @var \Kdyby\Doctrine\EntityManager
     */
    public $EntityManager;

    public function __construct(\Kdyby\Facebook\Facebook $facebook)
    {
        parent::__construct();
        $this->facebook = $facebook;
    }

    public function startup()
    {
        parent::startup();

        $this->template->userData = false;
        $this->template->avatar = false;

        $this->template->addFilter('count', function ($array) {
            return count($array);
        });

        $this->template->addFilter('genre', function ($id) {
            $genre = $this->EntityManager->getRepository(MovieGenres::class)->findOneBy(array('id' => $id));
            return $genre->genre;
        });

        $this->template->addFilter('translate', function ($text) {
            return $this->translate($text);
        });

        if ($this->getUser()->isLoggedIn()) {
            $user = $this->EntityManager->getRepository(User::class)->findOneBy(array('id' => $this->getUser()->identity->getId()));
            $this->template->userData = $user;

            if (file_exists('./img/user/' . $this->getUser()->identity->getId() . '.jpg')) {
                $this->template->avatar = true;
            }
        }
    }

    public function translate($text)
    {
        $text = strtolower($text);
        $translation = array("director" => "režisér", "producer" => "producent", "original music composer" => "skladatel původní hudby", "director of photography" => "hlavní kamera",
            "casting" => "obsazení", "production design" => "produkční designu", "writer" => "scénář", "author" => "tvůrce", "executive producer" => "výkonný producent", "set decoration" => "dekorace",
            "adr & dubbing" => "adr & dabování", "sound recordist" => "záznam zvuku", "rigging gaffer" => "osvětlení", "cinematography" => "kinematografie", "color timer" => "časování barev",
            "makeup artist" => "maskér", "script supervisor" => "vedoucí scénáře", "released" => "vydáno", "music" => "hudba", "screenplay" => "scénář", "art direction" => "umělecká režie",
            "costume design" => "kostýmy", "gaffer" => "osvětlovač", "stunt coordinator" => "koodinátor kaskadérů", "visual effects editor" => "vizuální efekty", "animation" => "animace",
            "hairstylist" => "kadeřník", "makeup department head" => "vedoucí maskérny", "production manager" => "výrobní ředitel");

        if (isset($translation[$text])) {
            return $translation[$text];
        }

        return $text;
    }

    public function handleFbRemove()
    {
        $user = $this->EntityManager->getRepository(User::class)->findOneBy(array('id' => $this->getUser()->identity->getId()));
        $user->facebookId = 0;
        $user->facebookToken = 0;
        $this->EntityManager->merge($user);
        $this->EntityManager->flush();

        $this->flashMessage("Propojení s facebookem bylo úspěšně zrušeno", "success");
    }

    /** @return \Kdyby\Facebook\Dialog\LoginDialog */
    protected function createComponentFbLogin()
    {
        $dialog = $this->facebook->createDialog('login');
        /** @var \Kdyby\Facebook\Dialog\LoginDialog $dialog */

        $dialog->onResponse[] = function (\Kdyby\Facebook\Dialog\LoginDialog $dialog) {
            $fb = $dialog->getFacebook();

            if (!$fb->getUser()) {
                $this->flashMessage("Přihlášení přes facebook selhalo!", "error");
                return;
            }

            try {
                $me = $fb->api('/me', NULL, ['fields' => ['id', 'name', 'email']]);

                $existing = $this->EntityManager->getRepository(User::class)->findOneBy(array('facebookId' => $fb->getUser()));

                if (is_null($existing)) {
                    if ($this->getUser()->isLoggedIn()) {
                        $user = $this->EntityManager->getRepository(User::class)->findOneBy(array('id' => $this->getUser()->identity->getId()));
                        $user->facebookId = $me->id;
                        $user->facebookToken = $fb->getAccessToken();
                        $this->EntityManager->merge($user);
                        $this->EntityManager->flush();
                    } else {
                        $this->redirect('Prihlaseni:fb', array("data" => json_encode($me)));
                    }
                } else {
                    $existing->facebookToken = $fb->getAccessToken();
                    $this->EntityManager->merge($existing);
                    $this->EntityManager->flush();

                    try {
                        $this->getUser()->login($existing->username, 0);
                    } catch (Nette\Security\AuthenticationException $e) {
                        $this->flashMessage("Při přihlášení nastala chyba!", "error");
                    }
                }

            } catch (\Kdyby\Facebook\FacebookApiException $e) {
                $this->flashMessage("Přihlášení přes facebook selhalo!", "error");
            }

            $this->redirect('this');
        };

        return $dialog;
    }

    protected function createComponentNewsletterForm()
    {
        $form = new UI\Form;
        $form->addText('email', 'Email')
            ->setRequired('Musíte zadat Email!')
            ->addRule(UI\Form::EMAIL, 'Email není ve správném tvaru!');
        $form->addSubmit('subscribe', 'Přihlásit k odběru');
        $form->onSubmit[] = array($this, 'newsletterFormSucceeded');

        return $form;
    }

    public function newsletterFormSucceeded(UI\Form $form)
    {

        foreach ($form->errors as $error) {
            $this->presenter->flashMessage($error, 'error');
        }

        $email = $this->EntityManager->getRepository(Newsletter::class)->findOneBy(array('email' => $form->values->email));

        if (!$form->hasErrors()) {
            if (is_null($email)) {
                $newsletter = new Newsletter();
                $newsletter->email = $form->values->email;
                $this->EntityManager->persist($newsletter);
                $this->EntityManager->flush();
                $this->presenter->flashMessage("Váš E-mail byl úspěšně přihlášen pro odběr novinek!", 'success');
            } else {
                $this->presenter->flashMessage("Tento Email je již pro odběr novinek přihlášen!", 'error');
            }
        }
    }

    protected function createComponentLoginForm()
    {
        $form = new UI\Form;
        $form->addText('username', 'Uživatelské jméno')
            ->setRequired('Musíte zadat uživatelské jméno!');
        $form->addPassword('password', 'Heslo')
            ->setRequired('Musíte zadat heslo!');
        $form->addHidden('fbId');
        $form->addCheckbox('remember', 'Zapamatovat');
        $form->addSubmit('login', 'Přihlásit');
        $form->onSuccess[] = array($this, 'loginFormSucceeded');

        return $form;
    }

    public function loginFormSucceeded(UI\Form $form, $values)
    {
        try {
            $this->getUser()->login($values["username"], $values["password"]);

            if ($values["fbId"] != 0) {
                $user = $this->EntityManager->getRepository(User::class)->findOneBy(array('username' => $values["username"]));
                $user->facebookId = $values["fbId"];
                $this->EntityManager->merge($user);
                $this->EntityManager->flush();
            }

            if ($values["remember"]) {
                $this->getUser()->setExpiration('1 year', FALSE);
            } else {
                $this->getUser()->setExpiration('30 minutes', TRUE);
            }
        } catch (Nette\Security\AuthenticationException $e) {
            $form->addError($e->getMessage());
        }
    }
}
