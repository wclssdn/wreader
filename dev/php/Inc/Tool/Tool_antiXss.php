<?php
class Tool_antiXss
{
    public static function clean_danger_param($string)
    {
    
        $keywords = array(
        "/eval[ ]*\(/i" =>     "eval_r(",
        "/expression[ ]*\(/isU" =>     "expression_r(",
        "/getElementsByTagName[ ]*\(/isU" =>     "getElementsByTagName_r(",
        '/onerror(_r)?[ ]*=[ ]*([\'|\"])[^\'\"]*([\'|\"])/isU' => '',
        '/ALLOWSCRIPTACCESS[ ]*=[ ]*([\'|\"])[^\'\"][^(samedomain)]*([\'|\"])/isU' => '',
        '/ALLOWSCRIPTACCESS_OLD[ ]*=[ ]*([\'|\"])[^\'\"]*([\'|\"])/isU' => '',
        '/auto[a-z_]*[ ]*=[ ]*([\'|\"])[^\'\"]*([\'|\"])/isU' => '',
        '/(AllowScriptAccess=\"samedomain\")/i' =>'allowScriptAccess="never" allowNetworking="internal" autostart="0" ',
        '/\<embed  allowScriptAccess="never" allowNetworking="internal" autostart="0"(.*)(music.sina.com.cn)(.*)\>/isU' =>'<embed  allownetworking="all" allowscriptaccess="always" \\1\\2\\3>',
        '/(\<object .*\>)/isU' => '\\1 <param name="autoStart" value="0"><param name="allowScriptAccess" value="never"><param name="allowNetworking" value="internal">',
        '/document\.getElementById/isU' => 'document.getElementByIdx',
        '/window\.i/i' => 'windows.i',
        '/data:text\/html/i' => '',
        '/BorDer-riGHT: black 1px solid; BorDer-Top: black 1px solid; FonT-siZe: 12px; LeFT:/i' => 'display:none;left:',
        );
    
        $string = preg_replace(array_keys($keywords),array_values($keywords),$string);
        return $string;
    }
}
?>