<?php

namespace Remit\Module\Front\Presenters;

use App\TvChannel;

class CronPresenter extends \Remit\Module\Base\Presenters\BasePresenter
{
    public $tv = "http://televize.sh.cvut.cz/xmltv/sit2.xml";

    public function actionTv()
    {
        $dom = new \DOMDocument();
        $dom->load($this->tv);

        $channels = $dom->getElementsByTagName('channel');

        foreach ($channels as $channel) {
            $tvChannel = new TvChannel();
            $tvChannel->id = trim($channel->getAttribute("id"));
            $tvChannel->name = trim($channel->nodeValue);
            $this->EntityManager->persist($tvChannel);
        }
        try {
            $this->EntityManager->flush();
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {

        }

        die("OK");
    }
}
