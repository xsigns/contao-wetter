<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2013 Leo Feyer
 *
 * @package FewoVerwalter
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */


/**
 * Register the namespaces
 */
ClassLoader::addNamespaces(array
(
    'xsWetter'
));


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
    // Elements
    'xsWetter\xsWetter' => 'system/modules/Contao-Wetter/elements/xsWetter.php',

    // Modules
    'xsWetter\ModxsWetter' => 'system/modules/Contao-Wetter/modules/ModxsWetter.php'
    //Sonstiges
));

/**
 * Register the templates
 */
TemplateLoader::addFiles(
    array
    (
        'xsWetter' => 'system/modules/Contao-Wetter/templates',
    ));

?>