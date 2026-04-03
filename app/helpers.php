<?php 


if (!function_exists('getPerPage')) {
    function getPerPage()
    {
        return request()->query('per_page', 99999999);
    }
} 