<?php
/**
 * DokuWiki Plugin HighlightJS
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author Ben van Magill <ben.vanmagill16@gmail.com>
 *
 * usage: <file html nowrap [downloadfile.html] | Title > ... </file>
 */

class syntax_plugin_codehighlightjs_file extends syntax_plugin_codehighlightjs_code
{
    public function connectTo($mode)
    {
        $this->Lexer->addEntryPattern('<file\b(?=.*</file>)', $mode, 'plugin_codehighlightjs_file');
    }

    public function postConnect()
    {
        $this->Lexer->addExitPattern('</file>', 'plugin_codehighlightjs_file');
    }
}
