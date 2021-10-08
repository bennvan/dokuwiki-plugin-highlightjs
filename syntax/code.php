<?php
/**
 * DokuWiki Plugin HighlightJS
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author Ben van Magill <ben.vanmagill16@gmail.com>
 *
 * usage: <code html nowrap [downloadfile.html] | Title > ... </Code>
 */

if (!defined('DOKU_INC')) die();

class syntax_plugin_codehighlightjs_code extends DokuWiki_Syntax_Plugin
{
    /** @var int counts the code and file blocks, used to provide download links */
    protected $_codeblock = 0;

    public function getType()
    {   // Syntax Type
        return 'protected';
    }

    public function getPType()
    {   // Paragraph Type
        return 'block';
    }

    /**
     * Return the format of the renderer
     *
     * @returns string 'code'
     */
    public function getFormat() {
        return 'code';
    }

    /**
     * Connect pattern to lexer
     */
    protected $mode, $pattern;

    public function getSort()
    {   // sort number used to determine priority of this mode
        return 199; // < native 'code' mode (=200)
    }

    public function preConnect()
    {
        // Plugin name
        $this->mode = substr(get_class($this), 7);
        // DokuWiki original syntax patterns
        $this->pattern[1] = '<code\b.*?>(?=.*?</code>)';
        $this->pattern[4] = '</code>';
        $this->pattern[11] = '<file\b.*?>(?=.*?</file>)';
        $this->pattern[14] = '</file>';
    }

    public function connectTo($mode)
    {
        $this->Lexer->addEntryPattern($this->pattern[1], $mode, $this->mode);
        $this->Lexer->addEntryPattern($this->pattern[11], $mode, $this->mode);
    }

    public function postConnect()
    {
        $this->Lexer->addExitPattern($this->pattern[4], $this->mode);
        $this->Lexer->addExitPattern($this->pattern[14], $this->mode);
    }


    /**
     * Handle the match
     */
    public function handle($match, $state, $pos, Doku_Handler $handler)
    {
        if ($state == DOKU_LEXER_ENTER) {
                list($params, $title) = explode('|', substr($match, 5, -1), 2);

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

                return $data = [$match, $state, $pos, $opts];
            }
        if ($state == DOKU_LEXER_UNMATCHED) {
                return $data = [$match, $state, $pos];
            }
        if ($state == DOKU_LEXER_EXIT) {
                return $data = [$match, $state, $pos];
        }
        return false;
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

        list($match, $state, $pos, $opts) = $data;

        global $code_filename;

        /** @var Doku_Renderer_metadata $renderer */
        if ($mode !== 'xhtml'){
            if ($state == DOKU_LEXER_ENTER) {
                $code_filename = $opts['filename'];
            }
            if ($state == DOKU_LEXER_UNMATCHED) {
                $renderer->file($match, null, $code_filename);
            }
            return false;
        } 
        
        /** @var Doku_Renderer_xhtml $renderer */
        if ($state == DOKU_LEXER_ENTER) {
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
                        array('codeblock' => $offset + $this->_codeblock)
                    ) . '" title="' . $lang['download'] . '" class="' . $class . '">';
                $markup .= hsc($code_filename);
                $markup .= '</a></dt>'.DOKU_LF.'<dd>';
            }


            if ($opts['title']) {
                $markup .= '<div class="code-title">'.$opts['title'].DOKU_LF.'</div>';
                unset($opts['title']);
            }

            // start the toolbar and code
            $markup .= '<div class="code-toolbar">';
            $markup .= '<pre class="'.hsc(implode(' ', $opts)).'">';

            $renderer->doc .= $markup;
            return true;
        }
        if ($state == DOKU_LEXER_UNMATCHED) {
            $renderer->doc .= $renderer->_xmlEntities($match);
            return true;
        }

        if ($state == DOKU_LEXER_EXIT) {
            $markup = '</pre></div>';

            if($code_filename) {
                $markup .= '</dd></dl>'.DOKU_LF;
            }

            $renderer->doc .= $markup;

            $this->_codeblock++;
            return true;
        }
        
    }
}
