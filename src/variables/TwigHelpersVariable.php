<?php
/**
 * Bcc Fogli Informativi plugin for Craft CMS 3.x
 *
 * Takes a custom excel file with descriptions and file paths of pdf files and renders to html with caching
 *
 * @link      https://www.prometeusweb.com
 * @copyright Copyright (c) 2018 PrometeusWeb
 */

namespace prometeusweb\craftcontentbuildertwighelpers\variables;

use prometeusweb\craftcontentbuildertwighelpers\CraftContentBuilderTwigHelpers;

use Craft;

/**
 * @author    PrometeusWeb
 * @package   CraftContentBuilderTwigHelpers
 * @since     1.0.0
 */
class TwigHelpersVariable
{
    // Public Methods
    // =========================================================================

	/**
	 * @param       $macro
	 * @param array $args
	 *
	 * @return mixed
	 */
	public function renderFormMacro($macro, array $args)
	{
		Craft::$app->view->getTemplatesPath();

		Craft::$app->view->renderTemplate()



		// Get the current template path
		$originalPath = Craft::$app->getViewPath()->path->getTemplatesPath();

		// Point Twig at the CP templates
		craft()->path->setTemplatesPath(craft()->path->getCpTemplatesPath());

		// Render the macro.
		$html = craft()->templates->renderMacro('_includes/forms', $macro, array($args));

		// Restore the original template path
		craft()->path->setTemplatesPath($originalPath);

		return TemplateHelper::getRaw($html);
	}

}
