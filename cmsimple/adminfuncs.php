<?php

/**
 * Admin only functions.
 *
 * PHP version 5
 *
 * @category  CMSimple_XH
 * @package   XH
 * @author    Peter Harteg <peter@harteg.dk>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 1999-2009 Peter Harteg
 * @copyright 2009-2015 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://cmsimple-xh.org/
 */

/**
 * Returns the readable version of a plugin.
 *
 * @param string $plugin Name of a plugin.
 *
 * @return string
 *
 * @global array The paths of system files and folders.
 *
 * @since 1.6
 */
function XH_pluginVersion($plugin)
{
    global $pth;

    $internalPlugins = array(
        'filebrowser', 'meta_tags', 'page_params', 'tinymce'
    );
    if (in_array($plugin, $internalPlugins)) {
        $version = 'for ' . CMSIMPLE_XH_VERSION;
    } else {
        $filename = $pth['folder']['plugins'] . $plugin . '/version.nfo';
        if (is_readable($filename)) {
            $contents = file_get_contents($filename);
            $contents = explode(',', $contents);
            $version = $contents[2];
        } else {
            $version = '';
        }
    }
    return $version;
}

/**
 * Returns the result view of the system check.
 *
 * @param array $data The data ;)
 *
 * @global array The paths of system files and folders.
 * @global array The localization of the core.
 *
 * @return string HTML
 *
 * @link http://www.cmsimple-xh.org/wiki/doku.php/plugin_interfaces#system_check
 *
 * @since 1.5.4
 */
function XH_systemCheck(array $data)
{
    global $pth, $tx;

    $stx = $tx['syscheck'];

    foreach (array('success', 'warning', 'fail') as $img) {
        $txt = $stx[$img];
        $imgs[$img] = '<img src="' . $pth['folder']['corestyle'] . $img . '.png"'
            . ' alt="' . $txt . '" title="' . $txt . '" width="16" height="16">';
    }

    $o = "<h4>$stx[title]</h4>\n<ul id=\"xh_system_check\">\n";

    if (key_exists('phpversion', $data)) {
        $ok = version_compare(PHP_VERSION, $data['phpversion']) >= 0;
        $o .= '<li>' . $imgs[$ok ? 'success' : 'fail']
            . sprintf($stx['phpversion'], $data['phpversion']) . "</li>\n";
    }

    if (key_exists('extensions', $data)) {
        $cat = ' class="xh_system_check_cat_start"';
        foreach ($data['extensions'] as $ext) {
            if (is_array($ext)) {
                $notok = $ext[1] ? 'fail' : 'warning';
                $ext = $ext[0];
            } else {
                $notok = 'fail';
            }
            $o .= '<li' . $cat . '>'
                . $imgs[extension_loaded($ext) ? 'success' : $notok]
                . sprintf($stx['extension'], $ext) . "</li>\n";
            $cat = '';
        }
    }

    if (key_exists('writable', $data)) {
        $cat = ' class="xh_system_check_cat_start"';
        foreach ($data['writable'] as $file) {
            if (is_array($file)) {
                $notok = $file[1] ? 'fail' : 'warning';
                $file = $file[0];
            } else {
                $notok = 'warning';
            }
            $o .= '<li' . $cat . '>' . $imgs[is_writable($file) ? 'success' : $notok]
                . sprintf($stx['writable'], $file) . "</li>\n";
            $cat = '';
        }
    }

    if (key_exists('other', $data)) {
        $cat = ' class="xh_system_check_cat_start"';
        foreach ($data['other'] as $check) {
            $notok = $check[1] ? 'fail' : 'warning';
            $o .= '<li' . $cat . '>' . $imgs[$check[0] ? 'success' : $notok]
                . $check[2] . "</li>\n";
            $cat = '';
        }
    }

    $o .= "</ul>\n";

    return $o;
}

/**
 * Returns the normalized absolute URL path.
 *
 * @param string $path A relative path.
 *
 * @return string
 *
 * @global string The script name.
 *
 * @since 1.6.1
 */
function XH_absoluteUrlPath($path)
{
    global $sn;

    $base = preg_replace('/index\.php$/', '', $sn);
    $parts = explode('/', $base . $path);
    $i = 0;
    while ($i < count($parts)) {
        switch ($parts[$i]) {
        case '.':
            array_splice($parts, $i, 1);
            break;
        case '..':
            array_splice($parts, $i - 1, 2);
            $i--;
            break;
        default:
            $i++;
        }
    }
    $path = implode('/', $parts);
    return $path;
}

