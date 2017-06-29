<?php
namespace ytubes\components;

use Detection\MobileDetect;
use Jaybizzle\CrawlerDetect\CrawlerDetect;

class Visitor
{
	/**
	 * @var bool initialized class;
	 */
	private static $init = false;

	private static $mobileDetect = null;

	private static $crawlerDetect = null;


	private static $deviceType = null;

	private static $isMobile = null;

	private static $isTablet = null;

	private static $isCrawler = null;

    private static function init()
    {
    	if (self::$init === true)
    		return;

    	self::$mobileDetect = new MobileDetect;
    	self::$crawlerDetect = new CrawlerDetect;
    }

	public static function getIp()
	{
		return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;
	}

	public static function getUserAgent()
	{
		return isset($_SERVER['HTTP_USER_AGENT']) ? trim($_SERVER['HTTP_USER_AGENT']) : null;
	}

	public static function getReferer()
	{
		return isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
	}

	public static function getRefererHost()
	{
		return isset($_SERVER['HTTP_REFERER']) ? parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST) : null;
	}

	public static function proxyDetect()
	{
		$proxy_headers = [
	        'HTTP_VIA',
	        'HTTP_X_FORWARDED_FOR',
	        'HTTP_FORWARDED_FOR',
	        'HTTP_X_FORWARDED',
	        'HTTP_FORWARDED',
	        'HTTP_CLIENT_IP',
	        'HTTP_FORWARDED_FOR_IP',
	        'VIA',
	        'X_FORWARDED_FOR',
	        'FORWARDED_FOR',
	        'X_FORWARDED',
	        'FORWARDED',
	        'CLIENT_IP',
	        'FORWARDED_FOR_IP',
	        'HTTP_PROXY_CONNECTION'
	    ];

	    foreach($proxy_headers as $val){
	        if (isset($_SERVER[$val]))
	        	return true;
	    }

	    return false;
	}

	public static function getDeviceType()
	{
		self::init();

		if (self::$deviceType === null) {
			if (self::isMobile()) {
				self::$deviceType = 'mobile';
			} elseif (self::isTablet()) {
				self::$deviceType = 'tablet';
			} else {
				self::$deviceType = 'desktop';
			}
		}

		return self::$deviceType;
	}

	public static function isMobile()
	{
		self::init();

		if (self::$isMobile === null) {
			self::$isMobile = self::$mobileDetect->isMobile();
		}

		return self::$isMobile;
	}

	public static function isTablet()
	{
		self::init();

		if (self::$isTablet === null) {
			self::$isTablet = self::$mobileDetect->isTablet();
		}

		return self::$isTablet;
	}

	public static function isCrawler()
	{
		self::init();

		if (self::$isCrawler === null) {
			self::$isCrawler = self::$crawlerDetect->isCrawler();
		}

		return self::$isCrawler;
	}

	public static function getRefererType()
	{
		$se = new SERefererDetect;

		if (self::getReferer() === '') {
			return 'bookmark';
		} elseif (self::getRefererHost() === $_SERVER['HTTP_HOST']) {
			return 'internal';
		} elseif ($se->fromSE(self::getReferer())) {
			return 'se';
		} elseif (filter_var(self::getReferer(), FILTER_VALIDATE_URL)) {
			return 'links';
		} else {
			return 'other';
		}
	}

    /**
     * Returns the user's preferred language from the browser
     *
     * @return string|null the preferred language from the browser or NULL
     */
    public static function getBrowserLanguage()
    {
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            return locale_accept_from_http($_SERVER['HTTP_ACCEPT_LANGUAGE']);
        }

        return null;
    }
}