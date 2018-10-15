<?php

namespace prometeusweb\craftcontentbuildertwighelpers\services;

use craft\base\Component;

class TruncateService extends Component
{
	private $settings = [];
	private $env;

	public function truncate(\Twig_Environment $env, $value = '', $length = 30, Array $settings)
	{
		$this->env = $env;
		$this->settings = $settings;

		if($settings['stripTags']){
			$value = strip_tags($value);
		}

		$truncateFunction = 'truncateBy' . ucfirst($settings['type']);
		$truncatedString = $this->$truncateFunction($value, $length);

		if($settings['fixClosingTags']){
			return $this->closeOpenHtmlTags($truncatedString);
		}

		return $truncatedString;
	}


	/**
	 * From: https://stackoverflow.com/questions/12444945/cut-the-content-after-10-words
	 *
	 * @param $string
	 * @param $your_desired_width
	 *
	 * @return string
	 */
	function truncateByWords($string, $wordCount) {
		$separator = $this->settings['useSeparator'] ? $this->settings['separator'] : '';

		return implode(
			'',
			array_slice(
				preg_split(
					'/([\s,\.;\?\!]+)/u',
					$string,
					$wordCount*2+1,
					PREG_SPLIT_DELIM_CAPTURE
				),
				0,
				$wordCount*2-1
			)
		).$separator;
	}

	private function truncateByChars($value, $lenght)
	{
		if (function_exists('mb_get_info')){
			return $this->twig_truncate_chars_mb($value, $lenght);
		}

		return $this->twig_truncate_chars_std($value, $lenght);
	}

	/** From https://github.com/twigphp/Twig-extensions/blob/master/lib/Twig/Extensions/Extension/Text.php */
	private function twig_truncate_chars_mb($value, $length)
	{
		if (($string_lenght = mb_strlen($value, $this->env->getCharset())) > $length) {
			$separator = $this->settings['useSeparator'] ? $this->settings['separator'] : '';

			if ($this->settings['preserve']) {
				// If breakpoint is on the last word, return the value without separator.
				if (false === ($breakpoint = mb_strpos($value, ' ', $length, $this->env->getCharset()))) {
					return $value;
				}
				$length = $breakpoint;
			}

			return rtrim(mb_substr($value, 0, $length, $this->env->getCharset())) . $separator;

		}
		return $value;
	}

	/** From https://github.com/twigphp/Twig-extensions/blob/master/lib/Twig/Extensions/Extension/Text.php */
	private function twig_truncate_chars_std($value, $length)
	{
		if (($str_length = strlen($value)) > $length) {

			$separator = $this->settings['useSeparator'] ? $this->settings['separator'] : '';

			if ($this->settings['preserve']) {
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

	/**
	 * @param string $string
	 *
	 * @return string
	 */
	public function closeOpenHtmlTags(string $string): string
	{
		libxml_use_internal_errors(true);
		$doc = new \DOMDocument();
		$doc->loadHTML('<?xml encoding="UTF-8">'.$string);
		$doc->removeChild($doc->doctype);
		$content = $doc->saveHTML();
//		$content = $doc->saveHTML($doc->getElementsByTagName('body')->item(0));
		$content = str_replace(['<html><body>', '</body></html>', '<?xml encoding="UTF-8">'], '', $content);
		return $content;
	}

	function substr_sentence($string, $start=0, $limit=10, $max_char = 600)
	{
		/* This functions cuts a long string in sentences.
		*
		* substr_sentence($string, $start, $limit);
		* $string = 'A example. By someone that loves PHP. Do you? We do!';
		* $start = 0; // we would start at the beginning
		* $limit = 10; // so, we get 10 sentences (not 10 words or characters!)
		*
		* It's not as substr()) in single characters.
		* It's not as substr_words() in single words.
		*
		* No more broken lines in a story. The story/article must go on!
		*
		* Written by Eddy Erkelens "Zunflappie"
		* Published on www.mastercode.nl
		* May be free used and adapted
		*
		*/

		// list of sentences-ends. All sentences ends with one of these. For PHP, add the ;
		$end_characters = array(
			'. ',
			'? ',
			'! '
		);

		// put $string in array $parts, necessary evil
		$parts = array($string);

		// foreach interpunctation-mark we will do this loop
		foreach($end_characters as $end_character)
		{
			// go thru each part of the sentences we already have
			foreach($parts as $part)
			{
				// make array with the new sentences
				$sentences[] = explode($end_character, $part);
			}

			// unfortunately explode() removes the end character itself. So, place it back
			foreach($sentences as $sentence)
			{
				// some strange stuff
				foreach($sentence as $real_sentence)
				{
					// empty sentence we do not want
					if($real_sentence != '')
					{
						// if there is already an end-character, dont place another one
						if(in_array(substr($real_sentence, -1, 1), $end_characters))
						{
							// store for next round
							$next[] = trim($real_sentence);
						}
						else
						{
							// store for next round and add the removed character
							$next[] = trim($real_sentence).$end_character;
						}
					}
				}
			}

			// store for next round
			$parts = $next;

			// unset the remaining and useless stuff
			unset($sentences, $sentence, $next);
		}

		// check for max-char-length
		$total_chars = 0;
		$sentence_nr = 0;
		$sentences = array();

		// walk thru each member of $part
		foreach($parts as $part)
		{
			// count the string-lenght and add this to $total_chars
			$total_chars += strlen($part);

			// if $total-chars not already higher then max-char, add this sentences!
			if($total_chars < $max_char)
			{
				$sentences[] = $part;
			}
		}

		// return the shortened story as a string
		return implode(" ", array_slice($sentences, $start, $limit));
	}

}