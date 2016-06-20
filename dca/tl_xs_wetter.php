<?php

$GLOBALS['TL_DCA']['tl_xs_wetter'] = array(
    'config' => array(
        'dataContainer' => 'Table',
        'switchToEdit' => false,
        'enableVersioning' => true,
        'sql' => array
        (
            'keys' => array
            (
                'id' => 'primary'
            )
        ),

    ),
    'list' => array
    (

        'label' => array(
            'fields' => array('wetter_titel'),
            'format' => '%s'
        ),
        'global_operations' => array
        (
            'all' => array
            (
                'label' => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset();"'
            )
        ),
        'operations' => array
        (
            'edit' => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_xs_wetter']['edit'],
                'href' => 'act=edit',
                'icon' => 'edit.gif'
            ),
            'copy' => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_xs_wetter']['copy'],
                'href' => 'act=copy',
                'icon' => 'copy.gif'
            ),
            'delete' => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_xs_wetter']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.gif',
                'attributes' => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
            )
        )
    ),

    // Palettes
    'palettes' => array(
        'default' => '{title_legend},name,type;{label_wetterglobal},wetter_titel,wetter_design,wetter_key,wetter_ort;
        {label_wettereinstellung},wetter_tage,wetter_sonnezeit,wetter_symbol,wetter_tempminmax,wetter_wind,wetter_luftfeucht,wetter_luftdruck'
    ),

    'fields' => array(
        'id' => array('sql' => "int(10) unsigned NOT NULL auto_increment"),
        'tstamp' => array('sql' => "int(10) unsigned NOT NULL default '0'"),
        'sorting' => array('sql' => "int(10) unsigned NOT NULL default '0'"),
        'wetter_titel' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_xs_wetter']['wetter_titel'],
            'inputType' => 'text',
            'eval' => array('mandatory' => true, 'maxlength' => 250, 'tl_class' => 'long'),
            'sql' => "varchar(250) NOT NULL default ''"
        ),
        'wetter_design' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_xs_wetter']['wetter_design'],
            'inputType' => 'fileTree',
            'eval' => array('filesOnly' => true, 'files' => true, 'fieldType' => 'radio', 'extension' => 'css'),
            'sql' => "binary(16) NULL"
        ),
        'wetter_key' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_xs_wetter']['wetter_key'],
            'inputType' => 'text',
            'eval' => array('maxlength' => 100, 'tl_class' => 'w50', 'mandatory' => true),
            'sql' => "varchar(100) NOT NULL default ''"
        ),
        'wetter_ort' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_xs_wetter']['wetter_ort'],
            'inputType' => 'text',
            'eval' => array('maxlength' => 120, 'tl_class' => 'w50', 'mandatory' => true),
            'sql' => "varchar(120) NOT NULL default ''"
        ),

        'wetter_tage' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_xs_wetter']['wetter_tage'],
            'inputType' => 'select',
            'options' => array('1', '2','3','4','5'),
            'default' => '1',
            'eval' => array( 'tl_class' => 'clr'),
            'sql' => "int(1) unsigned NOT NULL default '3'",
            'save_callback' => array(array('tl_xs_wetter','tl_xs_wetter_delete'))
        ),
        'wetter_sonnezeit' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_xs_wetter']['wetter_sonnezeit'],
            'inputType' => 'checkbox',
            'eval' => array( 'tl_class' => 'w50'),
            'sql' => "int(1) unsigned NOT NULL default '0'"
        ),
        'wetter_symbol' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_xs_wetter']['wetter_symbol'],
            'inputType' => 'checkbox',
            'eval' => array( 'tl_class' => 'w50'),
            'sql' => "int(1) unsigned NOT NULL default '0'"
        ),
        'wetter_tempminmax' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_xs_wetter']['wetter_tempminmax'],
            'inputType' => 'checkbox',
            'eval' => array( 'tl_class' => 'w50'),
            'sql' => "int(1) unsigned NOT NULL default '0'"
        ),
        'wetter_wind' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_xs_wetter']['wetter_wind'],
            'inputType' => 'checkbox',
            'eval' => array( 'tl_class' => 'w50'),
            'sql' => "int(1) unsigned NOT NULL default '0'"
        ),
        'wetter_luftfeucht' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_xs_wetter']['wetter_luftfeucht'],
            'inputType' => 'checkbox',
            'eval' => array('tl_class' => 'w50'),
            'sql' => "int(1) unsigned NOT NULL default '0'"
        ),
        'wetter_luftdruck' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_xs_wetter']['wetter_luftdruck'],
            'inputType' => 'checkbox',
            'eval' => array( 'tl_class' => 'w50'),
            'sql' => "int(1) unsigned NOT NULL default '0'"
        )
    )
);
class tl_xs_wetter extends \Backend{

        public function tl_xs_wetter_delete($fields)
        {
            $objFolder = new \Folder('files/xswetter');
            $objFolder->delete();
            return $fields;
        }
}
?>
