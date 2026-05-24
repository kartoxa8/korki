<?php
declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function old(string $key, array $source, string $default = ''): string
{
    return (string)($source[$key] ?? $default);
}

function validate_training_date(string $date): ?string
{
    $date = trim($date);

    if (!preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $date)) {
        return null;
    }

    [$day, $month, $year] = array_map('intval', explode('.', $date));

    if (!checkdate($month, $day, $year)) {
        return null;
    }

    return sprintf('%04d-%02d-%02d', $year, $month, $day);
}

function format_training_date(?string $date): string
{
    if (!$date) {
        return '';
    }

    $timestamp = strtotime($date);
    return $timestamp ? date('d.m.Y', $timestamp) : $date;
}
