<?php
// Register on http://kunden.dwd.de/gdsRegistration/gdsRegistrationStart.do

namespace xsWetter;

use Contao\Database;

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
            $cssFile = \FilesModel::findById($res->wetter_design);
            if (file_exists($cssFile->path))
                $GLOBALS['TL_USER_CSS']['xswetter'] = $cssFile->path;
            else
                $GLOBALS['TL_USER_CSS']['xswetter'] = 'system/modules/xsWetter/assets/default.css';
            $this->datenladen($this->xs_wetter_id, $res->wetter_key, $res->wetter_ort, $res->wetter_tage);
            $datenfile = "files/xsWetter/wetter_" . $this->xs_wetter_id . ".xml";
            if (file_exists($datenfile)) {
                $xml = simplexml_load_file($datenfile, 'SimpleXMLElement');
                $arrWetter = array();
                $arrHeute = array();
                $this->Template->wetter_sonnezeit = ($res->wetter_sonnezeit == 0 ? false : true);
                $this->Template->wetter_symbol = ($res->wetter_symbol == 0 ? false : true);
                $this->Template->wetter_tempminmax = ($res->wetter_tempminmax == 0 ? false : true);
                $this->Template->wetter_wind = ($res->wetter_wind == 0 ? false : true);
                $this->Template->wetter_luftfeucht = ($res->wetter_luftfeucht == 0 ? false : true);
                $this->Template->wetter_luftdruck = ($res->wetter_luftdruck == 0 ? false : true);

                $ort = (string)$xml->location->name;
                foreach ($xml->sun as $dat) {
                    $heutesun1 = date("H:m", strtotime((string)$dat['rise']));
                    $heutesun2 = date("H:m", strtotime((string)$dat['set']));
                }
                $zaehler =0;
                foreach ($xml->forecast->time as $daydat) {
                    if (date("H", strtotime((string)$daydat['from'])) == 12) { // Lese nur die 12 Uhr Werte ein
                        $zaehler ++;
                        if($zaehler == 1) // Heute
                        {
                            $arrHeute['ort']= $ort;
                            $arrHeute['vom'] = $GLOBALS['TL_LANG']['xsWetter']['tage'][date("w", strtotime((string)$daydat['from']))] . " " . date("d.m.Y", strtotime((string)$daydat['from']));
                            $arrHeute['icon'] = 'system/modules/xsWetter/assets/' . (string)$daydat->symbol['var'] . '.png';
                            $arrHeute['temp'] = number_format((double)$daydat->temperature['value'], '1', ',', '');
                            $arrHeute['tempmin'] = number_format((double)$daydat->temperature['min'], '1', ',', '');
                            $arrHeute['tempmax'] = number_format((double)$daydat->temperature['max'], '1', ',', '');
                            $arrHeute['wind'] = number_format((double)$daydat->windSpeed['mps'] * 3.6, '1', ',', '');
                            $arrHeute['windrichtung'] = (string)$daydat->windDirection['code'];
                            $arrHeute['luft'] = (string)$daydat->humidity['value'];
                            $arrHeute['druck'] = (string)$daydat->pressure['value'];
                            $arrHeute['sonnevon'] = $heutesun1;
                            $arrHeute['sonnebis'] = $heutesun2;

                        }else { // Weitere Tage
                            $arrWetter[] = array(
                                "vom" => $GLOBALS['TL_LANG']['xsWetter']['tage'][date("w", strtotime((string)$daydat['from']))] . " " . date("d.m.Y", strtotime((string)$daydat['from'])),
                                "icon" => 'system/modules/xsWetter/assets/' . (string)$daydat->symbol['var'] . '.png',
                                "temp" => number_format((double)$daydat->temperature['value'], '1', ',', ''),
                                "tempmin" => number_format((double)$daydat->temperature['min'], '1', ',', ''),
                                "tempmax" => number_format((double)$daydat->temperature['max'], '1', ',', ''),
                                "wind" => number_format((double)$daydat->windSpeed['mps'] * 3.6, '1', ',', ''),
                                "windrichtung" => (string)$daydat->windDirection['code'],
                                "luft" => (string)$daydat->humidity['value'],
                                "druck" => (string)$daydat->pressure['value']
                            );
                        }
                    }
                }
                $this->Template->heute = $arrHeute;
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
            $zeitaktuel = filemtime($datenfile);
            if (date("d.m.Y", $zeitaktuel) != date("d.m.Y", time()))
                $this->holeDaten($id, $ort, $key, $tage);
        }
    }

    protected function holeDaten($id, $ort, $key, $tage)
    {
        try {
            if ($tage > 1)
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
                return;
            }
            if(strpos($data,"Not found")) {
                \System::log('Error WetterModul : Not found', __METHOD__, TL_GENERAL);
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