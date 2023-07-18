<?php

class Process
{

    function do_in_background($url, $params = array(), $requestType = '')
    {
        if (is_array($params)) {
            $post_string = http_build_query($params);
        } else {
            $post_string = '';
        }
        $parts = parse_url($url);
        $errno = 0;
        $errstr = "";

        $fp = null;
        if (strtolower($parts['scheme']) == "https") {
            $fp = fsockopen('ssl://' . $parts['host'], isset($parts['port']) ? $parts['port'] : 443, $errno, $errstr, 3600);
        } else {
            $fp = fsockopen($parts['host'], isset($parts['port']) ? $parts['port'] : 80, $errno, $errstr, 3600);
        }

        if (!$fp) {
            error_log($errstr . "(" . $errno . ")");
        }


        $out = $requestType . " " . $parts['path'] . " HTTP/1.1\r\n";
        $out .= "Host: " . $parts['host'] . "\r\n";
        // $out .= "Content-Type: application/x-www-form-urlencoded\r\n";
        // $out .= "Content-Length: " . strlen($post_string) . "\r\n";
        $out .= "Content-Type: application/json\r\n";
        $out .= "Content-Length: " . strlen($post_string) . "\r\n";
        $out .= "Connection: Close\r\n\r\n";
        if (isset($post_string))
            $out .= $post_string;

        fwrite($fp, $out);
        fclose($fp);
    }
}
