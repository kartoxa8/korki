<?php
declare(strict_types=1);

const DB_HOST = 'localhost';
const DB_USER = 'root';
const DB_PASSWORD = '';
const DB_NAME = 'korki_portal';

const ADMIN_LOGIN = 'Admin';
const ADMIN_PASSWORD = 'KorokNET';

const COURSE_LIST = [
    'Основы алгоритмизации и программирования',
    'Основы веб-дизайна',
    'Основы проектирования баз данных',
];

const PAYMENT_METHODS = [
    'cash' => 'Наличными',
    'transfer' => 'Переводом по номеру телефона',
];

const REQUEST_STATUSES = [
    'new' => 'Новая',
    'in_progress' => 'Идет обучение',
    'completed' => 'Обучение завершено',
];
