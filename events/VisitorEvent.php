<?php
namespace ytubes\events;

use Yii;

use ytubes\components\Visitor;
use ytubes\models\Visitor as VisitorModel;

class VisitorEvent extends \yii\base\Object
{
    public static function onView($event)
    {
        $session = Yii::$app->session;
        $session->open();

        $visitTime = (new \DateTime())
            ->format('Y-m-d H:i:s');

        $limitAgo = (new \DateTime()) // sinceLastVisit
            ->sub(new \DateInterval('PT7200S'));

        $lastVisit = $session->get('last_visit', false);

        $firstVisit = false;
        if ($lastVisit === false) {
            $firstVisit = true;
        } else {
            $lastVisit = (new \DateTime($lastVisit));

                // Если с момента последнего визита прошло более чем полчаса, то будем считать новый уник.
            if ($lastVisit < $limitAgo) {
                $firstVisit = true;
                // Если с момента последнего клика прошло меньше 10 секунд, значит это бот
            } else {
                $lastClick = $session->get('last_click', false);
                $lastClick = (new \DateTime($lastClick));
                $fastClick = (new \DateTime())
                    ->sub(new \DateInterval('PT3S'));

                if ($lastClick !== false && $lastClick > $fastClick) {
                    return;
                }
            }
        }

        $ip = inet_pton(Visitor::getIp());
        $ref_group = Visitor::getRefererType();

        if ($firstVisit === true) {
            $device_group = Visitor::getDeviceType();
            $ref_site = Visitor::getRefererHost();
            if (!$ref_site) { // заплатка
                $ref_site = '';
            }

            $sql = "
                INSERT INTO `visitors` (`ip`, `first_visit`, `last_visit`, `session_time`, `raw_in`, `views`, `clicks`, `ref_site`, `ref_group`, `device_group`)
                VALUES (:ip, :first_visit, NULL, 0, 1, 1, 0, :ref_site, :ref_group, :device_group)";
            Yii::$app->db->createCommand($sql) // Переписать запрос с подготовленными выражениями. '{$ip}'
                ->bindValue(':ip', $ip)
                ->bindValue(':first_visit', $visitTime)
                ->bindValue(':ref_site', $ref_site)
                ->bindValue(':ref_group', $ref_group)
                ->bindValue(':device_group', $device_group)
                ->execute();

            $session->set('last_visit', $visitTime);
        } else {
                // Если переход на сайт извне, то считаем повторный вход
            $raw_in = 0;

            if ($ref_group !== 'internal') {
                $raw_in = 1;
            }

                // Время обновления берем из сессии + текуший ип
            $timestamp = $lastVisit->format('Y-m-d H:i:s');

            $sql = "
                UPDATE `visitors`
                SET `last_visit`=:last_visit, `session_time`=TIMESTAMPDIFF(SECOND,`first_visit`,`last_visit`), `raw_in`=`raw_in`+:raw_in, `views`=`views`+1
                WHERE `ip`=:ip AND `first_visit`=TIMESTAMP(:timestamp)"; // '{$timestamp}'
            Yii::$app->db->createCommand($sql)
                ->bindValue(':last_visit', $visitTime)
                ->bindValue(':raw_in', $raw_in)
                ->bindValue(':ip', $ip)
                ->bindValue(':timestamp', $timestamp)
                ->execute();
        }

        $session->set('last_click', $visitTime);
    }

    public static function onClick($event)
    {
        $session = Yii::$app->session;
        $session->open();

        $visitTime = (new \DateTime())
            ->format('Y-m-d H:i:s');

        $limitAgo = (new \DateTime())
            ->sub(new \DateInterval('PT7200S'));

        $lastVisit = $session->get('last_visit', false);

        $firstVisit = false;
        if ($lastVisit === false) {
            $firstVisit = true;
        } else {
            $lastVisit = (new \DateTime($lastVisit));

                // Если с момента последнего визита прошло более чем полчаса, то будем считать новый уник.
            if ($lastVisit < $limitAgo) {
                $firstVisit = true;
            } else {
                $lastClick = $session->get('last_click', false);
                $lastClick = (new \DateTime($lastClick));
                $fastClick = (new \DateTime())
                    ->sub(new \DateInterval('PT3S'));

                if ($lastClick !== false && $lastClick > $fastClick) {
                    return;
                }
            }
        }

        $ip = inet_pton(Visitor::getIp());
        $ref_group = Visitor::getRefererType();

        if ($firstVisit === true) {
            $device_group = Visitor::getDeviceType();
            $ref_site = Visitor::getRefererHost();
            if (!$ref_site) { // заплатка
                $ref_site = '';
            }

            $sql = "
                INSERT INTO `visitors` (`ip`, `first_visit`, `last_visit`, `session_time`, `raw_in`, `views`, `clicks`, `ref_site`, `ref_group`, `device_group`)
                VALUES (:ip, :first_visit, NULL, 0, 1, 1, 0, :ref_site, :ref_group, :device_group)";
            Yii::$app->db->createCommand($sql)
                ->bindValue(':ip', $ip)
                ->bindValue(':first_visit', $visitTime)
                ->bindValue(':ref_site', $ref_site)
                ->bindValue(':ref_group', $ref_group)
                ->bindValue(':device_group', $device_group)
                ->execute();

            $session->set('last_visit', $visitTime);
        } else {
                // Если переход на сайт извне, то считаем повторный вход
            $raw_in = 0;
            $click = 0;

            if ($ref_group !== 'internal') {
                $raw_in = 1;
            } else { // Если это внутренний переход, то считаем клик дополнительно.
                $click = 1;
            }

                // Время обновления берем из сессии + текуший ип
            $timestamp = $lastVisit->format('Y-m-d H:i:s');

            $sql = "
                UPDATE `visitors`
                SET `last_visit`=:last_visit, `session_time`=TIMESTAMPDIFF(SECOND,`first_visit`,`last_visit`), `raw_in`=`raw_in`+:raw_in, `views`=`views`+1, `clicks`=`clicks`+:click
                WHERE `ip`=:ip AND `first_visit`=TIMESTAMP(:timestamp)";
            Yii::$app->db->createCommand($sql)
                ->bindValue(':last_visit', $visitTime)
                ->bindValue(':raw_in', $raw_in)
                ->bindValue(':click', $click)
                ->bindValue(':ip', $ip)
                ->bindValue(':timestamp', $timestamp)
                ->execute();
        }

        $session->set('last_click', $visitTime);
    }
}
