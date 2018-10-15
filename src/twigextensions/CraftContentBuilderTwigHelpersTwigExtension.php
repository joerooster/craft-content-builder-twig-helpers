<?php
/**
 * Craft Content Builder Twig Helpers plugin for Craft CMS 3.x
 *
 * A collection of custom helpers and filters for a content builder
 *
 * @link      https://www.prometeusweb.com
 * @copyright Copyright (c) 2018 PrometeusWeb
 */

namespace prometeusweb\craftcontentbuildertwighelpers\twigextensions;

use craft\helpers\ElementHelper;
use prometeusweb\craftcontentbuildertwighelpers\CraftContentBuilderTwigHelpers;

use Craft;

/**
 * @author    PrometeusWeb
 * @package   CraftContentBuilderTwigHelpers
 * @since     1.0.0
 */
class CraftContentBuilderTwigHelpersTwigExtension extends \Twig_Extension
{
	/**
	 * @var array Contains the titles for the table of contents
	 */
	private $tableOfContents = [];

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'CraftContentBuilderTwigHelpers';
    }

    /**
     * @inheritdoc
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('slugify', [$this, 'slugify']),
            new \Twig_SimpleFilter('closeOpenHtmlTags', [$this, 'closeOpenHtmlTags'], ['is_safe' => ['html']]),
	        new \Twig_SimpleFilter('truncate', [$this, 'truncate'], array('needs_environment' => true)),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('slugify', [$this, 'slugify']),
            new \Twig_SimpleFunction('addToTableOfContents', [$this, 'addToTableOfContents']),
            new \Twig_SimpleFunction('getTableOfContents', [$this, 'getTableOfContents']),
            new \Twig_SimpleFunction('closeOpenHtmlTags', [$this, 'closeOpenHtmlTags'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('truncate', [$this, 'truncate'], ['needs_environment' => true]),
            new \Twig_SimpleFunction('ellipsis', [$this, 'ellipsis'], ['needs_environment' => true]),
        ];
    }

    /**
     * Transforms a string into a slugged string
     *
     * Eg: "hello! I'm a string" will be transformed into "hello-i-m-a-string"
     * 
     * @param null $string The string to be transformed in slug
     *
     * @return string
     */
    public function slugify(string $string = null): string
    {
        if($string){
        	return ElementHelper::createSlug(str_replace(['.'], '', $string));
        }

        throw new Exception("Missing text to be slugged");
    }

	/**
	 * Adds a title to the table of contents array
	 *
	 * @param string $title
	 * @param string $id
	 * @param string $size
	 */
	public function addToTableOfContents(string $title, string $id, string $size)
	{
		$this->tableOfContents[$size][] = [
			'id' => $id,
			'label' => $title
		];
    }

	/**
	 * Returns the table of contents
	 *
	 * @return array
	 */
	public function getTableOfContents()
	{
		return $this->tableOfContents;
    }


	/**
	 * @param string $string
	 *
	 * @return string
	 */
	public function closeOpenHtmlTags(string $string): string
	{
		libxml_use_internal_errors(true);
		$doc = new \DOMDocument();
//		$doc->substituteEntities = false;
		$doc->loadHTML('<?xml encoding="UTF-8">'.$string);
		$doc->removeChild($doc->doctype);
		$content = $doc->saveHTML();
//		$content = $doc->saveHTML($doc->getElementsByTagName('body')->item(0));
		$content = str_replace(['<html><body>', '</body></html>', '<?xml encoding="UTF-8">'], '', $content);
		return $content;
    }

	public function ellipsis(\Twig_Environment $env, string $string, $lenght = 30)
	{
		$blocks = $this->truncate($env, $string, $lenght, true, '...', true);
		$first = $this->closeOpenHtmlTags($blocks[0]);
		$second = $this->closeOpenHtmlTags($blocks[1]);

		$asd = explode('...', $first);



		preg_match_all('/(?<=\<\/)(.*?)(?=\>)/', $asd[1], $matches);

//		echo '<pre>'.($asd[1]).'</pre>';
//		echo '<pre>';
//		var_dump(array_reverse($matches[0]));
//		echo '</pre>';

		$st = "<".implode('><', array_reverse($matches[0])).">";

		return [$first, $this->closeOpenHtmlTags($st.$blocks[1])];

    }

	public function truncate(\Twig_Environment $env, $value, $length = 30, $preserve = false, $separator = '...', $returnBoth = false)
	{

		if (function_exists('mb_get_info')){
			return $this->twig_truncate_mb($env, $value, $length, $preserve, $separator, $returnBoth);
		}

		return $this->twig_truncate_std($env, $value, $length, $preserve, $separator, $returnBoth);
    }

	/** From https://github.com/twigphp/Twig-extensions/blob/master/lib/Twig/Extensions/Extension/Text.php */
	private function twig_truncate_mb(\Twig_Environment $env, $value, $length = 30, $preserve = false, $separator = '...', $returnBoth = false)
	{
		if (($string_lenght = mb_strlen($value, $env->getCharset())) > $length) {
			if ($preserve) {
				// If breakpoint is on the last word, return the value without separator.
				if (false === ($breakpoint = mb_strpos($value, ' ', $length, $env->getCharset()))) {
					return $value;
				}
				$length = $breakpoint;
			}
			if($returnBoth === false){
				return rtrim(mb_substr($value, 0, $length, $env->getCharset())).$separator;
			}
			else {
				return [
					rtrim(mb_substr($value, 0, $length, $env->getCharset())).$separator,
					ltrim(mb_substr($value, $length, $string_lenght, $env->getCharset()))
				];
			}

		}
		return $value;
    }

	/** From https://github.com/twigphp/Twig-extensions/blob/master/lib/Twig/Extensions/Extension/Text.php */
	private function twig_truncate_std(\Twig_Environment $env, $value, $length = 30, $preserve = false, $separator = '...', $returnBoth = false)
	{
		if (($str_length = strlen($value)) > $length) {
			if ($preserve) {
				if (false !== ($breakpoint = strpos($value, ' ', $length))) {
					$length = $breakpoint;
				}
			}
			if($returnBoth === false){
				return rtrim(substr($value, 0, $length)).$separator;
			}
			else {
				return [
					rtrim(substr($value, 0, $length)).$separator,
					ltrim(substr($value, $length, $str_length))
				];
			}

		}
		return $value;
    }
}
