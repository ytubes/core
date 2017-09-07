<?php
namespace ytubes\queue\job;

use Yii;
use yii\base\Object;
use yii\helpers\FileHelper;
use yii\queue\Job;
use ImageCompressor\ImageCompressor;
use SplFileInfo;

class CompressImageJob extends Object implements Job
{
    public $inFilepath;
    public $outFilepath;
    public $quality = 90;


    public function execute($queue)
    {
        $originalFile = new \SplFileInfo($this->inFilepath);

        $destinationDirectory = dirname($this->outFilepath);
        if (!is_dir($destinationDirectory)) {
            FileHelper::createDirectory($destinationDirectory, 0755);
        }

        try {
            $driver = new \ImageCompressor\Driver\Guetzli('/usr/sbin/guetzli');

            $compressor = new ImageCompressor($driver);
            $compressor
                ->setOriginalFile($originalFile)
                ->setDestination($this->outFilepath)
                ->setQuality(90)
                ->compress();
        } catch (\Exception $e) {
            $logPath = Yii::getAlias('@runtime/logs/queue.log');
            file_put_contents($logPath, $e->getMessage(), FILE_APPEND | LOCK_EX);
        }
    }

    /**
     * @inheritdoc
     */
    public function getTtr()
    {
        return 600;
    }

    /**
     * @inheritdoc
     */
    public function canRetry($attempt, $error)
    {
        return $attempt < 3;
    }
}
