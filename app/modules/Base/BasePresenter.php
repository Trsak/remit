<?php

namespace Remit\Module\Base\Presenters;

use Nette,
    Nette\Application\UI,
    App\User,
    App\Newsletter,
    App\MovieGenres,
    App\Notification,
    Remit\Sms,
    Nette\Mail\SendmailMailer,
    Nette\Mail\Message;


abstract class BasePresenter extends Nette\Application\UI\Presenter
{
    /** @var \Kdyby\Facebook\Facebook */
    private $facebook;

    private $genres = [];

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

        $genres = $this->EntityManager->getRepository(MovieGenres::class)->findAll();
        $this->template->genres = $genres;

        foreach ($genres as $genre) {
            $this->genres[$genre->id] = $genre->genre;
        }

        $this->template->addFilter('genre', function ($id) {
            if (!isset($this->genres[$id])) {
                return $this->genres[$id];
            }
            return false;
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

            $notifications = $this->EntityManager->getRepository(Notification::class)->findBy(array('user' => $this->getUser()->identity->getId(), 'done' => 0));
            $this->template->notifications = $notifications;
        }

        $this->checkNotifications();
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
                        $this->redirect('Prihlaseni:fb', array("data" => json_encode($me), "token" => json_encode($fb->getAccessToken())));
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
                die($e->getMessage());
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
        $form->addHidden('fbToken');
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
                $user->facebookToken = $values["fbToken"];
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

    protected function createComponentMovieSearchForm()
    {
        $form = new UI\Form;
        $form->addText('name', 'Název filmu');
        $form->addSubmit('search', 'Hledat');
        $form->onSuccess[] = array($this, 'searchFormSucceeded');

        return $form;
    }


    public function searchFormSucceeded(UI\Form $form, $values)
    {
        $name = urldecode($this->removeAccents($values->name));
        $this->redirect('Filmy:', array('search' => true, 'name' => $name));
    }

    protected function createComponentMovieCard()
    {
        $control = new \Remit\MovieCardControl();
        $control->setGenres($this->genres);
        return $control;
    }

    public function checkNotifications()
    {
        $token = new \Tmdb\ApiToken($this->context->parameters["movies"]["apiKey"]);
        $client = new \Tmdb\Client($token, ['secure' => false]);

        $query = $this->EntityManager->createQuery('SELECT n FROM App\Notification n WHERE n.done = 0 AND n.datetime < :now')
            ->setParameter('now', new \DateTime("now"));

        $notifications = $query->getResult();

        foreach ($notifications as $notification) {
            $user = $this->EntityManager->getRepository(User::class)->findOneBy(array('id' => $notification->user));
            $notif = $this->EntityManager->getRepository(Notification::class)->findOneBy(array('id' => $notification->id));
            //TODO: Dodelat Email a fb upozornění
            if ($user) {
                $data = json_decode($notification->data);
                $movie = $client->getMoviesApi()->getMovie($data->movie_id, array('language' => 'cs'));

                $date = date('d.m.Y', strtotime($movie["release_date"]));

                if ($notification->sms and $user->phone) {
                    Sms::send("Pozor! Jiz " . $date . " je premiera filmu " . $movie["title"] . "!", $user->phone);
                }

                if ($notification->email and $user->email) {
                    $mail = new Message;
                    $mail->setFrom('info@leminou.eu')
                        ->addTo($user->email)
                        ->setSubject("Upozornění na premiéru filmu " . $movie["title"])
                        ->setHTMLBody("Dobrý den,<br>nezapomeňte, již " . $date . " je premiéra filmu " . $movie["title"] . "!");

                    $mailer = new SendmailMailer;
                    $mailer->send($mail);
                }

                if ($notification->facebook and $user->facebookId) {
                    try {
                        $this->facebook->api('/' . $user->facebookId . '/notifications', 'POST', ['access_token' => $this->facebook->accessToken, 'template' => 'Již ' . $date . ' je premiéra filmu ' . $movie["title"] . '!']);
                    } catch
                    (\Kdyby\Facebook\FacebookApiException $e) {
                    }
                }

                $notif->done = 1;
                $this->EntityManager->merge($notif);
            }
            $this->EntityManager->flush();
        }
    }

    public
    function removeAccents($text)
    {
        $table = Array(
            'ä' => 'a',
            'Ä' => 'A',
            'á' => 'a',
            'Á' => 'A',
            'à' => 'a',
            'À' => 'A',
            'ã' => 'a',
            'Ã' => 'A',
            'â' => 'a',
            'Â' => 'A',
            'č' => 'c',
            'Č' => 'C',
            'ć' => 'c',
            'Ć' => 'C',
            'ď' => 'd',
            'Ď' => 'D',
            'ě' => 'e',
            'Ě' => 'E',
            'é' => 'e',
            'É' => 'E',
            'ë' => 'e',
            'Ë' => 'E',
            'è' => 'e',
            'È' => 'E',
            'ê' => 'e',
            'Ê' => 'E',
            'í' => 'i',
            'Í' => 'I',
            'ï' => 'i',
            'Ï' => 'I',
            'ì' => 'i',
            'Ì' => 'I',
            'î' => 'i',
            'Î' => 'I',
            'ľ' => 'l',
            'Ľ' => 'L',
            'ĺ' => 'l',
            'Ĺ' => 'L',
            'ń' => 'n',
            'Ń' => 'N',
            'ň' => 'n',
            'Ň' => 'N',
            'ñ' => 'n',
            'Ñ' => 'N',
            'ó' => 'o',
            'Ó' => 'O',
            'ö' => 'o',
            'Ö' => 'O',
            'ô' => 'o',
            'Ô' => 'O',
            'ò' => 'o',
            'Ò' => 'O',
            'õ' => 'o',
            'Õ' => 'O',
            'ő' => 'o',
            'Ő' => 'O',
            'ř' => 'r',
            'Ř' => 'R',
            'ŕ' => 'r',
            'Ŕ' => 'R',
            'š' => 's',
            'Š' => 'S',
            'ś' => 's',
            'Ś' => 'S',
            'ť' => 't',
            'Ť' => 'T',
            'ú' => 'u',
            'Ú' => 'U',
            'ů' => 'u',
            'Ů' => 'U',
            'ü' => 'u',
            'Ü' => 'U',
            'ù' => 'u',
            'Ù' => 'U',
            'ũ' => 'u',
            'Ũ' => 'U',
            'û' => 'u',
            'Û' => 'U',
            'ý' => 'y',
            'Ý' => 'Y',
            'ž' => 'z',
            'Ž' => 'Z',
            'ź' => 'z',
            'Ź' => 'Z'
        );

        return strtr($text, $table);
    }
}
