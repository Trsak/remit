<?php

namespace Remit\Module\Front\Presenters;

use App\TvChannel,
    App\TvGuide;

class CronPresenter extends \Remit\Module\Base\Presenters\BasePresenter
{
    public $tv = "http://televize.sh.cvut.cz/xmltv/";

    public function actionTv($tv)
    {
        $dom = new \DOMDocument();
        $dom->load($this->tv . $tv . '.xml');

        $guides = $dom->getElementsByTagName('programme');
        $guideTitles = $dom->getElementsByTagName('title');

        $i = 0;
        foreach ($guides as $guide) {
            $start = explode(" ", $guide->getAttribute('start'));
            $start = $start[0];
            $year = substr($start, 0, 4);
            $month = substr($start, 4, 2);
            $day = substr($start, 6, 2);
            $hour = substr($start, 8, 2);
            $minute = substr($start, 10, 2);
            $start = $day . '.' . $month . '.' . $year . ' ' . $hour . ':' . $minute . ':' . '00';

            $stop = explode(" ", $guide->getAttribute('stop'));
            $stop = $stop[0];
            $year = substr($stop, 0, 4);
            $month = substr($stop, 4, 2);
            $day = substr($stop, 6, 2);
            $hour = substr($stop, 8, 2);
            $minute = substr($stop, 10, 2);
            $stop = $day . '.' . $month . '.' . $year . ' ' . $hour . ':' . $minute . ':' . '00';

            $start = strtotime($start);
            $stop = strtotime($stop);

            $tvGuide = $this->EntityManager->getRepository(TvGuide::class)->findOneBy(array('channel' => $guide->getAttribute('channel'), 'start' => $start));

            if (!$tvGuide) {
                $tvGuide = new TvGuide();
                $tvGuide->channel = $guide->getAttribute('channel');
                $tvGuide->start = $start;
                $tvGuide->stop = $stop;
                $tvGuide->name = $guideTitles[$i]->nodeValue;
                $this->EntityManager->persist($tvGuide);
            }
            ++$i;
        }

        $this->EntityManager->flush();

        $qb = $this->EntityManager->createQueryBuilder();
        $qb->delete('TvGuide', 's');
        $qb->where('s.stop < :stop');
        $qb->setParameter('stop', time());

        die (1);
    }
}
