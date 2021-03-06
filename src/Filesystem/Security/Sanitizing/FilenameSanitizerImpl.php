<?php

namespace ILIAS\Filesystem\Security\Sanitizing;

use ilException;
use ilFileUtils;

/**
 * Class FilenameSanitizerImpl
 *
 * Standard implementation of the filename sanitizing interface.
 *
 * @package ILIAS\Filesystem\Security\Sanitizising
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @version 1.0.0
 * @since 5.3.4
 */
final class FilenameSanitizerImpl implements FilenameSanitizer {

	/**
	 * Contains the whitelisted file suffixes.
	 *
	 * @var string[] $whitelist
	 */
	private $whitelist;


	/**
	 * FilenameSanitizerImpl constructor.
	 */
	public function __construct() {
		$this->whitelist = ilFileUtils::getValidExtensions();

		// the secure file ending must be valid, therefore add it if it got removed from the white list.
		if(!in_array(FilenameSanitizer::CLEAN_FILE_SUFFIX, $this->whitelist, true))
			array_push($this->whitelist, FilenameSanitizer::CLEAN_FILE_SUFFIX);
	}


	/**
	 * @inheritDoc
	 */
	public function isClean($filename) {
		$this->validateFilename($filename);
		return in_array($this->extractFileSuffix($filename), $this->whitelist, true);
	}


	/**
	 * @inheritDoc
	 */
	public function sanitize($filename) {
		$this->validateFilename($filename);
		if($this->isClean($filename))
			return $filename;

		$pathInfo = pathinfo($filename);
		$basename = $pathInfo['basename'];
		$parentPath = $pathInfo['dirname'];


		$filename = str_replace('.', '', $basename);
		$filename .= "." . FilenameSanitizer::CLEAN_FILE_SUFFIX;

		// there is no parent
		if($parentPath === '')
			return $filename;

		return "$parentPath/$filename";
	}


	/**
	 * Extracts the suffix from the given filename.
	 * If no suffix was found an empty string will be returned.
	 *
	 * @param string $filename The filename which should be used to extract the file suffix.
	 * @return string The file name suffix in lowercase.
	 */
	private function extractFileSuffix($filename) {
		return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
	}


	/**
	 * Validates that the filename is valid for further sanitizing.
	 *
	 * @param string $filename The filename which should get validated before sanitizing.
	 *
	 * @throws ilException Thrown if the filename is not considered valid for sanitizing.
	 */
	private function validateFilename($filename) {
		if($filename === NULL)
			throw new ilException("Filename must not be null to get sanitized!");
	}
}