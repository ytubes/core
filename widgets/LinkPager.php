<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */
namespace ytubes\widgets;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\Widget;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;


/**
 * LinkPager displays a list of hyperlinks that lead to different pages of target.
 *
 * LinkPager works with a [[Pagination]] object which specifies the total number
 * of pages and the current page number.
 *
 * Note that LinkPager only generates the necessary HTML markups. In order for it
 * to look like a real pager, you should provide some CSS styles for it.
 * With the default configuration, LinkPager should look good using Twitter Bootstrap CSS framework.
 *
 * For more details and usage information on LinkPager, see the [guide article on pagination](guide:output-pagination).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class LinkPager extends \yii\widgets\LinkPager
{
    public $dotsLabel = false;
    public $dotsClass = 'dots';

    /**
     * @var string класс для тега li (каждой кнопки)
     */
    public $itemCssClass = 'pagination__item';
    /**
     * @var string класс для тега внутри li (каждой кнопки)
     */
    public $linkCssClass = 'pagination__link';

    /**
     * Renders the page buttons.
     * @return string the rendering result
     */
    protected function renderPageButtons()
    {
        $pageCount = $this->pagination->getPageCount();
        if ($pageCount < 2 && $this->hideOnSinglePage) {
            return '';
        }
        $buttons = [];
        $currentPage = $this->pagination->getPage();
        // first page
        $firstPageLabel = $this->firstPageLabel === true ? '1' : $this->firstPageLabel;
        if ($firstPageLabel !== false) {
            $buttons[] = $this->renderPageButton($firstPageLabel, 0, $this->firstPageCssClass, $currentPage <= 0, false);
        }
        // prev page
        if ($this->prevPageLabel !== false) {
            if (($page = $currentPage - 1) < 0) {
                $page = 0;
            }
            $buttons[] = $this->renderPageButton($this->prevPageLabel, $page, $this->prevPageCssClass, $currentPage <= 0, false);
        }
        // dots
        if (
        	$this->dotsLabel !== false &&
        	!empty($buttons) &&
        	$currentPage >= ceil($this->maxButtonCount / 2)
        ) {
        	$buttons[] = $this->renderPageButton($this->dotsLabel, 0, $this->dotsClass, true, false);
        }

        // internal pages
        list($beginPage, $endPage) = $this->getPageRange();
        	// высчитаем положение текущей кнопки
         $buttonsNum = 0;
        for ($i = $beginPage; $i <= $endPage; ++$i) {
        	if ($i === $currentPage) {
        		$currentPosition = $buttonsNum + 1;
        	}
        	$buttonsNum ++;
        }
        $deltaArray = $this->getDeltaIndexArray($buttonsNum, $currentPosition);

        $j = 0;
        for ($i = $beginPage; $i <= $endPage; ++$i) {
            $pageCssClass = $this->pageCssClass . ' delta' . $deltaArray[$j];
            $buttons[] = $this->renderPageButton($i + 1, $i, $pageCssClass, $this->disableCurrentPageButton && $i == $currentPage, $i == $currentPage);
            $j++;
        }

        // last page label (условие для генерации кнопки (последней страницы))
        $lastPageLabel = $this->lastPageLabel === true ? $pageCount : $this->lastPageLabel;

        // dots
        if (
        	$this->dotsLabel !== false &&
        	($this->nextPageLabel !== false || $lastPageLabel !== false) &&
        	$currentPage < ($pageCount - ceil($this->maxButtonCount / 2))
        ) {
        	$buttons[] = $this->renderPageButton($this->dotsLabel, 0, $this->dotsClass, true, false);
        }
        // next page
        if ($this->nextPageLabel !== false) {
            if (($page = $currentPage + 1) >= $pageCount - 1) {
                $page = $pageCount - 1;
            }
            $buttons[] = $this->renderPageButton($this->nextPageLabel, $page, $this->nextPageCssClass, $currentPage >= $pageCount - 1, false);
        }
        // last page (генерация кнопки)
        if ($lastPageLabel !== false) {
            $buttons[] = $this->renderPageButton($lastPageLabel, $pageCount - 1, $this->lastPageCssClass, $currentPage >= $pageCount - 1, false);
        }
        return Html::tag('ul', implode("\n", $buttons), $this->options);
    }

    /**
     * Renders a page button.
     * You may override this method to customize the generation of page buttons.
     * @param string $label the text label for the button
     * @param int $page the page number
     * @param string $class the CSS class for the page button.
     * @param bool $disabled whether this page button is disabled
     * @param bool $active whether this page button is active
     * @return string the rendering result
     */
    protected function renderPageButton($label, $page, $class, $disabled, $active)
    {
        $options = ['class' => $this->itemCssClass];
        Html::addCssClass($options, $class);
        $linkOptions = $this->linkOptions;

        if ($active) {
            Html::addCssClass($options, $this->activePageCssClass);
        }

        if ($disabled) {
            Html::addCssClass($options, $this->disabledPageCssClass);
            $tag = ArrayHelper::remove($this->disabledListItemSubTagOptions, 'tag', 'span');
			Html::addCssClass($linkOptions, $this->disabledListItemSubTagOptions);
            return Html::tag('li', Html::tag($tag, $label, $linkOptions), $options);
        }

        $linkOptions['data-page'] = $page;
        return Html::tag('li', Html::a($label, $this->pagination->createUrl($page), $linkOptions), $options);
    }

	/**
	 * Высчитывает массив значений для классов кнопок, расходящихся от текущего положения активной кнопки.
	 * @param int $total сколько всего кнопок в пагинашке.
	 * @param int $position текущая позиция активной кнопки
	 *
	 * @return array
	 */
    protected function getDeltaIndexArray($total, $position)
    {
			// левый и правый порядковые номера.
		$shortRight = [];
		$central = [0];
		$shortLeft = [];

			// Высчитаем левый и правые массивы по размеру.
		$rightLength = $total - $position;
		$leftLength = $total - $rightLength - 1;

			// Определим минимальный из них.
		$minVal = min($leftLength, $rightLength);

			// если кнопка не по центру, то создаем два массива. левый будет зеркальной версией правой.
		if ($minVal > 0) {
			$shortRight = range(1, $minVal);
			$shortLeft = array_reverse($shortRight);
			$central = array_merge($shortLeft, $central, $shortRight);
		}
			// определим количество оставшихся шагов для заполнения массива до макс. размера.
		$numSteps = $total - count($central);

			// Создадим массив с добавочными дублирущимися значениями дельты.
		$addititionalArray = [];
		for ($i = ($total - $numSteps); $i < $total; $i++) {
			$addititionalArray[] = ceil($i / 2);
		}

			// если кнопка находится в начале, то добавочный массив будет справа
		if ($leftLength < $rightLength) {
			$resultArr = array_merge($central, $addititionalArray);

			// если в конце, то добавочный массив слева.
		} else {
			$reversedAddititionalArray = array_reverse($addititionalArray);
			$resultArr = array_merge($reversedAddititionalArray, $central);
		}

		return $resultArr;
    }
}
