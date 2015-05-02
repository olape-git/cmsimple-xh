<?php

/**
 * Generates the admin menu.
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

namespace XH;

/**
 * Generates the admin menu.
 *
 * @category CMSimple_XH
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.7
 */
class AdminMenu
{
    /**
     * The installed plugins.
     *
     * @var array
     */
    protected $plugins;

    /**
     * The width of the plugin menu.
     *
     * @var int
     */
    protected $width;

    /**
     * The left margin of the plugin menu.
     *
     * @var int
     */
    protected $leftMargin;

    /**
     * Initializes a new instance.
     *
     * @param array $plugins A list of plugins.
     */
    public function __construct(array $plugins = array())
    {
        $this->plugins = $plugins;
    }

    /**
     * Returns the admin menu.
     *
     * @return string HTML
     */
    public function render()
    {
        $t = "\n" . '<div id="xh_adminmenu">';
        $t .= "\n" . '<ul>' . "\n";
        foreach ($this->getMenu() as $item) {
            $t .= $this->renderItem($item);
        }
        $t .= '</ul>' . "\n"
            . '<div class="xh_break"></div>' . "\n" . '</div>' . "\n";
        return $t;
    }

    /**
     * Arranges the plugins for the multi-colum plugin menu.
     *
     * @return array
     *
     * @global array  The configuration of the core.
     */
    protected function arrangePlugins()
    {
        global $cf;

        $hiddenPlugins = explode(',', $cf['plugins']['hidden']);
        $hiddenPlugins = array_map('trim', $hiddenPlugins);
        $plugins = array_diff($this->plugins, $hiddenPlugins);
        $total = count($plugins);
        $rows = 12;
        $columns = ceil($total / $rows);
        $rows = ceil($total / $columns);
        $this->width = 125 * $columns;
        $this->leftMargin = min($this->width, 250) - $this->width;
        natcasesort($plugins);
        $plugins = array_values($plugins);
        $orderedPlugins = array();
        for ($j = 0; $j < $rows; ++$j) {
            for ($i = 0; $i < $total; $i += $rows) {
                if (isset($plugins[$i + $j])) {
                    $orderedPlugins[] = $plugins[$i + $j];
                }
            }
        }
        return $orderedPlugins;
    }

    /**
     * Returns the items of the full admin menu.
     *
     * @return array Nested array structure.
     *
     * @global string The scipt name.
     * @global bool   Whether edit mode is active.
     * @global int    The index of the current page.
     * @global array  The URLs of the pages.
     * @global array  The localization of the core.
     * @global string The URL of the current page.
     */
    protected function getMenu()
    {
        global $sn, $edit, $s, $u, $tx, $su;

        if ($s < 0) {
            $su = $u[0];
        }

        $changeMode = $edit ? 'normal' : 'edit';
        $changeText = $edit ? $tx['editmenu']['normal'] : $tx['editmenu']['edit'];

        return array(
            array(
                'label' => $changeText,
                'url' => $sn . '?' . $su . '&' . $changeMode,
            ),
            array(
                'label' => utf8_ucfirst($tx['editmenu']['pagemanager']),
                'url' => $sn . '?&normal&xhpages'
            ),
            array(
                'label' => utf8_ucfirst($tx['editmenu']['files']),
                'url' => $sn . '?&normal&userfiles',
                'children' => $this->getFilesMenu()
                ),
            array(
                'label' => utf8_ucfirst($tx['editmenu']['settings']),
                'url' => $sn . '?&settings',
                'children' => $this->getSettingsMenu()
            ),
            array(
                'label' => utf8_ucfirst($tx['editmenu']['plugins']),
                'url' => $sn, // TODO: use more sensible URL
                'children' => $this->getPluginsMenu(),
                'id' => 'xh_adminmenu_plugins',
                'style' => 'width:' . $this->width . 'px; margin-left: '
                    . $this->leftMargin . 'px'
            ),
            array(
                'label' => utf8_ucfirst($tx['editmenu']['logout']),
                'url' => $sn . '?&logout'
            )
        );
    }

