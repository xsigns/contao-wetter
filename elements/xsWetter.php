<?php

namespace xsWetter;

class xsWetter extends \ContentElement
{
    protected $strTemplate = 'xsWetter';

    public function generate()
    {
        $this->loadLanguageFile('tl_content');
        if (TL_MODE == 'BE') {
            $res = \Database::getInstance()->prepare("SELECT wetter_titel FROM tl_xs_wetter WHERE id=" . $this->xs_wetter_id)->execute();
            $objT = new \BackendTemplate('be_wildcard');
            $objT->wildcard = '### WETTER ###<br><br><b>' . $res->wetter_titel .'</b>';
            return ($objT->parse());
        }
        global $objPage;
        $this->arrLocal = array('id' => $objPage->id, 'alias' => $objPage->alias);
        return (parent::generate());
    }

    protected function compile()
    {
        setlocale(LC_TIME,  "de_DE.utf8");
        date_default_timezone_set("Europe/Berlin");
        try {

            $res = \Database::getInstance()->prepare("SELECT * FROM tl_xs_wetter WHERE id=" . $this->xs_wetter_id)->execute();
            if ($res->numRows < 1)
                return;
            $tage = $res->wetter_tage;
            $cssFile = \FilesModel::findById($res->wetter_design);
            if (file_exists($cssFile->path))
                $GLOBALS['TL_USER_CSS']['xswetter'] = $cssFile->path;
            else
                $GLOBALS['TL_USER_CSS']['xswetter'] = 'system/modules/xsWetter/assets/default.css';
            $this->datenladen($this->xs_wetter_id, $res->wetter_key, $res->wetter_ort, $res->wetter_tage);
            $datenfile = "files/xswetter/wetter_" . $this->xs_wetter_id . ".xml";
            $jetztZeit = date("H:i",time());
            if (file_exists($datenfile)) {
                $xml = simplexml_load_file($datenfile, 'SimpleXMLElement');
                $arrWetter = array();
                $this->Template->wetter_sonnezeit = ($res->wetter_sonnezeit == 0 ? false : true);
                $this->Template->wetter_symbol = ($res->wetter_symbol == 0 ? false : true);
                $this->Template->wetter_tempminmax = ($res->wetter_tempminmax == 0 ? false : true);
                $this->Template->wetter_wind = ($res->wetter_wind == 0 ? false : true);
                $this->Template->wetter_luftfeucht = ($res->wetter_luftfeucht == 0 ? false : true);
                $this->Template->wetter_luftdruck = ($res->wetter_luftdruck == 0 ? false : true);
                $this->Template->wetter_ort = (string)$xml->location->name;
                $tagHeute = date("z",time());
                $tageJetzt =0;
                foreach ($xml->forecast->time as $daydat) {
                    if ($jetztZeit  >= date("H:i", strtotime((string)$daydat['from'])) && $jetztZeit <= date("H:i", strtotime((string)$daydat['to'])) && $tagHeute == date('z',strtotime((string)$daydat['from'])) && $tageJetzt==0) {
                        $tageJetzt =1;
                        $arrWetter[] = array(
                            'vom' => $GLOBALS['TL_LANG']['xsWetter']['tag'][date("w", strtotime((string)$daydat['from']))] . ' ' . date('d.m.Y', strtotime((string)$daydat['from'])),
                            'zeit' => sprintf($GLOBALS['TL_LANG']['xsWetter']['zeit'],date("H:i", strtotime((string)$daydat['from'])),date("H:i", strtotime((string)$daydat['to']))),
                            'icon' => 'system/modules/xsWetter/assets/' . (string)$daydat->symbol['var'] . '.png',
                            'temp' => number_format((double)$daydat->temperature['value'], '1', ',', ''),
                            'tempmin' => number_format((double)$daydat->temperature['min'], '1', ',', ''),
                            'tempmax' => number_format((double)$daydat->temperature['max'], '1', ',', ''),
                            'wind' => number_format((double)$daydat->windSpeed['mps'] * 3.6, '1', ',', ''),
                            'windrichtung' => (string)$daydat->windDirection['code'],
                            'luft' => (string)$daydat->humidity['value'],
                            'druck' => (string)$daydat->pressure['value']
                        );
                    }
                    if($tagHeute +$tageJetzt == date("z",strtotime((string)$daydat['from'])) && date("H", strtotime((string)$daydat['from'])) == 12)
                    { // Weitere Tage
                            $arrWetter[] = array(
                                'vom' => $GLOBALS['TL_LANG']['xsWetter']['tag'][date("w", strtotime((string)$daydat['from']))] . ' ' . date('d.m.Y', strtotime((string)$daydat['from'])),
                                'zeit' => sprintf($GLOBALS['TL_LANG']['xsWetter']['zeit'],date("H:i", strtotime((string)$daydat['from'])),date("H:i", strtotime((string)$daydat['to']))),
                                'icon' => 'system/modules/xsWetter/assets/' . (string)$daydat->symbol['var'] . '.png',
                                'temp' => number_format((double)$daydat->temperature['value'], '1', ',', ''),
                                'tempmin' => number_format((double)$daydat->temperature['min'], '1', ',', ''),
                                'tempmax' => number_format((double)$daydat->temperature['max'], '1', ',', ''),
                                'wind' => number_format((double)$daydat->windSpeed['mps'] * 3.6, '1', ',', ''),
                                'windrichtung' => (string)$daydat->windDirection['code'],
                                'luft' => (string)$daydat->humidity['value'],
                                'druck' => (string)$daydat->pressure['value']
                            );
                    }
                    if(count($arrWetter) == $tage) {
                        break;
                    }
                }
                $this->Template->wetter = $arrWetter;
            }else
                $this->Template->error = $GLOBALS['TL_LANG']['xsWetter']['error'];
        } catch (Exception $e) {
            \System::log('Error WetterModul', __METHOD__, TL_GENERAL);
        }
    }
    protected function datenladen($id, $key, $ort, $tage)
    {
        $objFolder = new \Folder('files/xswetter');
        $datenfile = "files/xswetter/wetter_" . $id . ".xml";
        if (!file_exists($datenfile))
            $this->holeDaten($id, $ort, $key, $tage);
        if (file_exists($datenfile)) {
            $xml = simplexml_load_file($datenfile, 'SimpleXMLElement');
            $zaehler = 0;
            foreach ($xml->forecast->time as $daydat) {
                $zaehler++;
                if ($zaehler == 1) {
                    $vonZeit = date("d.m.Y", strtotime((string)$daydat['from']));
                    break;
                }
            }
            if($vonZeit != date("d.m.Y",time()))
                $this->holeDaten($id, $ort, $key, $tage);
        }
    }

