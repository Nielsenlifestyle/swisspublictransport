<?php

namespace Transport\Entity\Schedule;

use Transport\Entity\Location\Station;
use Transport\Entity\Query;
use Transport\Entity\Transportations;

class StationBoardQuery extends Query
{
    /**
     * @var Station
     */
    public $station;

    public $boardType = 'departure';

    public $maxJourneys = 40;

    public $date;

    public $transportations = ['all'];

    public function __construct(Station $station, \DateTime $date = null)
    {
        $this->station = $station;

        if (!($date instanceof \DateTime)) {
            $date = new \DateTime('now', new \DateTimeZone('Europe/Zurich'));
        }
        $this->date = $date;
    }

    public function toXml()
    {
        $request = $this->createRequest();

        $board = $request->addChild('STBReq');

        if ($this->boardType === 'arrival') {
            $boardType = 'ARR';
        } else {
            $boardType = 'DEP';
        }
        $board->addAttribute('boardType', $boardType);

        $board->addAttribute('maxJourneys', $this->maxJourneys);
        $board->addChild('Time', $this->date->format('H:i'));

        $period = $board->addChild('Period');
        $dateBegin = $period->addChild('DateBegin');
        $dateBegin->addChild('Date', $this->date->format('Ymd'));
        $dateEnd = $period->addChild('DateEnd');
        $dateEnd->addChild('Date', $this->date->format('Ymd'));

        $tableStation = $board->addChild('TableStation');
        $tableStation->addAttribute('externalId', $this->station->id);
        $board->addChild('ProductFilter', Transportations::reduceTransportations($this->transportations));

        return $request->asXML();
    }
}
