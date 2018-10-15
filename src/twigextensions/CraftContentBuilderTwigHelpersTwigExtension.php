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
use prometeusweb\craftcontentbuildertwighelpers\services\TruncateService;

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
	        new \Twig_SimpleFilter('ellipsis', [$this, 'ellipsis'], ['needs_environment' => true, 'is_safe' => ['all']]),
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
            new \Twig_SimpleFunction('ellipsis', [$this, 'ellipsis'], ['needs_environment' => true, 'is_safe' => ['all']]),
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
	 * Truncates the string by words or by chars
	 * 
	 * @param \Twig_Environment $env
	 * @param string|null       $string
	 * @param null              $lenght
	 * @param array|null        $settings
	 *      + stripTags (bool, optional - default: false) - If the string should be stripped from html tags
	 *      + fixClosingTags (bool, optional - default: false)
	 *          If the string should be fixed from unclosed html tags.
	 *          If stripTags is true, then fixClosingTags has not meaning so it becomes false
	 *      + type (string, required, 'chars' | 'words')
	 *          The type of truncation, if by characters (chars) or by words (words)
	 *      + preserve (bool, optional, default: true)
	 *          If truncating by chars, it defines if the last word integrity should be mantained
	 *      + separator (string, optional, default: '...')
	 *          If the string lenght is greater than the limit, a separator will
	 *          be added at the end of the truncated string
	 *      + useSeparator (bool, optional, default: true)
	 *          Enable or disable separator for truncated strings
	 *
	 * @return string
	 */
	public function ellipsis(\Twig_Environment $env, string $string = null, $lenght = null, array $settings = null)
	{
		$stripTags = isset($settings['stripTags']) && is_bool($settings['stripTags']) ? $settings['stripTags'] : false;

		$fixClosingTags = true;

		if($stripTags) {
			$fixClosingTags = false;
		}

		if(isset($settings['fixClosingTags']) && $settings['fixClosingTags'] === false ){
			$fixClosingTags = false;
		}

		$options = [
			'type' => isset($settings['type']) && in_array(strtolower($settings['type']), ['chars', 'words']) ? strtolower($settings['type']) : 'chars',
			'preserve' => isset($settings['preserve']) && is_bool($settings['preserve']) ? $settings['preserve'] : true,
			'fixClosingTags' => $fixClosingTags,
			'separator' => $settings['separator'] ?? '...',
			'useSeparator' => isset($settings['separator']) && is_bool($settings['separator']) ? $settings['separator'] : true,
			'stripTags' => $stripTags
		];

		$truncateService = new TruncateService();

		return $truncateService->truncate($env, $string, $lenght, $options);

    }

}
