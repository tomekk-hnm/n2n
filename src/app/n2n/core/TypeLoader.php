<?php
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the N2N FRAMEWORK.
 *
 * The N2N FRAMEWORK is free software: you can redistribute it and/or modify it under the terms of
 * the GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * N2N is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg.....: Architect, Lead Developer
 * Bert Hofmänner.......: Idea, Community Leader, Marketing
 * Thomas Günther.......: Developer, Hangar
 */
namespace n2n\core;

use n2n\N2N;
use n2n\io\IoUtils;
use n2n\util\StringUtils;
use n2n\reflection\ReflectionUtils;
use n2n\core\err\ExceptionHandler;

class TypeLoader {
	const SCRIPT_FILE_EXTENSION = '.php';
	
	private static $includePaths;
	private static $exceptionHandler;
	private static $latestException = null;
	
	public static function getIncludePaths() {
		return self::$includePaths;
	}
	/**
	 * 
	 * @param string $includePath
	 * @param string $moduleIncludePath
	 * @param ExceptionHandler $exceptionHandler
	 */
	public static function register($includePath, ExceptionHandler $exceptionHandler) {
		// @todo check if include paths are valid
		self::$includePaths = explode(PATH_SEPARATOR, $includePath);
		self::$exceptionHandler = $exceptionHandler;

		spl_autoload_register('n2n\\core\\TypeLoader::load', true);
	}
	/**
	 * 
	 * @param string $typeName
	 * @throws TypeLoaderErrorException
	 */
	public static function load($typeName) {
		if (!self::$includePaths) return false;
		
		try {
			self::requireScript(self::getFilePathOfTypeWihtoutCheck($typeName), $typeName);
			return true;
		} catch (TypeNotFoundException $e) {
			$lutp = N2N::getLastUserTracePoint();
			self::$latestException = new TypeLoaderErrorException($typeName, $e->getMessage(), 0, 
					E_ERROR, $lutp['file'], $lutp['line']);
			return false;
		} /*catch (\Exception $e) {
			self::$exceptionHandler->handleThrowable($e);
			die();
		}*/
		return false;
	}
	/**
	 * 
	 * @param unknown_type $typeName
	 * @throws TypeNotFoundException
	 * @return \ReflectionClass
	 */
	public static function loadType($typeName) {
		return self::requireScript(self::getFilePathOfType($typeName), $typeName);
	}
	
	public static function isTypeLoaded($typeName) {
		$typeName = (string) $typeName;
		return class_exists($typeName, false) || interface_exists($typeName, false) 
				|| (function_exists('trait_exists') && trait_exists($typeName, false)); 
	}
	/**
	 *
	 * @param unknown_type $typeName
	 * @throws TypeNotFoundException
	 */
	public static function ensureTypeIsLoaded($typeName) {
		if (self::isTypeLoaded($typeName)) return;
		self::loadType($typeName);
	}
	
	public static function loadScript($scriptPath) {
		$scriptPath = IoUtils::realpath((string) $scriptPath);
		return self::requireScript($scriptPath, str_replace(DIRECTORY_SEPARATOR, '\\', 
				mb_substr(trim(self::removeIncludePathOfFilePath($scriptPath), DIRECTORY_SEPARATOR), 0, -strlen(self::SCRIPT_FILE_EXTENSION))));
	}
	
	public static function pathToTypeName($scriptPath) {
		return ReflectionUtils::qualifyTypeName(mb_substr(
				trim(self::removeIncludePathOfFilePath($scriptPath), DIRECTORY_SEPARATOR), 
				0, -strlen(self::SCRIPT_FILE_EXTENSION)));
	}
	
	private static function requireScript($scriptPath, $typeName) {
		require_once $scriptPath;
		
		if (!self::isTypeLoaded($typeName)) {
			throw new TypeLoaderErrorException($typeName, 'Missing type \'' . $typeName . '\' in file: '
					. $scriptPath, 0, E_USER_ERROR, $scriptPath);
		}
		
		$class = new \ReflectionClass($typeName);
// @todo file casesensitive		
//		if ($class->getFileName() != $scriptPath) {
//			throw new TypeLoaderErrorException($typeName, SysTextUtils::get('n2n_error_core_missing_type_in_file',
//					array('type' => $typeName, 'file' => $scriptPath)), 0, E_USER_ERROR, $scriptPath);
//		}
		
		return $class;
	}
	/**
	 * 
	 * @param string $namespace
	 * @throws TypeLoaderErrorException
	 * @return array
	 */
	public static function getNamespaceDirPaths($namespace) {
		if (ReflectionUtils::hasSpecialChars($namespace, false)) {
			throw new \InvalidArgumentException('Namespace contains invalid characters: ' . $namespace);
		}
		
		$dirPaths = array();
		foreach (self::$includePaths as $includePath) {
			$path = $includePath . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $namespace);
			
			if (!is_dir($path)) continue;
			if (!is_readable($path)) {
				throw new TypeLoaderErrorException($namespace, 'Can not access directory: ' . $path);
			}
				
			$dirPaths[] = $path;
		}
		