    /**
     * Returns the items of the files menu.
     *
     * @return array
     *
     * @global string The scipt name.
     * @global array  The localization of the core.
     */
    protected function getFilesMenu()
    {
        global $sn, $tx;

        $filesMenu = array();
        foreach (array('images', 'downloads', 'media') as $item) {
            $filesMenu[] =  array(
                'label' => utf8_ucfirst($tx['editmenu'][$item]),
                'url' => $sn . '?&normal&' . $item
            );
        }
        return $filesMenu;
    }

    /**
     * Returns the items of the settings menu.
     *
     * @return array
     *
     * @global string The scipt name.
     * @global array  The localization of the core.
     */
    protected function getSettingsMenu()
    {
        global $sn, $tx;

        return array(
            array(
                'label' => utf8_ucfirst($tx['editmenu']['configuration']),
                'url' => $sn . '?file=config&action=array'
            ),
            array(
                'label' => utf8_ucfirst($tx['editmenu']['language']),
                'url' => $sn . '?file=language&action=array'
            ),
            array(
                'label' => utf8_ucfirst($tx['editmenu']['template']),
                'url' => $sn . '?file=template&action=edit'
            ),
            array(
                'label' => utf8_ucfirst($tx['editmenu']['stylesheet']),
                'url' => $sn . '?file=stylesheet&action=edit'
            ),
            array(
                'label' => utf8_ucfirst($tx['editmenu']['log']),
                'url' => $sn . '?file=log&action=view'
            ),
            array(
                'label' => utf8_ucfirst($tx['editmenu']['validate']),
                'url' => $sn . '?&validate'
            ),
            array(
                'label' => utf8_ucfirst($tx['editmenu']['backups']),
                'url' => $sn . '?&xh_backups'
            ),
            array(
                'label' => utf8_ucfirst($tx['editmenu']['pagedata']),
                'url' => $sn . '?&xh_pagedata'
            ),
            array(
                'label' => utf8_ucfirst($tx['editmenu']['sysinfo']),
                'url' => $sn . '?&sysinfo'
            )
        );
    }

    /**
     * Returns the items of the plugins menu.
     *
     * @return array
     *
     * @global string The scipt name.
     */
    protected function getPluginsMenu()
    {
        global $sn;

        $plugins = $this->arrangePlugins();
        $pluginMenu = array();
        foreach ($plugins as $plugin) {
            $label = isset($plugin_tx[$plugin]['menu_plugin'])
                ? $plugin_tx[$plugin]['menu_plugin']
                : ucfirst($plugin);
            $pluginMenuItem = array('label' => $label);
            if ($plugin != '') {
                $pluginMenuItem['url'] = $sn . '?' . $plugin . '&normal';
                foreach (XH_registerPluginMenuItem($plugin) as $item) {
                    $pluginMenuItem['children'][] = $item;
                }
            }
            $pluginMenu[] = $pluginMenuItem;
        }
        return $pluginMenu;
    }

    /**
     * Returns the LI element of menu item.
     *
     * @param array $item  The menu item.
     * @param int   $level The level of the menu item.
     *
     * @return string
     */
    protected function renderItem(array $item, $level = 0)
    {
        $indent = str_repeat('    ', $level);
        $t = $indent . '<li>';
        if (isset($item['url'])) {
            $t .= '<a href="' . XH_hsc($item['url']) . '"';
            if (isset($item['target'])) {
                $t .= ' target="' . $item['target'] . '"';
            }
            $t .= '>';
        } else {
            $t .= '<span>';
        }
        $t .= $item['label'];
        if (isset($item['url'])) {
            $t .= '</a>';
        } else {
            $t .= '</span>';
        }
        if (isset($item['children'])) {
            $t .= "\n" . $indent . '    <ul';
            if (isset($item['id'])) {
                $t .= ' id="' . $item['id'] . '"';
            }
            if (isset($item['style'])) {
                $t .= ' style="' . $item['style'] . '"';
            }
            $t .= '>' . "\n";
            foreach ($item['children'] as $child) {
                $t .= $this->renderItem($child, $level + 1);
            }
            $t .= $indent . '    </ul>' . "\n" . $indent;
        }
        $t .= '</li>' . "\n";
        return $t;
    }

}

?>
