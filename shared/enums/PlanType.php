<?php
namespace app\shared\enums;

enum PlanType: string {
    case MONTHLY = 'MENSUAL';
    case HALF_MONTH = 'MEDIO_MES';
    case SESSION = 'SESSION';
}