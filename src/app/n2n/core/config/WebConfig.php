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
 * Bert Hofmänner.......: Idea, Frontend UI, Community Leader, Marketing
 * Thomas Günther.......: Developer, Hangar
 */
namespace n2n\core\config;

use n2n\web\http\Supersystem;
use n2n\util\type\ArgUtils;
use n2n\web\http\Subsystem;
use n2n\web\http\controller\ControllerDef;
use n2n\l10n\N2nLocale;

class WebConfig {
	private $responseCachingEnabled;
	private $responseBrowserCachingEnabled; 
	private $responseSendEtagAllowed;
	private $responseSendLastModifiedAllowed; 
	private $responseServerPushAllowed;
	private $viewCachingEnabled; 
	private $viewClassNames; 
	private $mainControllerDefs; 
	private $filterControllerDefs; 
	private $supersystem;
	private $subsystems;
	private $dispatchPropertyProviderClassNames;
	private $dispatchTargetCryptAlgorithm;
	private $aliasN2nLocales;
	
	/**
	 * @param bool $responseCachingEnabled
	 * @param bool $responseBrowserCachingEnabled
	 * @param bool $responseSendEtagAllowed
	 * @param bool $responseSendLastModifiedAllowed
	 * @param bool $responseServerPushAllowed
	 * @param bool $viewCachingEnabled
	 * @param string[] $viewClassNames
	 * @param ControllerDef[] $mainControllerDefs
	 * @param ControllerDef[] $filterControllerDefs
	 * @param Supersystem $supersystem
	 * @param Supersystem[] $subsystems
	 * @param string[] $dispatchPropertyProviderClassNames
	 * @param string $dispatchTargetCryptAlgorithm
	 * @param N2nLocale[] $aliasN2nLocales
	 */
	public function __construct(bool $responseCachingEnabled, bool $responseBrowserCachingEnabled, bool $responseSendEtagAllowed, 
			bool $responseServerPushAllowed, bool $responseSendLastModifiedAllowed, bool $viewCachingEnabled, array $viewClassNames, array $mainControllerDefs,
			array $filterControllerDefs, Supersystem $supersystem, array $subsystems, array $dispatchPropertyProviderClassNames,
			string $dispatchTargetCryptAlgorithm = null, array $aliasN2nLocales) {
		ArgUtils::valArray($subsystems, Subsystem::class);
				
		$this->responseCachingEnabled = $responseCachingEnabled;
		$this->responseBrowserCachingEnabled = $responseBrowserCachingEnabled;
		$this->responseSendEtagAllowed = $responseSendEtagAllowed;
		$this->responseSendLastModifiedAllowed = $responseSendLastModifiedAllowed;
		$this->responseServerPushAllowed = $responseServerPushAllowed;
		$this->viewCachingEnabled = $viewCachingEnabled;
		$this->viewClassNames = $viewClassNames;
		$this->mainControllerDefs = $mainControllerDefs;
		$this->filterControllerDefs = $filterControllerDefs;
		$this->supersystem = $supersystem;
		$this->subsystems = $subsystems;
		$this->dispatchPropertyProviderClassNames = $dispatchPropertyProviderClassNames;
		$this->dispatchTargetCryptAlgorithm = $dispatchTargetCryptAlgorithm;
		$this->aliasN2nLocales = $aliasN2nLocales;
	}
	
	/**
	 * @return boolean
	 */
	public function isResponseCachingEnabled(): bool {
		return $this->responseCachingEnabled;
	}
	
	/**
	 * @return boolean
	 */
	public function isResponseBrowserCachingEnabled(): bool {
		return $this->responseBrowserCachingEnabled;
	}
	
	/**
	 * @return boolean
	 */
	public function isResponseSendEtagAllowed(): bool {
		return $this->responseSendEtagAllowed;
	}
	
	/**
	 * @return boolean
	 */
	public function isResponseSendLastModifiedAllowed(): bool {
		return $this->responseSendLastModifiedAllowed;
	}
		
	/**
	* @return boolean
	*/
	public function isResponseServerPushAllowed(): bool {
		return $this->responseServerPushAllowed;
	}
	
	/**
	 * @return boolean
	 */
	public function isViewCachingEnabled(): bool {
		return $this->viewCachingEnabled;
	}
	
	/**
	 * @return string[]
	 */
	public function getViewClassNames(): array {
		return $this->viewClassNames;
	}
	
	/**
	 * @return ControllerDef[]
	 */
	public function getMainControllerDefs(): array {
		return $this->mainControllerDefs;
	}
	
	/**
	 * @return ControllerDef[]
	 */
	public function getFilterControllerDefs(): array {
		return $this->filterControllerDefs;
	}
	
	/**
	 * @return \n2n\l10n\N2nLocale[]
	 */
	public function getAllN2nLocales(): array {
		$n2nLocales = $this->supersystem->getN2nLocales();
		foreach ($this->subsystems as $supersystem) {
			$n2nLocales = array_merge($n2nLocales, $supersystem->getN2nLocales());
		}
		return $n2nLocales;
	}
	
	/**
	 * @return Supersystem
	 */
	public function getSupersystem() {
		return $this->supersystem;
	}
	
	/**
	 * @return string[]
	 */
	public function getSubsystemNames() {
		return array_keys($this->subsystems);
	}
	
	/**
	 * @return Subsystem[]
	 */
	public function getSubsystems(): array {
		return $this->subsystems;
	}
	
	/**
	 * @return string[] 
	 */
	public function getDispatchPropertyProviderClassNames(): array {
		return $this->dispatchPropertyProviderClassNames;
	}
	
	/**
	 * @return string
	 */
	public function getDispatchTargetCryptAlgorithm() {
		return $this->dispatchTargetCryptAlgorithm;
	}
	
	/**
	 * @return N2nLocale[] 
	 */
	public function getAliasN2nLocales() {
		return $this->aliasN2nLocales;
	}
}
