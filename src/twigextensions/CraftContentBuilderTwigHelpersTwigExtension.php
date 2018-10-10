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

}
