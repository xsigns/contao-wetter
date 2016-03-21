<?php
/**
 * config
 *
 * @copyright  xsigns GmbH & Co. KG 2014
 * @package    FewoVerwalter
 */
$GLOBALS['FE_MOD']['wettermodule'] = array('xs_wetter' => 'ModxsWetter');

$GLOBALS['BE_MOD']['wettermodule']['xs_wetter'] = array('tables' => array('tl_xs_wetter'), 'icon' => 'system/modules/Contao-Wetter/assets/wetter.png');
$GLOBALS['TL_CTE']['wettermodule']['xs_wetter'] = 'xsWetter';