/**
 * Returns whether a resource is access protected.
 *
 * @param string $path A normalized absolute URL path.
 *
 * @return bool
 *
 * @global string The script name.
 *
 * @since 1.6.1
 */
function XH_isAccessProtected($path)
{
    global $sn;

    $host = $_SERVER['HTTP_HOST'];
    $errno = $errstr = null;
    $stream = fsockopen($host, $_SERVER['SERVER_PORT'], $errno, $errstr, 5);
    if ($stream) {
        stream_set_timeout($stream, 5);
        $request = "HEAD  $sn$path HTTP/1.1\r\nHost: $host\r\n"
            . "User-Agent: CMSimple_XH\r\n\r\n";
        fwrite($stream, $request);
        $response = fread($stream, 12);
        fclose($stream);
        $status = substr($response, 9);
        return $status[0] == '4' || $status[0] == '5';
    } else {
        return false;
    }
}

/**
 * Returns the system information view.
 *
 * @global array The paths of system files and folders.
 * @global array The localization of the core.
 *
 * @return string HTML
 *
 * @since 1.6
 */
function XH_sysinfo()
{
    global $pth, $tx;

    $o = '<p><b>' . $tx['sysinfo']['version'] . '</b></p>' . "\n";
    $o .= '<ul>' . "\n" . '<li>' . CMSIMPLE_XH_VERSION . '&nbsp;&nbsp;Released: '
        . CMSIMPLE_XH_DATE . '</li>' . "\n" . '</ul>' . "\n" . "\n";

    $o .= '<p><b>' . $tx['sysinfo']['plugins'] . '</b></p>' . "\n" . "\n";

    $o .= '<ul>' . "\n";
    foreach (XH_plugins() as $temp) {
        $o .= '<li>' . ucfirst($temp) . ' ' . XH_pluginVersion($temp) . '</li>'
            . "\n";
    }
    $o .= '</ul>' . "\n" . "\n";

    $serverSoftware = !empty($_SERVER['SERVER_SOFTWARE'])
        ? $_SERVER['SERVER_SOFTWARE']
        : $tx['sysinfo']['unknown'];
    $o .= '<p><b>' . $tx['sysinfo']['webserver'] . '</b></p>' . "\n"
        . '<ul>' . "\n" . '<li>' . $serverSoftware . '</li>' . "\n"
        . '</ul>' . "\n\n";
    $o .= '<p><b>' . $tx['sysinfo']['php_version'] . '</b></p>' . "\n"
        . '<ul>' . "\n" . '<li>' . phpversion() . '</li>' . "\n"
        . '<li><a href="./?&phpinfo" target="blank"><b>'
        . $tx['sysinfo']['phpinfo_link'] . '</b></a> &nbsp; '
        . $tx['sysinfo']['phpinfo_hint'] . '</li>' . "\n" . '</ul>' . "\n" . "\n";

    $o .= '<h4>' . $tx['sysinfo']['helplinks'] . '</h4>' . "\n" . "\n";
    $o .= <<<HTML
<ul>
<li><a href="http://www.cmsimple-xh.org/">cmsimple-xh.org &raquo;</a></li>
<li><a href="http://www.cmsimple-xh.org/wiki/">cmsimple-xh.org/wiki/ &raquo;</a></li>
<li><a href="http://www.cmsimpleforum.com/">cmsimpleforum.com &raquo;</a></li>
<li><a href="http://www.cmsimplewiki.com/">cmsimplewiki.com &raquo;</a></li>
</ul>

HTML;

    $checks = array(
        'phpversion' => '5.3',
        'extensions' => array(
            'json',
            'pcre',
            array('session', false),
            array('xml', false)
        ),
        'writable' => array(),
        'other' => array()
    );
    $temp = array(
        'content', 'corestyle', 'images', 'downloads', 'userfiles', 'media'
    );
    foreach ($temp as $i) {
        $checks['writable'][] = $pth['folder'][$i];
    }
    $temp = array('config', 'log', 'language', 'content', 'template', 'stylesheet');
    foreach ($temp as $i) {
        $checks['writable'][] = $pth['file'][$i];
    }
    $checks['writable'] = array_unique($checks['writable']);
    sort($checks['writable']);
    $files = array(
        $pth['file']['config'], $pth['file']['content'], $pth['file']['template']
    );
    foreach ($files as $file) {
        $checks['other'][] = array(
            XH_isAccessProtected($file), false,
            sprintf($tx['syscheck']['access_protected'], $file)
        );
    }
    if ($tx['locale']['all'] == '') {
        $checks['other'][] = array(true, false, $tx['syscheck']['locale_default']);
    } else {
        $checks['other'][] = array(
            setlocale(LC_ALL, $tx['locale']['all']), false,
            sprintf($tx['syscheck']['locale_available'], $tx['locale']['all'])
        );
    }
    $checks['other'][] = array(
        date_default_timezone_get() !== 'UTC',
        false, $tx['syscheck']['timezone']
    );
    $checks['other'][] = array(
        !get_magic_quotes_runtime(), false, $tx['syscheck']['magic_quotes']
    );
    $checks['other'][] = array(
        !ini_get('safe_mode'), false, 'safe_mode off'
    );
    $checks['other'][] = array(
        !ini_get('session.use_trans_sid'), false, 'session.use_trans_sid off'
    );
    $checks['other'][] = array(
        ini_get('session.use_only_cookies'), false, 'session.use_only_cookies on'
    );
    $checks['other'][] = array(
        strpos(ob_get_contents(), "\xEF\xBB\xBF") !== 0,
        false, $tx['syscheck']['bom']
    );
    $o .= XH_systemCheck($checks);
    return $o;
}


