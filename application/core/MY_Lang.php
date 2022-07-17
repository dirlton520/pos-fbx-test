<?php
class MY_Lang extends CI_Lang
{
    function __construct()
    {
        parent::__construct();
    }
    
    function switch_to($idiom)
    {
        $CI =& get_instance();
        if(is_string($idiom))
        {
            $CI->config->set_item('language',$idiom);
            $loaded = $this->is_loaded;
            $this->is_loaded = array();
                
            foreach(array_keys($loaded) as $file)
            {
                $this->load(str_replace('_lang.php','',$file));    
            }
        }
    }
}

?>
