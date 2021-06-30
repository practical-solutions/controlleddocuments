<?php
/**
 * Controlled Documents
 *
 * @license    GPL2
 * @author     Gero Gothe <gero.gothe@medizindoku.de>
 */
 
class admin_plugin_controlleddocuments extends DokuWiki_Admin_Plugin {
     
    var $output = 'COMMAND: none';
    
    
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
    

    function getMenuText($language){
		return $this->getLang("plugin title");
	}
	
    /**
    * handle user request
    */
    function handle() {

        if (!$this->dw2pdf_inst) return; # Check if dw2pdf is activated
        
        if (!isset($_REQUEST['cmd'])) return; # first time - nothing to do
        if (!is_array($_REQUEST['cmd'])) return;
     
        // verify valid values
        $command = key($_REQUEST['cmd']);

        # Install chosen templates
        if (strpos($command,"install:")===0) {
            $command = $_REQUEST['template'];

            msg($this->getLang('template').' <code>'.$command.'</code> '.$this->getLang('install msg'),1);

            $this->recurse_copy(DOKU_PLUGIN."/controlleddocuments/tpl/$command",DOKU_PLUGIN."/dw2pdf/tpl/$command");
        }

    }
     
    /**
    * output appropriate html
    */
    function html() {
        global $ID;

        echo '<form action="'.wl($ID).'" method="post">';
     
        // output hidden values to ensure dokuwiki will return back to this plugin
        echo '  <input type="hidden" name="do"   value="admin" />';
        echo '  <input type="hidden" name="page" value="'.$this->getPluginName().'" />';

        # get installed templates
        $dw = $this->dirList(DOKU_PLUGIN."/dw2pdf/tpl/");
        # get availabe templates of the plugin
        $opt = $this->dirList(DOKU_PLUGIN."/controlleddocuments/tpl/");

        echo '<h1>'.$this->getLang("plugin title").'</h1>';
        echo $this->getLang('template info').'.<br><br>';
        
        # build list of templates
        if ($this->dw2pdf_inst) {
            echo '<table><tr><th>'.$this->getLang('template').'</th><th>Status</th></tr>';
            foreach ($opt as $tpl_lenkung) {
                echo "<tr><td>$tpl_lenkung</td><td>";
                if (!in_array($tpl_lenkung,$dw)) echo 'NOT';
                echo " installed</td></tr>";
            }
            echo "</table>";

            echo '<select name="template">';
            
            foreach ($opt as $s) echo "<option>$s</option>";
            
            echo '</select> ';
            
            
            
            ptln('<input type="submit" name="cmd[install:lenkung]" value="Install/Update Lenkung-Template">');
            echo "<br>".$this->getLang('install fail');
        } else {ptln('dw2pdf-Plugin not installed.');}
        
        ptln("</form>");
        
        echo "<br><br><hr><h2>Overwritable Options</h2>";
        
        echo '<table><tr><th>Parameter</th><th>Template Replacement</th></tr>';
        echo '<tr><td><code>'.$this->getLang("option:title").'</code></td><td><code>@DOK-TITLE@</code></td></tr>';
        echo '<tr><td><code>'.$this->getLang("option:author-approve").'</code></td><td><code>@AUTHOR-APPROVE@</code></td></tr>';
        echo '<tr><td><code>'.$this->getLang("option:date-approve").'</code></td><td><code>@DATE-APPROVE@</code></td></tr>';
        echo '<tr><td><code>'.$this->getLang("option:author-valid").'</code></td><td><code>@AUTHOR-VALID@</code></td></tr>';
        echo '<tr><td><code>'.$this->getLang("option:date-valid").'</code></td><td><code>@DATE-VALID@</code></td></tr>';
        echo '<tr><td><code>'.$this->getLang("option:author-mark").'</code></td><td><code>@AUTHOR-MARK@</code></td></tr>';
        echo '<tr><td><code>'.$this->getLang("option:date-mark").'</code></td><td><code>@DATE-MARK@</code></td></tr>';
        echo '<tr><td><code>'.$this->getLang("option:version").'</code></td><td><code>@REVISION@</code></td></tr>';
        echo '</table>';

        ptln('<br><br><hr><code>'.htmlspecialchars($this->output).'</code>');
    }
    
    # Returns list of directories
    function dirList($path){
        
        $dirs = array();

        // directory handle
        $dir = dir($path);

        while (false !== ($entry = $dir->read())) {
            if ($entry != '.' && $entry != '..') {
                if (is_dir($path . '/' .$entry)) {
                    $dirs[] = $entry;
                }
            }
        }

        return $dirs;
    }

    # Copies a directory
    function recurse_copy($src,$dst) {
        $dir = opendir($src);
        
        @mkdir($dst);
        while(false !== ( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if ( is_dir($src . '/' . $file) ) {
                    recurse_copy($src . '/' . $file,$dst . '/' . $file);
                }
                else {
                    copy($src . '/' . $file,$dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    } 
     
}

