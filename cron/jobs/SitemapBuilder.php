<?php
namespace ytubes\cron\jobs;

use Yii;
use yii\db\Expression;
use yii\base\Model;
use yii\helpers\Url;
use yii\helpers\FileHelper;

use samdark\sitemap\Sitemap;
use samdark\sitemap\Index;

/**
 * https://github.com/samdark/sitemap
 */
class SitemapBuilder
{
    private $urlManager;
    private $index;
    private $baseSitemapUrl;
    private $baseDirectory;

    public function __construct()
    {
        $this->baseSitemapUrl = Yii::$app->settings->get('site_url') . '/sitemap/';
        $this->baseDirectory = Yii::getAlias('@frontend/web/sitemap');

        if (!is_dir($this->baseDirectory)) {
            FileHelper::CreateDirectory($this->baseDirectory, 0755);
        }

        $indexFilepath = $this->baseDirectory . '/index.xml';
        $this->index = new Index($indexFilepath);

        $siteUrl = Yii::$app->settings->get('site_url');
        $this->urlManager = Yii::$app->urlManager;
        $this->urlManager->setScriptUrl('/frontend/web/index.php');
        $this->urlManager->setHostInfo($siteUrl);

    }

    public function handle()
    {
        $this->build();
    }

    private function build()
    {
        if (Yii::$app->hasModule('videos')) {
            $this->addCategories();
            $this->addVideos();
        }

        $this->index->write();
    }

    private function addCategories()
    {
        $filepath = $this->baseDirectory . '/videos_categories.xml';
        $sitemap = new Sitemap($filepath);

        $models = \ytubes\videos\models\Category::find()
            ->select(['category_id', 'slug', 'updated_at']);

        foreach ($models->batch(1000) as $categories) {
            foreach ($categories as $category) {
                $sitemap->addItem($this->urlManager->createAbsoluteUrl(['/videos/category/index', 'slug' => $category->slug]), strtotime($category->updated_at), Sitemap::DAILY, 0.7);
            }
        }

        $sitemap->write();

        $sitemapFileUrls = $sitemap->getSitemapUrls($this->baseSitemapUrl);
        $this->addSitemapToIndex($sitemapFileUrls);
    }

    private function addVideos()
    {
        $filepath = $this->baseDirectory . '/videos.xml';
        $sitemap = new Sitemap($filepath);

        $models = \ytubes\videos\models\Video::find()
            ->select(['video_id', 'slug', 'published_at'])
            ->where(['status' => 10])
            ->orderBy(['published_at' => SORT_DESC]);

        foreach ($models->batch(1000) as $videos) {
            foreach ($videos as $video) {
                $sitemap->addItem($this->urlManager->createAbsoluteUrl(['/videos/view/index', 'slug' => $video->slug]), strtotime($video->published_at), Sitemap::DAILY, 0.5);
            }
        }

        $sitemap->write();

        $sitemapFileUrls = $sitemap->getSitemapUrls($this->baseSitemapUrl);
        $this->addSitemapToIndex($sitemapFileUrls);
    }

    private function addSitemapToIndex($sitemapFileUrls)
    {
        if (empty($sitemapFileUrls))
            return;

        foreach ($sitemapFileUrls as $sitemapUrl) {
            $this->index->addSitemap($sitemapUrl);
        }
    }
}
