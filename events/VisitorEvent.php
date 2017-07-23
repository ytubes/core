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

		$limitAgo = (new \DateTime())
			->sub(new \DateInterval('PT1800S'));

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

			$sql = "
				INSERT INTO `visitors` (`ip`, `first_visit`, `last_visit`, `session_time`, `raw_in`, `views`, `clicks`, `ref_site`, `ref_group`, `device_group`)
				VALUES ('{$ip}', '{$visitTime}', NULL, 0, 1, 1, 0, '{$ref_site}', '{$ref_group}', '{$device_group}')";
			Yii::$app->db->createCommand($sql)
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
				SET `last_visit`='{$visitTime}', `session_time`=TIMESTAMPDIFF(SECOND,`first_visit`,`last_visit`), `raw_in`=`raw_in`+{$raw_in}, `views`=`views`+1
				WHERE `ip`='{$ip}' AND `first_visit`=TIMESTAMP('{$timestamp}')";
			Yii::$app->db->createCommand($sql)
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
			->sub(new \DateInterval('PT1800S'));

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

			$sql = "
				INSERT INTO `visitors` (`ip`, `first_visit`, `last_visit`, `session_time`, `raw_in`, `views`, `clicks`, `ref_site`, `ref_group`, `device_group`)
				VALUES ('{$ip}', '{$visitTime}', NULL, 0, 1, 1, 0, '{$ref_site}', '{$ref_group}', '{$device_group}')";
			Yii::$app->db->createCommand($sql)
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
				SET `last_visit`='{$visitTime}', `session_time`=TIMESTAMPDIFF(SECOND,`first_visit`,`last_visit`), `raw_in`=`raw_in`+{$raw_in}, `views`=`views`+1, `clicks`=`clicks`+{$click}
				WHERE `ip`='{$ip}' AND `first_visit`=TIMESTAMP('{$timestamp}')";
			Yii::$app->db->createCommand($sql)
				->execute();
		}

		$session->set('last_click', $visitTime);
    }
}
