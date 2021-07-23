<?php

namespace Sprint\Migration;


class Version20210723232022 extends Version
{
    protected $description = "";

    protected $moduleVersion = "3.28.7";

    /**
     * @throws Exceptions\ExchangeException
     * @throws Exceptions\RestartException
     * @throws Exceptions\HelperException
     * @return bool|void
     */
    public function up()
    {
        $this->createHL()
             ->uploadElements();
    }

    protected function createHL() {
        $helper = $this->getHelperManager();
        $hlblockId = $helper->Hlblock()->saveHlblock(array(
            'NAME' => 'UserAddresses',
            'TABLE_NAME' => 'artbyte_hl_user_addresses',
            'LANG' =>
                array(
                    'ru' =>
                        array(
                            'NAME' => 'Адреса пользователя',
                        ),
                ),
        ));
        $helper->Hlblock()->saveField($hlblockId, array(
            'FIELD_NAME' => 'UF_USER_ID',
            'USER_TYPE_ID' => 'integer',
            'XML_ID' => 'user_addresses_user_id',
            'SORT' => '100',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'Y',
            'SHOW_FILTER' => 'N',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' =>
                array(
                    'SIZE' => 20,
                    'MIN_VALUE' => 0,
                    'MAX_VALUE' => 0,
                    'DEFAULT_VALUE' => '',
                ),
            'EDIT_FORM_LABEL' =>
                array(
                    'en' => '',
                    'ru' => 'Идентификатор пользователя',
                ),
            'LIST_COLUMN_LABEL' =>
                array(
                    'en' => '',
                    'ru' => 'Идентификатор пользователя',
                ),
            'LIST_FILTER_LABEL' =>
                array(
                    'en' => '',
                    'ru' => 'Идентификатор пользователя',
                ),
            'ERROR_MESSAGE' =>
                array(
                    'en' => '',
                    'ru' => '',
                ),
            'HELP_MESSAGE' =>
                array(
                    'en' => '',
                    'ru' => '',
                ),
        ));
        $helper->Hlblock()->saveField($hlblockId, array(
            'FIELD_NAME' => 'UF_USER_ADDRESS',
            'USER_TYPE_ID' => 'string',
            'XML_ID' => 'user_addresses_value',
            'SORT' => '100',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'Y',
            'SHOW_FILTER' => 'N',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' =>
                array(
                    'SIZE' => 20,
                    'ROWS' => 1,
                    'REGEXP' => '',
                    'MIN_LENGTH' => 0,
                    'MAX_LENGTH' => 0,
                    'DEFAULT_VALUE' => '',
                ),
            'EDIT_FORM_LABEL' =>
                array(
                    'en' => '',
                    'ru' => 'Адрес',
                ),
            'LIST_COLUMN_LABEL' =>
                array(
                    'en' => '',
                    'ru' => 'Адрес',
                ),
            'LIST_FILTER_LABEL' =>
                array(
                    'en' => '',
                    'ru' => 'Адрес',
                ),
            'ERROR_MESSAGE' =>
                array(
                    'en' => '',
                    'ru' => '',
                ),
            'HELP_MESSAGE' =>
                array(
                    'en' => '',
                    'ru' => '',
                ),
        ));
        $helper->Hlblock()->saveField($hlblockId, array(
            'FIELD_NAME' => 'UF_ACTIVE',
            'USER_TYPE_ID' => 'boolean',
            'XML_ID' => 'user_addresses_active',
            'SORT' => '100',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'N',
            'SHOW_FILTER' => 'N',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' =>
                array(
                    'DEFAULT_VALUE' => 0,
                    'DISPLAY' => 'CHECKBOX',
                    'LABEL' =>
                        array(
                            0 => '',
                            1 => '',
                        ),
                    'LABEL_CHECKBOX' => '',
                ),
            'EDIT_FORM_LABEL' =>
                array(
                    'en' => '',
                    'ru' => 'Активность',
                ),
            'LIST_COLUMN_LABEL' =>
                array(
                    'en' => '',
                    'ru' => 'Активность',
                ),
            'LIST_FILTER_LABEL' =>
                array(
                    'en' => '',
                    'ru' => 'Активность',
                ),
            'ERROR_MESSAGE' =>
                array(
                    'en' => '',
                    'ru' => '',
                ),
            'HELP_MESSAGE' =>
                array(
                    'en' => '',
                    'ru' => '',
                ),
        ));

        return $this;
    }

    protected function uploadElements() {
        $this->getExchangeManager()
            ->HlblockElementsImport()
            ->setExchangeResource('hlblock_elements.xml')
            ->setLimit(20)
            ->execute(function ($item) {
                $this->getHelperManager()
                    ->Hlblock()
                    ->addElement(
                        $item['hlblock_id'],
                        $item['fields']
                    );
            });
    }

    public function down()
    {
       $this->outError("Обратная операция не предусмотрена");
    }
}
