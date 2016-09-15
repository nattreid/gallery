<?php

namespace NAttreid\Gallery\Lang;

/**
 * Translator
 *
 * @author Attreid <attreid@gmail.com>
 */
class Translator implements \Nette\Localization\ITranslator
{

	private $translations;

	/**
	 * Nastavi jazyk
	 * @param string $lang
	 * @throws \InvalidArgumentException
	 */
	public function setLang($lang)
	{
		if (!$this->translations = @include(__DIR__ . "/Translator.php")) {
			throw new \InvalidArgumentException("Translations for language '$lang' not found.");
		}
	}

	private function getTranslations()
	{
		if ($this->translations === NULL) {
			$this->setLang('cs');
		}
		return $this->translations;
	}

	public function translate($message, $count = NULL)
	{
		$translations = $this->getTranslations();
		return isset($translations[$message]) ? $translations[$message] : $message;
	}

}
