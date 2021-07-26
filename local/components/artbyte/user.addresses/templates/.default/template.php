<?php
/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponentTemplate $this
 * @var string $templateName
 * @var string $componentPath
 * @var string $templateFolder
 */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

if(property_exists($this->getComponent(), "arResultCacheKeys")) {

    if(!is_array($this->getComponent()->arResultCacheKeys)) {
        $this->getComponent()->arResultCacheKeys = array();
    }

    $this->getComponent()->arResultCacheKeys[] = "arResult";
    $this->getComponent()->arResult["arResult"] = $arResult;
}