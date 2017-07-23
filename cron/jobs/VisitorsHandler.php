<?php
namespace ytubes\cron\jobs;

use Yii;
use yii\db\Expression;
use yii\base\Model;
use yii\helpers\Url;
use yii\helpers\FileHelper;
use ytubes\models\Visitor;
use yii\support\facades\Db;

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
		$last_day = (new \DateTime('NOW'))
			->sub(new \DateInterval('P1D'))
			->format('Y-m-d H:i:s');

		Db::createCommand()
			->delete(Visitor::tableName(), 'first_visit<TIMESTAMP(:last_day)')
			->bindValue(':last_day', $last_day)
			->execute();
    }
}
