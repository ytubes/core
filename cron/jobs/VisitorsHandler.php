<?php
namespace ytubes\cron\jobs;

use Yii;
use yii\db\Expression;
use yii\base\Model;
use yii\helpers\Url;
use yii\helpers\FileHelper;

/**
 * https://github.com/samdark/sitemap
 */
class VisitorsHandler extends \yii\base\Object
{

    public function handle()
    {
        $this->delete24HourOld();
    }

    private function delete24HourOld()
    {
			// -24 часа от текущего момента. в UTC формате
		$date_utc = new \DateTime('NOW', new \DateTimeZone('UTC'));
		$date_utc->sub(new \DateInterval('P1D'));
		$last_day = $date_utc->format('Y-m-d H:i:s');

		Yii::$app->db->createCommand()
			->delete('visitors', 'first_visit<TIMESTAMP(:last_day)')
			->bindValue(':last_day', $last_day)
			->execute();
    }
}