/**
 * Returns the general settings view.
 *
 * @return string HTML
 *
 * @global string The script name.
 * @global array  The localization of the core.
 *
 * @since 1.6
 */
function XH_settingsView()
{
    global $sn, $tx;

    $o = '<p>' . $tx['settings']['warning'] . '</p>' . "\n"
        . '<h4>' . $tx['settings']['systemfiles'] . '</h4>' . "\n" . '<ul>' . "\n";

    foreach (array('config', 'language') as $i) {
        $o .= '<li><a href="' . $sn . '?file=' . $i . '&amp;action=array">'
            . utf8_ucfirst($tx['action']['edit']) . ' '
            . $tx['filetype'][$i] . '</a></li>' . "\n";
    }

    foreach (array('stylesheet', 'template') as $i) {
        $o .= '<li><a href="' . $sn . '?file=' . $i . '&amp;action=edit">'
            . utf8_ucfirst($tx['action']['edit']) . ' '
            . $tx['filetype'][$i] . '</a></li>' . "\n";
    }
    foreach (array('log') as $i) {
        $o .= '<li><a href="' . $sn . '?file=' . $i . '&amp;action=view">'
            . utf8_ucfirst($tx['action']['view']) . ' '
            . $tx['filetype'][$i] . '</a></li>' . "\n";
    }
    $o .= '</ul>' . "\n";

    $o .= '<h4>' . $tx['settings']['backup'] . '</h4>' . "\n";
    $o .= XH_backupsView();
    return $o;
}

/**
 * Returns the log file view.
 *
 * @return string HTML
 *
 * @global array  The paths of system files and folders.
 * @global array  The localization of the core.
 * @global string The title of the current page.
 *
 * @since 1.6
 */
function XH_logFileView()
{
    global $pth, $tx, $title;

    $title = $tx['title']['log'];
    return '<h1>' . $tx['title']['log'] . '</h1>'
        . '<pre id="xh_logfile">' . XH_hsc(XH_readFile($pth['file']['log']))
        . '</pre>'
        . '<script type="text/javascript">/* <![CDATA[ */'
        . '(function () {'
        . 'var elt = document.getElementById("xh_logfile");'
        . 'elt.scrollTop = elt.scrollHeight;'
        . '}())'
        . '/* ]]> */</script>'
        . '<p>('
        . $tx['log']['timestamp'] . ' &ndash; '
        . $tx['log']['type']      . ' &ndash; '
        . $tx['log']['module']    . ' &ndash; '
        . $tx['log']['category']  . ' &ndash; '
        . $tx['log']['description']
        . ')</p>';
}

/**
 * Returns the backup view.
 *
 * @return string HTML
 *
 * @global array  The paths of system files and folders.
 * @global array  The script name.
 * @global array  The localization of the core.
 * @global object The CSRF protection object.
 *
 * @since 1.6
 */
