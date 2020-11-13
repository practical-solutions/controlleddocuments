<?php
/**
 * Controlled Documents
 *
 * @license    Unlicense
 * @author     Gero Gothe <practical@medizin-lernen.de>
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_controlleddocuments extends DokuWiki_Syntax_Plugin {

    private $dw2pdf_inst = false;

    function getType(){return 'substition';}

    function getSort(){return 59;}

    # Checks if dw2pdf is installed/activated
    function __construct() {
        $list = plugin_list();
        if(in_array('dw2pdf',$list)) {
            $this->dw2pdf_inst = true;
        }
    }

   function connectTo($mode) {
        $this->Lexer->addEntryPattern('<control>',$mode,'plugin_controlleddocuments');
    }

   
    function postConnect() {      
        $this->Lexer->addExitPattern('</control>', 'plugin_controlleddocuments');
    }


    /* Handle the match */
    function handle($match, $state, $pos, Doku_Handler $handler){
         if ($state == DOKU_LEXER_UNMATCHED) {
            return $match;
        }
          
        return false;
    }


    public function render($format, Doku_Renderer $renderer, $data) {
        
        if ($data === false) return;
        
        if($format == 'xhtml') {
            $renderer->info['cache'] = false;
            
            $lines = explode("\n",$data);
            
            #print_r($lines);
            foreach ($lines as $l) {
                if (strpos($l,':')>0) {
                    $t = explode(':',$l);
                    $l1 .= '<th>'.$t[0].'</th>';
                    $l2 .= '<td>'.$t[1].'</td>';
                }
            }
            
            if ($this->getConf("showtable")) $renderer->doc .= "<table class='plugin_controlledocuments_table'><tr>$l1</tr><tr>$l2</tr></table>";
            
            return true;
        }
        return false;
    }


}

//Setup VIM: ex: et ts=4 enc=utf-8 :
