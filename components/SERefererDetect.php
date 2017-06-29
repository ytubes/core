<?php
namespace ytubes\components;

class SERefererDetect
{
	private $signatures = [
		'google',
		'yandex',
		'ya\.ru',
		'bing',
		'yahoo',
		'baidu',
		'ask\.com',
		'aol\.com',
		'duckduckgo',
		'wolframalpha',
		'webcrawler',
		'search\.com',
		'dogpile',
		'ixquick',
		'excite\.com',
		'info\.com',
		'infospace\.com',
		'nigma\.ru',
		'mail\.ru',
		'metabot\.ru',
		'webalta\.ru',
		'tut\.by',
	];

	public function getSignatures()
	{
		return $this->signatures;
	}

	public function fromSE($referer = null)
	{
		if (null === $referer) {
			$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
		}

		$compiledRegex = '(' . (implode('|', $this->signatures)) . ')';

		$result = preg_match('/' . $compiledRegex . '/is', $referer, $matches);

		return (bool) $result;
	}
}