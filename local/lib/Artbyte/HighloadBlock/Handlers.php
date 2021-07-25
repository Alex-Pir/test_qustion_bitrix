<?php
namespace Artbyte\HighloadBlock;

use Bitrix\Main\Entity\Event;

/**
 * Класс для обработки событий, связанных с highloadblock
 *
 * Class Handlers
 * @package Artbyte\HighloadBlock
 */
class Handlers {

    /** @var string тэг кеша */
    const CACHE_TAG = "artbyte_user_addresses";

    /**
     * Сброс кеша по тегу
     *
     * @param Event $event
     * @return bool
     */
    public static function hlOnAfterEdit(Event $event) {
        if (!defined("BX_COMP_MANAGED_CACHE")) {
            return true;
        }

        global $CACHE_MANAGER;
        $CACHE_MANAGER->ClearByTag(self::CACHE_TAG);
    }
}