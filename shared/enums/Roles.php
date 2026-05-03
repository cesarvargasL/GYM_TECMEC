<?php
namespace app\shared\enums;

enum Roles: string {
    case ADMINISTRATOR = 'ADMINISTRADOR';
    case CLIENT = 'CLIENTE';
    case SUPER_ADMIN = 'SUPER_ADMIN';
}