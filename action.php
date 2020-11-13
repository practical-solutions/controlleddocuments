<?php
/**
 * Controlled Documents
 *
 * @license    MIT
 * @author     Gero Gothe <practical@medizin-lernen.de>
 */


# must be run within Dokuwiki
if(!defined('DOKU_INC')) die();


class action_plugin_controlleddocuments extends DokuWiki_Action_Plugin {

    private $dw2pdf_inst  = false;
    private $approve_inst = false;

    function __construct() {
        $list = plugin_list();
        if(in_array('dw2pdf',$list)) {
            $this->dw2pdf_inst = true;
        }
        if(in_array('approve',$list)) {
            $this->approve_inst = true;
        }
    }


    public function register(Doku_Event_Handler $controller) {

        $controller->register_hook('PLUGIN_DW2PDF_REPLACE', 'BEFORE', $this, 'replacement_before', null, 100);

    }


    function extractTags($id){
        
        $page = rawwiki($id);
        $start = strpos($page,"<control>");
        $end = strpos($page,"</control>");
        
        if ($start === false || $end ===  false) return false;

        $start += 10;
        $len = $end-$start-1;

        $lines = explode("\n",substr($page,$start,$len));
        $res = Array();
        foreach ($lines as &$l) {
            $t= explode(":",$l);
            $t[0] = mb_strtoupper($t[0]);
            
            if ($t[0] == $this->getLang("option:title")) $t[0]          = 'DOK-TITLE';
            if ($t[0] == $this->getLang("option:author-approve")) $t[0] = 'AUTHOR-APPROVE';
            if ($t[0] == $this->getLang("option:date-approve")) $t[0]   = 'DATE-APPROVE';
            if ($t[0] == $this->getLang("option:author-valid")) $t[0]   = 'AUTHOR-VALID';
            if ($t[0] == $this->getLang("option:date-valid")) $t[0]     = 'DATE-VALID';
            if ($t[0] == $this->getLang("option:author-mark")) $t[0]    = 'AUTHOR-MARK';
            if ($t[0] == $this->getLang("option:date-mark")) $t[0]      = 'DATE-MARK';
            if ($t[0] == $this->getLang("option:version")) $t[0]        = 'REVISION';
            
            if ($t[0] == strtoupper($this->getConf("option1"))) {
                $t[0] = 'OPTION1';
                $t[1] = $this->getConf("prefix1").$t[1];
            }

            if ($t[0] == strtoupper($this->getConf("option2"))) {
                $t[0]   = 'OPTION2';
                $t[1] = $this->getConf("prefix2").$t[1];
            }
            
            $res[$t[0]] = $t[1];
        }

        return $res;
       
    }



    function replacement_before(Doku_Event $event, $param) {
        global $conf;
        global $INFO;
        global $auth;
        
        $tags = $this->extractTags($INFO['id']);

        foreach ($tags as $key => $name) {
            $event->data['replace']['@'.$key.'@'] = $name;
        }
        
        #If no title is given: Use the first title of the document
        $k = array_keys($tags);

        # Use document title if no title has been set
        if (!in_array("DOK-TITLE",$k)) $event->data['replace']['@DOK-TITLE@'] = p_get_first_heading($INFO['id']);
        if (!in_array("REVISION",$k)) $event->data['replace']['@REVISION@'] = $this->getLang("draft");
        
        # Leave blank space if options have not been set
        $blanks = Array("OPTION1","OPTION2","AUTHOR-VALID","DATE-VALID","AUTHOR-MARK","DATE-MARK","AUTHOR-APPROVE","DATE-APPROVE");
        foreach ($blanks as $b) {
            if (!in_array($b,$k)) $event->data['replace']["@$b@"] = "";
        }
        
        #if (!in_array("OPTION1",$k)) $event->data['replace']['@OPTION1@'] = "";
        #if (!in_array("OPTION2",$k)) $event->data['replace']['@OPTION2@'] = "";
        
        if ($this->approve_inst) $approve = $this->approve_data();
        
        # Sets data from approve-plugin only if it has NOT been set before manually
        foreach ($approve as $key => $name){
            if (!in_array($key,$k)) $event->data['replace']["@$key@"] = $approve[$key];
        }

        $event->data['replace']['@LASTAUTHOR@'] = $auth->getUserData($INFO['user'])['name'];

    }


    function approve_data() {
        global $INFO;
        global $auth;

        try {
            /** @var \helper_plugin_approve_db $db_helper */
            $db_helper = plugin_load('helper', 'approve_db');
            $sqlite = $db_helper->getDB();
        } catch (Exception $e) {
            return;
        }
        
        $last_change_date = @filemtime(wikiFN($INFO['id']));
        $rev = !$INFO['rev'] ? $last_change_date : $INFO['rev'];

        $res = $sqlite->query('SELECT ready_for_approval, ready_for_approval_by,
                                        approved, approved_by, version
                                FROM revision
                                WHERE page=? AND rev=?', $INFO['id'], $rev);

        $approve = $sqlite->res_fetch_assoc($res);
        
        $ret = Array();
        
        if ($approve['approved_by'] == false) {
            $ret['AUTHOR-APPROVE'] = "<b>".$this->getLang("unapproved")."</b>";
            $ret['AUTHOR-VALID'] = "";
        } else {
            $ret['AUTHOR-APPROVE'] = $auth->getUserData($approve['approved_by'])['name'];
            $ret['AUTHOR-VALID'] = $ret['AUTHOR-APPROVE'];
        }
        
        if ($approve['approved'] == false) {
            $ret['DATE-APPROVE'] = "<b>".$this->getLang("unapproved")."</b>";
            $ret['DATE-VALID'] = "";
        } else {
            $date = new DateTime($approve['approved']);
            $ret['DATE-APPROVE'] = $date->format('d.m.Y');
            $ret['DATE-VALID'] = $ret['DATE-APPROVE'];
        }
        
        if ($approve['ready_for_approval'] == false) {
            if ($approve['approved'] == false) {
                $ret['DATE-MARK'] = "";
            } else $ret['DATE-MARK'] = $ret['DATE-APPROVE'];
        } else {
            $date2 = new DateTime($approve['ready_for_approval']);
            $ret['DATE-MARK'] = $date2->format('d.m.Y');
        }
        
        if ($approve['ready_for_approval_by'] == false) {
            if ($approve['approved_by'] == false) {
                $ret['AUTHOR-MARK'] = "";
            } else $ret['AUTHOR-MARK'] = $ret['AUTHOR-APPROVE'];
        } else $ret['AUTHOR-MARK'] = $auth->getUserData($approve['ready_for_approval_by'])['name'];
        
        if ($approve['version'] == false) {
            $ret['REVISION'] = "<span style='color:red'>".$this->getLang("draft")."</span>";
        } else {
            $ret['REVISION'] = $approve['version'];
        }
        
        return $ret;
    }


   
}

//Setup VIM: ex: et ts=4 enc=utf-8 :
