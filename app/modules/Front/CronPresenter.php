<?php

namespace Remit\Module\Front\Presenters;

use App\TvChannel;

class CronPresenter extends \Remit\Module\Base\Presenters\BasePresenter
{
    /**
     * @inject
     * @var \Kdyby\Doctrine\EntityManager
     */
    public $EntityManager;

    public $tv = "http://televize.sh.cvut.cz/xmltv/sit2.xml";

    public function actionTv()
    {
        $dom = new \DOMDocument();
        $dom->load($this->tv);

        $channels = $dom->getElementsByTagName('channel');

        foreach ($channels as $channel) {
            try {
                $tvChannel = new TvChannel();
                $tvChannel->id = $channel->getAttribute("id");
                $tvChannel->name = $channel->nodeValue;
                $this->EntityManager->persist($tvChannel);
                $this->EntityManager->flush();
            } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {

            }
        }

        die("OK");
    }
}