		return $dirPaths;
	}
	
	public static function isTypeUnsafe($typeName) {
		return false !== strpos($typeName, '/') || preg_match('#(^|\\\\)\\.{1,2}(\\\\|$)#', $typeName)
				|| 0 == mb_strlen($typeName) || mb_substr($typeName, 0, 1) == '\\' 
				|| mb_substr($typeName, -1) == '\\';
	}
	
	public static function doesTypeExist($typeName, $fileExt = self::SCRIPT_FILE_EXTENSION) {
		// @todo do check without exception
		try {
			self::getFilePathOfType($typeName);
			return true;
		} catch (TypeNotFoundException $e) {
			return false;
		}
	}
	
	public static function getFilePathOfType($typeName, $fileExt = self::SCRIPT_FILE_EXTENSION) {
		if (self::isTypeUnsafe($typeName)) {
			throw new \InvalidArgumentException('Type name contains invalid characters: ' . $typeName);
		}
				
		return self::getFilePathOfTypeWihtoutCheck($typeName, $fileExt);
	}
	
	public static function namespaceOfTypeName($typeName) {
		$lastPos = strrpos($typeName, '\\');
		if (false === $lastPos) return null;
		return mb_substr($typeName, 0, $lastPos);
	}
	/**
	 * 
	 * @param string $typeName
	 * @param string $fileExt
	 * @throws TypeLoaderErrorException
	 * @throws TypeNotFoundException
	 * @return string
	 */
	private static function getFilePathOfTypeWihtoutCheck($typeName, $fileExt = self::SCRIPT_FILE_EXTENSION) {
		$typeName = (string) $typeName;
		$relativeFilePath = str_replace('\\', DIRECTORY_SEPARATOR, $typeName) . $fileExt;
		$searchPaths = array();
		foreach (self::$includePaths as $includePath) {
			$path = $includePath . DIRECTORY_SEPARATOR . $relativeFilePath;
			$searchPaths[] = $path;
			
			if (!is_file($path)) continue;
			if (!is_readable($path)) {
				throw new TypeLoaderErrorException($typeName, 'Can not access file: ' . $path);
			}
			
			return $path;
		}
		
		throw new TypeNotFoundException('Type \'' . $typeName . '\' not found. Paths:' 
				. implode(PATH_SEPARATOR, $searchPaths));
	}
	/**
	 * 
	 * @return TypeLoaderErrorException
	 */
	public static function getLatestException() {
		return self::$latestException;
	}
	
	public static function clear() {
		self::$latestException = null;
	}
	
	public static function isFilePartOfNamespace($filePath, $namepsace) {
		foreach (self::$includePaths as $includePath) {
			$path = $includePath . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, (string) $namepsace);
			if (StringUtils::startsWith($path, $filePath)) return true;
		}
		
		return false;
	}	
	
	public static function removeIncludePathOfFilePath($filePath) {
		foreach (self::$includePaths as $includePath) {
			if (!StringUtils::startsWith($includePath, $filePath)) continue;
			return mb_substr($filePath, mb_strlen($includePath));
		}
		
		throw new FileIsNotPartOfIncludePathException('File path is not part of a include path: '
				. $filePath);
	}
}

class TypeLoaderErrorException extends \ErrorException {
	private $typeName;
	
	public function __construct($typeName, $message, $code, $severity, $errFilePath = null, $errLineNo = null) {
		parent::__construct((string) $message, $code, $severity, $errFilePath, $errLineNo);
		
		$this->typeName = $typeName;
	}
	
	public function getTypeName(): string {
		return $this->typeName;
	}
}

class TypeNotFoundException extends N2nRuntimeException {
	
}

class FileIsNotPartOfIncludePathException extends N2nRuntimeException {
	
}
