<?php

/**
 * s6yUtilsColor
 *
 * A class dedicated to the management of color data
 *
 * @author  Jérôme Pierre <contact@susokary.com>
 * @version V1 - March 1st 2011
 */
class s6yUtilsColor
{

    /**
     * Provides an hexadecimal value from a given RGB one
     *
     * @author Jérôme Pierre <contact@susokary.com>
     *
     * @param string $value The given RGB value
     *
     * @return string The relevant hexadecimal one (6 chars)
     */
    static public function rgbToHex($value)
    {
        return implode(
            array_map(
                create_function(
                    '$value',
                    'return str_pad(
                        dechex(
                            $value
                        ),
                        2,
                        "0",
                        STR_PAD_LEFT
                    );'
                ),
                str_split(
                    str_pad(
                        $value,
                        9,
                        '0',
                        STR_PAD_LEFT
                    ),
                    3
                )
            )
        );
    }

    /**
     * Provides a RGB value from a given hexadecimal one
     *
     * @author Jérôme Pierre <contact@susokary.com>
     *
     * @param string $value The given hexadecimal value
     *
     * @return string The relevant RGB one (9 chars)
     */
    static public function hexToRgb($value)
    {
        return implode(
            array_map(
                create_function(
                    '$value',
                    'return str_pad(
                        hexdec(
                            str_pad(
                                $value,
                                2,
                                $value,
                                STR_PAD_LEFT
                            )
                        ),
                        3,
                        "0",
                        STR_PAD_LEFT
                    );'
                ),
                str_split(
                    $value,
                    round(
                        $length / 3
                    )
                )
            )
        );
    }

}
