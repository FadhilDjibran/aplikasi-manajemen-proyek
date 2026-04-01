<?php

if (!function_exists('parse_money')) {
    function parse_money($val)
    {
        if (!$val && $val !== 0 && $val !== '0') return 0;

        if (strpos($val, ',') !== false) {
            $val = str_replace('.', '', $val);
            $val = str_replace(',', '.', $val);
        } else if (substr_count($val, '.') > 1) {
            $val = str_replace('.', '', $val);
        }

        return is_numeric($val) ? (float)$val : 0;
    }
}