function XH_backupsView()
{
    global $pth, $sn, $tx, $_XH_csrfProtection;

    $o = '<ul>' . "\n";
    if (isset($_GET['xh_success'])) {
        $o .= XH_message('success', $tx['message'][stsl($_GET['xh_success'])]);
    }
    $o .= '<li>' . utf8_ucfirst($tx['filetype']['content']) . ' <a href="'
        . $sn . '?file=content&amp;action=view" target="_blank">'
        . $tx['action']['view'] . '</a>' . ' <a href="' . $sn . '?file=content">'
        . $tx['action']['edit'] . '</a>' . ' <a href="'
        . $sn . '?file=content&amp;action=download">' . $tx['action']['download']
        . '</a>'
        . ' <form action="' . $sn . '?&xh_backups" method="post"'
        . ' class="xh_inline_form" id="xh_backup_form">'
        . '<input type="hidden" name="file" value="content">'
        . '<input type="hidden" name="action" value="backup">'
        . '<input type="hidden" name="xh_suffix" value="extra">'
        . '<input type="submit" class="submit" value="'
        . $tx['action']['backup'] . '">'
        . $_XH_csrfProtection->tokenInput()
        . '</form>'
        . ' <form action="' . $sn . '?&xh_backups" method="post"'
        . ' class="xh_inline_form">'
        . '<input type="hidden" name="file" value="content">'
        . '<input type="hidden" name="action" value="empty">'
        . '<input type="submit" class="submit" value="'
        . $tx['action']['empty'] . '">'
        . $_XH_csrfProtection->tokenInput()
        . '</form>'
        . '</li>' . "\n";
    $o .= '</ul>' . "\n" . '<hr>' . "\n" . '<p>'
        . $tx['settings']['backupexplain1'] . '</p>' . "\n" . '<p>'
        . $tx['settings']['backupexplain2'] . '</p>' . "\n" . '<ul>' . "\n";
    $fs = sortdir($pth['folder']['content']);
    foreach ($fs as $p) {
        if (XH_isContentBackup($p, false)) {
            $size = filesize($pth['folder']['content'] . '/' . $p);
            $size = round(($size) / 102.4) / 10;
            $o .= '<li><a href="' . $sn . '?file=' . $p
                . '&amp;action=view" target="_blank">'
                . $p . '</a> (' . $size . ' KB)'
                . ' <form action="' . $sn . '?&xh_backups" method="post"'
                . ' class="xh_inline_form">'
                . '<input type="hidden" name="file" value="' . $p . '">'
                . '<input type="hidden" name="action" value="restore">'
                . '<input type="submit" class="submit" value="'
                . $tx['action']['restore'] . '">'
                . $_XH_csrfProtection->tokenInput()
                . '</form>'
                . '</li>' . "\n";
        }
    }
    $o .= '</ul>' . "\n";
    return $o;
}

/**
 * Creates the menu of a plugin (add row, add tab), constructed as a table.
 * This is an object implemented with a procedural interface.
 *
 * @param string $add    Add a ROW, a TAB or DATA (Userdefineable content).
 *                       SHOW will return the menu.
 * @param string $link   The link, the TAB will lead to.
 * @param string $target Target of the link (with(!) 'target=').
 * @param string $text   Description of the TAB.
 * @param array  $style  Array with style-data for the containing table-cell
 *
 * @return mixed
 *
 * @global XH\ClassicPluginMenu The plugin menu builder.
 */
function pluginMenu($add = '', $link = '', $target = '', $text = '',
    array $style = array()
) {
    global $_XH_pluginMenu;

    switch (strtoupper($add)) {
    case 'ROW':
        $_XH_pluginMenu->makeRow($style);
        break;
    case 'TAB':
        $_XH_pluginMenu->makeTab($link, $target, $text, $style);
        break;
    case 'DATA':
        $_XH_pluginMenu->makeData($text, $style);
        break;
    case 'SHOW':
        return $_XH_pluginMenu->show();
        break;
    }
}

/**
 * Registers the standard plugin menu items for the admin menu.
 *
 * @param bool $showMain Whether to display the main settings item.
 *
 * @return void
 *
 * @since 1.6.2
 */
function XH_registerStandardPluginMenuItems($showMain)
{
    $pluginMenu = new XH\IntegratedPluginMenu();
    $pluginMenu->render($showMain);
}

