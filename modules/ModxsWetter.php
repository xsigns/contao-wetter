<?php
namespace xsWetter;

use Contao\Database;

class ModxsWetter extends \xsWetter
{

    public function generate()
    {
        if (TL_MODE == 'BE') {
            $objT = new \BackendTemplate('be_wildcard');
            $objT->wildcard = '### WETTER ###';
            return ($objT->parse());
        }
        $this->strTemplate = 'xsWetter';
        return (parent::generate());
    }
}

?>