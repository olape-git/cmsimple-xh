<?php

/**
 * jQuery for CMSimple
 *
 * Admin-interface for configuring the plugin
 * via the standard-functions of pluginloader.
 *
 * Version:    1.6.7
 * Build:      2024080501
 * Copyright:  Holger Irmler
 * Email:      CMSimple@HolgerIrmler.de
 * Website:    http://CMSimple.HolgerIrmler.de
 * Copyright:  CMSimple_XH developers
 * Website:    https://www.cmsimple-xh.org/?About-CMSimple_XH/The-XH-Team
 
 * */
 
$plugin_mcf['jquery']['version_core']="function:jquery_getCoreVersions";
$plugin_mcf['jquery']['version_ui']="function:jquery_getUiVersions";
$plugin_mcf['jquery']['version_migrate']="function:jquery_getMigrateVersions";
$plugin_mcf['jquery']['load_migrate']="bool";
$plugin_mcf['jquery']['autoload']="bool";
$plugin_mcf['jquery']['autoload_libraries']="enum:jQuery,jQuery & jQueryUI";
