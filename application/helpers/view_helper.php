<?php
if (!function_exists('load_cleaned_view'))
{
    function load_cleaned_view($view, $data = NULL, $return_as_string = FALSE)
    {
        $CI =& get_instance();
        $content = $CI->load->view($view, $data, $return_as_string);

        if(!empty($data))
        {
            foreach ($data as $key => $value) $data[$key] = NULL;
            $CI->load->view($view, $data, TRUE);
        }
        return $content;
    }
}
