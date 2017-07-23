<?php
namespace ytubes\cron\jobs;

use Yii;
use yii\db\Expression;
use yii\base\Model;
use yii\helpers\Url;
use yii\helpers\FileHelper;
use ytubes\models\Visitor;

/**
 * Очищение статы пользователей более чем за сутки
 */
class VisitorsHandler
{

    public function handle()
    {
        $this->delete24HourOld();
    }

    private function delete24HourOld()
    {
            // -24 часа от текущего момента.
        $last_day = (new \DateTime('NOW'))
            ->sub(new \DateInterval('P1D'))
            ->format('Y-m-d H:i:s');

        Yii::$app->db->createCommand()
            ->delete(Visitor::tableName(), 'first_visit<TIMESTAMP(:last_day)')
            ->bindValue(':last_day', $last_day)
            ->execute();
    }
}
