<?php

namespace Enum;

use MyCLabs\Enum\Enum;


class UserRole extends Enum
{
    const USUARIO = 2;    
    const ADMIN = 1;

    public static function IsValidArea($area)
    {
        switch ($area) {
            case "ADMIN":
                return true;            
            case "USUARIO":
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
            case UserRole::USUARIO:
                return "USUARIO";
        }
    }

    public static function GetVal($role)
    {
        switch ($role) {
            case "ADMIN":
                return UserRole::ADMIN;           
            case "USUARIO":
                return UserRole::USUARIO;
            default:
                return false;
        }
    }
}