/**
 * Register a new plugin menu item, or returns the registered plugin menu items,
 * if <var>$label</var> and <var>$url</var> are null.
 *
 * @param string $plugin A plugin name.
 * @param string $label  A menu item label.
 * @param string $url    A URL to link to.
 * @param string $target A target attribute value.
 *
 * @return mixed
 *
 * @staticvar array $pluginMenu The array of already registered menu items.
 *
 * @since 1.6.2
 */
function XH_registerPluginMenuItem($plugin, $label = null, $url = null,
    $target = null
) {
    static $pluginMenu = array();

    if (isset($label) && isset($url)) {
        $pluginMenu[$plugin][] = array(
            'label' => $label,
            'url' => $url,
            'target' => $target
        );
    } else {
        if (isset($pluginMenu[$plugin])) {
            return $pluginMenu[$plugin];
        } else {
            return array();
        }
    }
}

/**
 * Returns the admin menu.
 *
 * @param array $plugins A list of plugins.
 *
 * @return string HTML
 *
 * @since 1.6
 */
function XH_adminMenu(array $plugins = array())
{
    $view = new XH\AdminMenu($plugins);
    return $view->render();
}

/**
 * Returns the plugin menu.
 *
 * @param string $main Whether the main setting menu item should be shown
 *                     ('ON'/'OFF').
 *
 * @return string HTML
 *
 * @global XH\ClassicPluginMenu The plugin menu builder.
 */
// @codingStandardsIgnoreStart
function print_plugin_admin($main)
{
// @codingStandardsIgnoreEnd
    global $_XH_pluginMenu;

    initvar('action');
    initvar('admin');
    return $_XH_pluginMenu->render(strtoupper($main) == 'ON');
}

/**
 * Handles reading and writing of plugin files
 * (e.g. en.php, config.php, stylesheet.css).
 *
 * @param bool  $action Unused.
 * @param array $admin  Unused.
 * @param bool  $plugin Unused.
 *
 * @global string The requested action.
 * @global string The requested admin-action.
 * @global string The name of the currently loading plugin.
 *
 * @return string Returns the created form or the result of saving the data.
 *
 * @todo Deprecated unused parameters.
 */
// @codingStandardsIgnoreStart
function plugin_admin_common($action, $admin, $plugin)
{
// @codingStandardsIgnoreEnd
    global $action, $admin, $plugin;

    switch ($admin) {
    case 'plugin_config':
        $fileEdit = new XH\PluginConfigFileEdit();
        break;
    case 'plugin_language':
        $fileEdit = new XH\PluginLanguageFileEdit();
        break;
    case 'plugin_stylesheet':
        $fileEdit = new XH\PluginTextFileEdit();
        break;
    default:
        return false;
    }
    switch ($action) {
    case 'plugin_edit':
    case 'plugin_text':
        return $fileEdit->form();
    case 'plugin_save':
    case 'plugin_textsave':
        return $fileEdit->submit();
    default:
        return false;
    }
}


/**
 * Returns the content editor and activates it.
 *
 * @global string The script name.
 * @global string The currently active page URL.
 * @global int    The index of the currently active page.
 * @global array  The URLs of the pages.
 * @global array  The content of the pages.
 * @global string Error messages as HTML fragment consisting of LI Elements.
 * @global array  The configuration of the core.
 * @global array  The localization of the core.
 * @global object The CSRF protection object.
 *
 * @return string  HTML
 *
 * @since 1.6
 */
function XH_contentEditor()
{
    global $sn, $su, $s, $u, $c, $e, $cf, $tx, $_XH_csrfProtection;

    $su = $u[$s]; // TODO: is changing of $su correct here???

    $editor = $cf['editor']['external'] == '' || init_editor();
    if (!$editor) {
        $msg = sprintf($tx['error']['noeditor'], $cf['editor']['external']);
        $e .= '<li>' . $msg . '</li>' . "\n";
    }
    $o = '<form method="POST" id="ta" action="' . $sn . '">'
        . '<input type="hidden" name="selected" value="' . $u[$s] . '">'
        . '<input type="hidden" name="function" value="save">'
        . '<textarea name="text" id="text" class="xh-editor" style="height: '
        . $cf['editor']['height'] . 'px; width: 100%;" rows="30" cols="80">'
        . XH_hsc($c[$s])
        . '</textarea>'
        . '<script type="text/javascript">/* <![CDATA[ */'
        . 'document.getElementById("text").style.height=(' . $cf['editor']['height']
        . ') + "px";/* ]]> */</script>'
        . $_XH_csrfProtection->tokenInput();
    if ($cf['editor']['external'] == '' || !$editor) {
        $value = utf8_ucfirst($tx['action']['save']);
        $o .= '<input type="submit" value="' . $value . '">';
    }
    $o .= '</form>';
    return $o;
}