    protected function holeDaten($id, $ort, $key, $tage)
    {
        try {

                $tage = $tage * 8;
            if(is_numeric($ort))
                $url = 'http://api.openweathermap.org/data/2.5/forecast/city?id=' . $ort . '&units=metric&cnt=' . $tage . '&mode=xml&APPID=' . $key;
            else
                $url = 'http://api.openweathermap.org/data/2.5/forecast/city?q=' . $ort . '&units=metric&cnt=' . $tage . '&mode=xml&APPID=' . $key;
            $ch = curl_init();
            $options = array(CURLOPT_URL => $url,
                CURLOPT_PORT => "80",
                CURLOPT_RETURNTRANSFER => TRUE
            );
            curl_setopt_array($ch, $options);
            $data = curl_exec($ch);
            curl_close($ch);
            if(strpos($data,"Bad Request")> 0) {
                \System::log('Error WetterModul : Bad Request', __METHOD__, TL_GENERAL);
                $objFolder = new \Folder('files/xswetter');
                $objFolder->delete();
                return;
            }
            if(strpos($data,"Invalid")) {
                \System::log('Error WetterModul : Invalid Api key', __METHOD__, TL_GENERAL);
                $objFolder = new \Folder('files/xswetter');
                $objFolder->delete();
                return;
            }
            if(strpos($data,"Not found")) {
                \System::log('Error WetterModul : Not found', __METHOD__, TL_GENERAL);
                $objFolder = new \Folder('files/xswetter');
                $objFolder->delete();
                return;
            }
            $writexml = '<?xml version="1.0" encoding="utf-8"?>' . $data;
            $objFile = new \File("files/xswetter/wetter_" . $id . ".xml");
            $objFile->write($writexml);
            $objFile->close();

        } catch (Exception $e) {
            \System::log('Error WetterModul', __METHOD__, TL_GENERAL);
        }
    }
}

?>