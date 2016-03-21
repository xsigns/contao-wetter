<?php
$GLOBALS['TL_DCA']['tl_content']['palettes']['xs_wetter'] = '{title_legend},name,type;{label_wetterglobal},xs_wetter_id;{expert_legend:hide}guests,cssID,space;';
$GLOBALS['TL_DCA']['tl_content']['fields']['xs_wetter_id'] = array('label' => &$GLOBALS['TL_LANG']['tl_content']['xs_wetter_id'], 'inputType' => 'select', 'options_callback' => array('tl_content_wetter', 'holeWetter'), 'eval' => array('mandatory' => true, 'tl_class' => 'long'), 'sql' => "int(10) unsigned NOT NULL default '0'");


class tl_content_wetter extends \Backend {

    public function holeWetter()
    {
        $res = \Database::GetInstance()->prepare("SELECT id,wetter_titel FROM tl_xs_wetter")->execute();
        $arrElem = array();
        while ($res->next()) {
            if (trim($res->wetter_titel) == '') continue;
            $arrElem[$res->id] = $res->wetter_titel;
        }
        return ($arrElem);
    }
}
?>