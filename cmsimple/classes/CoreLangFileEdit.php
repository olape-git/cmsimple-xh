<?php

namespace XH;

/**
 * Editing of core language files.
 *
 * @author    Peter Harteg <peter@harteg.dk>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 1999-2009 Peter Harteg
 * @copyright 2009-2019 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @see       http://cmsimple-xh.org/
 * @since     1.6
 */
class CoreLangFileEdit extends CoreArrayFileEdit
{
    /**
     * Constructs an instance.
     */
    public function __construct()
    {
        global $sl, $cf, $tx;

        parent::__construct();
        $this->varName = 'tx';
        $this->params = array(
            'form' => 'array',
            'file' => 'language',
            'action' => 'save'
        );
        $this->redir = '?file=language&action=array&xh_success=language';
        $this->cfg = array();
        foreach ($tx as $cat => $opts) {
            $this->cfg[$cat] = array();
            foreach ($opts as $name => $val) {
                // don't show or save the following
                if ($cat == 'meta' && $name =='codepage') {
                    continue;
                }
                $co = array('val' => $val, 'type' => 'text', 'isAdvanced' => false);
                if ($cat == 'subsite' && $name == 'template') {
                    if ($sl === $cf['language']['default']) {
                        $co['type'] = 'hidden';
                    } else {
                        $co['type'] = 'enum';
                        $co['vals'] = XH_templates();
                        array_unshift($co['vals'], '');
                    }
                }
                $this->cfg[$cat][$name] = $co;
            }
        }
    }
}
