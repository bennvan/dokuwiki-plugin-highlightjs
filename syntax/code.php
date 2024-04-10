<?php
/**
 * DokuWiki Plugin HighlightJS
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author Ben van Magill <ben.vanmagill16@gmail.com>
 *
 * usage: <code html nowrap [downloadfile.html] | Title > ... </Code>
 */

use dokuwiki\Extension\SyntaxPlugin;

class syntax_plugin_codehighlightjs_code extends SyntaxPlugin
{
    /** @var int counts the code and file blocks, used to provide download links */
    private static $_codeblock = 0;

    public function getType()
    {   // Syntax Type
        return 'protected';
    }

    public function getPType()
    {   // Paragraph Type
        return 'block';
    }

    /**
     * Connect pattern to lexer
     */
    protected $mode, $pattern;

    public function getSort()
    {   // sort number used to determine priority of this mode
        return 199; // < native 'code' mode (=200)
    }

    public function connectTo($mode)
    {
        $this->Lexer->addEntryPattern('<code\b(?=.*</code>)', $mode, 'plugin_codehighlightjs_code');
    }

    public function postConnect()
    {
        $this->Lexer->addExitPattern('</code>', 'plugin_codehighlightjs_code');
    }


    /**
     * Handle the match
     */
    public function handle($match, $state, $pos, Doku_Handler $handler)
    {
        if ($state == DOKU_LEXER_UNMATCHED) {
                $matches = explode('>',$match,2);
                list($params, $title) = explode('|', $matches[0], 2);

                if ($title) {
                    $opts['title'] = $title;
                }

                // Cut out any filename enclosed in []
                preg_match('/\[.*\]/', $params, $options);
                if (!empty($options[0])) {
                    $opts['filename'] = substr($options[0], 1, -1);
                    $params = str_replace($options[0], '', $params);
                }

                // look for nowrap command
                $params = preg_replace('/\bnowrap\b/', '', $params, 1, $count);
                if ($count){
                    $opts['wrap'] = 'code-nowrap';
                }

                // get language
                $param = preg_split('/\s+/', $params, 2, PREG_SPLIT_NO_EMPTY);
                while(count($param) < 2) array_push($param, null);

                if ($param[0]){
                    if ($param[0] == '-') $opts['language'] = 'nohighlight'; 
                    else $opts['language'] = 'language-'.$param[0];
                }

                $opts['class'] = 'hljs';
                $text = $matches[1];
            }
        return array($match, $state, $pos, $opts, $text);
    }

    /**
     * Create output
     */
    function render($mode, Doku_Renderer $renderer, $data)
    {
        global $ID;
        global $lang;
        global $INPUT;

        if (empty($data)) return false;

        list($match, $state, $pos, $opts, $text) = $data;

        /** @var Doku_Renderer_metadata $renderer */
        if ($mode !== 'xhtml'){
            // if ($state == DOKU_LEXER_ENTER) {
                
            // }
            if ($state == DOKU_LEXER_UNMATCHED) {
                $code_filename = $opts['filename'];
                $renderer->file($text, null, $opts['filename']);
            }
            return false;
        } 
        
        /** @var Doku_Renderer_xhtml $renderer */
        if ($state == DOKU_LEXER_UNMATCHED) {
            // Prepare the edit buttons
            $secidclass = '';
            if ($this->getConf('editbutton')) {
                if  (defined('SEC_EDIT_PATTERN')) { // for DokuWiki Greebo and more recent versions
                    $secidclass = $renderer->startSectionEdit($pos-strlen('<code'), array('target' => 'plugin_codehighlightjs', 'name' => $state));
                } else {
                    $secidclass = $renderer->startSectionEdit($pos-strlen('<code'), 'plugin_codehighlightjs', $state);
                }
            }

            $code_filename = $opts['filename'];
            if($code_filename) {
                unset($opts['filename']);
                // add icon
                list($ext) = mimetype($code_filename, false);
                $class = preg_replace('/[^_\-a-z0-9]+/i', '_', $ext);
                $class = 'mediafile mf_'.$class;

                $offset = 0;
                if ($INPUT->has('codeblockOffset')) {
                    $offset = $INPUT->str('codeblockOffset');
                }
                $markup .= '<dl class="codeblock-file">'.DOKU_LF;
                $markup .= '<dt><a href="' .
                    exportlink(
                        $ID,
                        'code',
                        array('codeblock' => $offset + $this::$_codeblock)
                    ) . '" title="' . $lang['download'] . '" class="' . $class . '">';
                $markup .= hsc($code_filename);
                $markup .= '</a></dt>'.DOKU_LF.'<dd>';
            }


            if ($opts['title']) {
                $markup .= '<div class="code-title">'.$opts['title'].DOKU_LF.'</div>';
                unset($opts['title']);
            }

            // start the toolbar and code
            $markup .= '<div class="code-toolbar '.$secidclass.'">';
            $markup .= '<pre class="'.hsc(implode(' ', $opts)).'">'.$renderer->_xmlEntities($text).'</pre>'.DOKU_LF;
            $markup .= '</div>';

            if($code_filename) {
                $markup .= '</dd></dl>'.DOKU_LF;
            }

            $renderer->doc .= $markup;

            $this::$_codeblock++;
        }
        if ($state == DOKU_LEXER_EXIT){
            if ($this->getConf('editbutton')){
                $renderer->finishSectionEdit($pos + strlen($match));
            }
        }
        return true;
    }
}