/**
 * Saves the current contents (including the page data), if edit mode is active.
 *
 * @return bool Whether that succeeded
 *
 * @global array  The content of the pages.
 * @global array  The paths of system files and folders.
 * @global array  The configuration of the core.
 * @global array  The localization of the core.
 * @global array  Whether edit mode is active.
 * @global object The page data router.
 *
 * @since 1.6
 */
function XH_saveContents()
{
    global $c, $pth, $cf, $tx, $edit, $pd_router;

    if (!(XH_ADM && $edit)) {
        trigger_error(
            'Function ' . __FUNCTION__ . '() must not be called in view mode',
            E_USER_WARNING
        );
        return false;
    }
    $hot = '<h[1-' . $cf['menu']['levels'] . '][^>]*>';
    $hct = '<\/h[1-' . $cf['menu']['levels'] . ']>';
    $title = utf8_ucfirst($tx['filetype']['content']);
    $cnts = "<html><head><title>$title</title>\n"
        . $pd_router->headAsPHP()
        . '</head><body>' . "\n";
    foreach ($c as $j => $i) {
        preg_match("/(.*?)($hot(.+?)$hct)(.*)/isu", $i, $matches);
        $page = $matches[1] . $matches[2] . PHP_EOL . $pd_router->pageAsPHP($j)
            . $matches[4];
        $cnts .= rmnl($page . "\n");
    }
    $cnts .= '</body></html>';
    if (!file_exists($pth['folder']['content'])) {
        mkdir($pth['folder']['content'], true);
    }
    return XH_writeFile($pth['file']['content'], $cnts) !== false;
}

/**
 * Saves content.htm after submitting changes from the content editor.
 *
 * @param string $text The text to save.
 *
 * @global array  The paths of system files and folders.
 * @global array  The configuation of the core.
 * @global array  The localization of the core.
 * @global object The page data router.
 * @global array  The content of the pages.
 * @global int    The index of the active page.
 * @global array  The URLs of the pages.
 * @global string The URL of the active page.
 *
 * @return void
 *
 * @since 1.6
 */
function XH_saveEditorContents($text)
{
    global $pth, $cf, $tx, $pd_router, $c, $s, $u, $selected;

    $hot = '<h[1-' . $cf['menu']['levels'] . '][^>]*>';
    $hct = '<\/h[1-' . $cf['menu']['levels'] . ']>'; // TODO: use $1 ?
    // TODO: this might be done before the plugins are loaded
    //       for backward compatibility
    $text = stsl($text);
    // remove empty headings
    $text = preg_replace("/$hot(&nbsp;|&#160;|\xC2\xA0| )?$hct/isu", '', $text);
    // replace P elements around plugin calls and scripting with DIVs
    $text = preg_replace(
        '/<p>({{{.*?}}}|#CMSimple .*?#)<\/p>/isu', '<div>$1</div>', $text
    );

    // handle missing heading on the first page
    if ($s == 0) {
        if (!preg_match('/^<h1[^>]*>.*<\/h1>/isu', $text)
            && !preg_match('/^(<p[^>]*>)?(\&nbsp;| |<br \/>)?(<\/p>)?$/isu', $text)
        ) {
            $text = '<h1>' . $tx['toc']['missing'] . '</h1>' . "\n" . $text;
        }
    }
    $c[$s] = $text; // keep editor contents, if saving fails

    // insert $text to $c
    $text = preg_replace(
        '/<h[1-' . $cf['menu']['levels'] . ']/i', "\x00" . '$0', $text
    );
    $pages = explode("\x00", $text);
    // append everything before the first page heading to the previous page:
    if ($s > 0) {
        $c[$s - 1] .= $pages[0];
    }
    array_shift($pages);
    array_splice($c, $s, 1, $pages);

    // delegate changes to $pd_router
    preg_match_all("/$hot(.+?)$hct/isu", $text, $matches);
    if ($pd_router->refresh_from_texteditor($matches[1], $s)) {
        // redirect to get back in sync
        if (count($matches[1]) > 0) {
            // page heading might have changed
            $urlParts = explode($cf['uri']['seperator'], $selected);
            array_splice(
                $urlParts, -1, 1, uenc(trim(xh_rmws(strip_tags($matches[1][0]))))
            );
            $su = implode($cf['uri']['seperator'], $urlParts);
        } else {
            // page was deleted; go to previous page
            $su = $u[max($s - 1, 0)];
        }
        header("Location: " . CMSIMPLE_URL . "?" . $su, true, 303);
        exit;
    } else {
        e('notwritable', 'content', $pth['file']['content']);
    }
}

