<?php

namespace App\Helpers;

class CategoryHelper
{
    /**
     * Get full category name from code or name
     */
    public static function getFullName($category): string
    {
        if (is_object($category)) {
            $name = $category->name ?? '';
            $type = $category->type ?? '';
        } else {
            $name = $category;
            $type = $category;
        }
        
        // If already a full name, return it
        if (stripos($name, "Men's Singles") !== false || stripos($name, "Women's Singles") !== false || 
            stripos($name, "Men's Doubles") !== false || stripos($name, "Women's Doubles") !== false ||
            stripos($name, "Mixed Doubles") !== false) {
            return $name;
        }
        
        // Map codes to full names
        $code = strtoupper($type ?: $name);
        $map = [
            'MS' => "Men's Singles",
            'WS' => "Women's Singles",
            'MD' => "Men's Doubles",
            'WD' => "Women's Doubles",
            'XD' => "Mixed Doubles",
        ];
        
        return $map[$code] ?? $name;
    }
    
    /**
     * Get category code from name
     */
    public static function getCode($name): string
    {
        $name = strtolower($name ?? '');
        if (str_contains($name, 'mixed')) {
            return 'XD';
        } elseif (str_contains($name, "men's doubles") || str_contains($name, 'mens doubles')) {
            return 'MD';
        } elseif (str_contains($name, "women's doubles") || str_contains($name, 'womens doubles')) {
            return 'WD';
        } elseif (str_contains($name, "men's singles") || str_contains($name, 'mens singles')) {
            return 'MS';
        } elseif (str_contains($name, "women's singles") || str_contains($name, 'womens singles')) {
            return 'WS';
        }
        return 'MS';
    }
}

