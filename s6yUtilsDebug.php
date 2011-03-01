<?php

/**
 * s6yUtilsDebug
 *
 * A class dedicated to the management of debug data
 *
 * @author  Jérôme Pierre <contact@susokary.com>
 * @version V1 - March 1st 2011
 */
class s6yUtilsDebug
{

    /**
     * Prints some detailed informations about a given expression
     *
     * @author Jérôme Pierre <contact@susokary.com>
     *
     * @param mixed   $expression The given expression to print
     * @param boolean $exit       Says if the current script must stopped or not
     *
     * @return  void
     */
    static public function dump($expression = null, $exit = false)
    {
        $backtrace = debug_backtrace();

        ob_start();        
        var_dump($expression);        
        $expression = ob_get_contents();        
        ob_end_clean();

        $expression = <<<EOS
================================================================================
>> FILE     : {$backtrace[0]['file']}
>> LINE     : {$backtrace[0]['line']}
>> CLASS    : {$backtrace[1]['class']}
>> FUNCTION : {$backtrace[1]['function']}
================================================================================
$expression
================================================================================
EOS;

        if ($exit) {
            $expression .= <<<EOS
BEWARE THAT THE CURRENT SCRIPT WAS SUDDENLY STOPPED, PURSUANT TO YOUR REQUEST!
================================================================================

EOS;
        } else {
            $expression .= "\n";
        }

        if (PHP_SAPI !== 'cli') {
            $expression = str_replace(
                '>>',
                '&raquo;',
                "<pre>$expression</pre>"
            );
        }

        print($expression);

        if ($exit) {
            exit();
        }
    }

}
