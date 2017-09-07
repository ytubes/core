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

		$compressor = new ImageCompressor('guetzli');
		$compressor
		    ->setOriginalFile($originalFile)
		    ->setDestination($this->outFilepath)
		    ->setQuality(90)
		    ->compress();
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
