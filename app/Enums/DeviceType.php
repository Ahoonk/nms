<?php

namespace App\Enums;

enum DeviceType: string
{
    case Router = 'router';
    case Switch = 'switch';
    case Firewall = 'firewall';
    case AccessPoint = 'access_point';
    case Server = 'server';
    case Storage = 'storage';
    case Printer = 'printer';
    case Camera = 'camera';
    case IoT = 'iot';
}
