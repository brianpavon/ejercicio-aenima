<?php

namespace Enum;

use MyCLabs\Enum\Enum;


class UserRole extends Enum
{
    const USER = 2;    
    const ADMIN = 1;

    public static function IsValidArea($area)
    {
        switch ($area) {
            case "ADMIN":
                return true;            
            case "USER":
                return true;
            default:
                return false;
        }
    }

    public static function GetDescription($role)
    {
        switch ($role) {
            case UserRole::ADMIN:
                return "ADMIN";            
            case UserRole::USER:
                return "USER";
        }
    }

    public static function GetVal($role)
    {
        switch ($role) {
            case "ADMIN":
                return UserRole::ADMIN;           
            case "USER":
                return UserRole::USER;
            default:
                return false;
        }
    }
}
