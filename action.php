<?php
/**
 * DokuWiki Plugin HighlightJS
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author Ben van Magill <ben.vanmagill16@gmail.com>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

class action_plugin_codehighlightjs extends DokuWiki_Action_Plugin
{
    // register hook
    public function register(Doku_Event_Handler $controller)
    {
        $controller->register_hook('TOOLBAR_DEFINE', 'AFTER', $this, 'insert_button', array());
        $controller->register_hook('TOOLBAR_DEFINE', 'AFTER', $this, 'insert_button_inline', array());
        $controller->register_hook('TPL_METAHEADER_OUTPUT', 'BEFORE', $this, 'load_highlightjs');
    }

    /**
     * Insert a toolbar button
     */
    public function insert_button(Doku_Event $event) {
        $event->data[] = array(
            'type' => 'format',
            'title' => 'Insert code',
            'icon' => '../../plugins/codehighlightjs/images/code.png',
            'open' => '<code php nowrap [filename.php]| Title>\n',
            'close' => '\n</code>',
        );
    }
    
    /**
     * Insert a toolbar button
     */
    public function insert_button_inline(Doku_Event $event) {
        $event->data[] = array(
            'type' => 'format',
            'title' => 'Insert code inline',
            'icon' => '../../plugins/codehighlightjs/images/inline-button.png',
            'open' => "''",
            'close' => "''",
        );
    }

    /**
     * register highlightjs script and css
     */
    public function load_highlightjs(Doku_Event $event, $param) {
        $base_url = DOKU_BASE.'/lib/plugins/codehighlightjs/highlight/';

        $event->data['script'][] = [
            'type'    => 'text/javascript',
            'charset' => 'utf-8',
            'src'     => $base_url.'highlight.min.js',
            '_data'   => '',
        ];

        // load the theme
        $skin = $this->getConf('skin');
        if (empty($skin)) {
            $skin = 'monokai-sublime';
        } 
        $event->data['link'][] = array (
                'rel'     => 'stylesheet',
                'type'    => 'text/css',
                'href'    => $base_url.'styles/'.$skin.'.min.css',
        );
    }

}