/**
 * Empties the contents.
 *
 * @return void
 *
 * @global array  The content of the pages.
 * @global int    The number of pages.
 * @global array  The paths of system files and folders.
 * @global array  An HTML fragment with error messages.
 * @global object The pagedata router.
 */
function XH_emptyContents()
{
    global $c, $cl, $pth, $e, $pd_router;

    XH_backup();
    if ($e) {
        return;
    }
    $c = array();
    for ($i = 0; $i < $cl; ++$i) {
        $pd_router->destroy($i);
    }
    if (XH_saveContents()) {
        // the following relocation is necessary to cater for the changed content
        $url = CMSIMPLE_URL . '?&xh_backups&xh_success=emptied';
        header('Location: ' . $url, true, 303);
        exit;
    } else {
        e('cntsave', 'content', $pth['file']['content']);
    }
}

/**
 * Restores a content backup. The current content.htm is backed up before.
 *
 * @param string $filename The filename.
 *
 * @return void
 *
 * @global array  The paths of system files and folders.
 * @global array  An HTML fragment with error messages.
 *
 * @since 1.6
 */
function XH_restore($filename)
{
    global $pth, $e;

    $tempFilename = $pth['folder']['content'] . 'restore.htm';
    if (!rename($filename, $tempFilename)) {
        e('cntsave', 'backup', $tempFilename);
        return;
    }
    XH_backup();
    if ($e) {
        if (!unlink($tempFilename)) {
            e('cntdelete', 'content', $tempFilename);
        }
        return;
    }
    if (!rename($tempFilename, $pth['file']['content'])) {
        e('cntsave', 'content', $pth['file']['content']);
        return;
    }
    // the following relocation is necessary to cater for the changed content
    $url = CMSIMPLE_URL . '?&xh_backups&xh_success=restored';
    header('Location: ' . $url, true, 303);
    exit;
}

/**
 * Creates an extra backup of the contents file.
 *
 * @param string $suffix A suffix for the filename.
 *
 * @return void
 *
 * @since 1.6
 */
function XH_extraBackup($suffix)
{
    global $pth;

    $date = date("Ymd_His");
    $dest = $pth['folder']['content'] . $date . '_' . $suffix . '.htm';
    if (!copy($pth['file']['content'], $dest)) {
        e('cntsave', 'backup', $dest);
    } else {
        $url = CMSIMPLE_URL . '?&xh_backups&xh_success=backedup';
        header('Location: ' . $url, true, 303);
        exit;
    }
}

/**
 * Returns SCRIPT element containing the localization for admin.js.
 *
 * @return string HTML
 *
 * @global array The localization of the core.
 *
 * @since 1.6
 */
function XH_adminJSLocalization()
{
    global $tx;

    $keys = array(
        'action' => array('cancel', 'ok'),
        'password' => array('fields_missing', 'invalid', 'mismatch', 'wrong'),
        'error' => array('server'),
        'settings' => array('backupsuffix')
    );
    $l10n = array();
    foreach ($keys as $category => $keys2) {
        foreach ($keys2 as $key) {
            $l10n[$category][$key] = $tx[$category][$key];
        }
    }
    $o = '<script type="text/javascript">/* <![CDATA[ */XH.i18n = '
        . XH_encodeJson($l10n) . '/* ]]> */</script>' . PHP_EOL;
    return $o;
}

/**
 * Returns whether the administration of a certain plugin is requested.
 *
 * @param string $pluginName A plugin name.
 *
 * @return bool
 *
 * @since 1.6.3
 */
function XH_wantsPluginAdministration($pluginName)
{
    return isset($GLOBALS[$pluginName]) && $GLOBALS[$pluginName] == 'true';
}

?>
