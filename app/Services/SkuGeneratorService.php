<?php

namespace App\Services;

use App\Models\AturanKode;
use App\Models\Produk;

class SkuGeneratorService
{
    /**
     * Generate a unique SKU code based on product attributes and coding rules.
     */
    public static function generate(string $productName, string $category, string $variant = ''): string
    {
        // 1. Resolve active rule for this category or global fallback
        $rule = AturanKode::where(['kategori' => $category, 'is_active' => true])->first();
        if (!$rule) {
            $rule = AturanKode::whereNull('kategori')->where(['is_active' => true])->first();
        }

        $formatTipe = $rule ? $rule->format_tipe : 'INCREMENT';

        if ($formatTipe === 'PRD_STYLE') {
            // Extract color and size from variant
            $color = 'General';
            $size = 'General';

            if (!empty($variant)) {
                if (str_contains($variant, ' / ')) {
                    $parts = explode(' / ', $variant, 2);
                    $color = trim($parts[0]);
                    $size = trim($parts[1] ?? 'General');
                } elseif (str_contains($variant, '/')) {
                    $parts = explode('/', $variant, 2);
                    $color = trim($parts[0]);
                    $size = trim($parts[1] ?? 'General');
                } elseif (str_contains($variant, ' - ')) {
                    $parts = explode(' - ', $variant, 2);
                    $color = trim($parts[0]);
                    $size = trim($parts[1] ?? 'General');
                } else {
                    if (is_numeric(trim($variant))) {
                        $size = trim($variant);
                    } else {
                        $color = trim($variant);
                    }
                }
            }

            // Apply color map translation
            $colorLower = strtolower(trim($color));
            $colorMap = [
                'hitam' => 'FullBlack',
                'black' => 'FullBlack',
                'abu' => 'Grey',
                'abu-abu' => 'Grey',
                'grey' => 'Grey',
                'gray' => 'Grey',
                'army' => 'Army',
                'hijau army' => 'Army',
                'merah' => 'Red',
                'red' => 'Red',
                'putih' => 'White',
                'white' => 'White',
                'biru' => 'Blue',
                'blue' => 'Blue',
                'coklat' => 'Brown',
                'cokelat' => 'Brown',
                'brown' => 'Brown',
                'kuning' => 'Yellow',
                'yellow' => 'Yellow',
                'pink' => 'Pink',
                'merah muda' => 'Pink',
                'orange' => 'Orange',
                'jingga' => 'Orange',
                'ungu' => 'Purple',
                'purple' => 'Purple',
            ];

            $cleanColor = $colorMap[$colorLower] ?? str_replace(' ', '', ucwords(strtolower(trim($color))));
            $cleanSize = str_replace(' ', '', trim($size));

            // Resolve prefix
            $prefix = $rule ? $rule->prefix : '';
            if (empty($prefix)) {
                // Fallback: convert category to CamelCase
                $catClean = str_replace(' ', '', ucwords(strtolower(trim($category))));
                if (strtoupper(trim($category)) === 'SANDAL') {
                    $prefix = 'SandalSlide';
                } elseif (str_contains(strtoupper($category), 'SANDAL')) {
                    $prefix = 'SandalSlide';
                } elseif (strtoupper(trim($category)) === 'SEPATU') {
                    $prefix = 'Sepatu';
                } elseif (str_contains(strtoupper($category), 'SEPATU')) {
                    $prefix = 'Sepatu';
                } else {
                    $prefix = $catClean ?: 'Product';
                }
            }

            // Resolve model name from product name
            // Strip out color words, category words, size numbers
            $categoryWords = preg_split('/[\s\-\_\/]+/', strtolower(trim($category)));
            $prefixWords = preg_split('/(?=[A-Z])|[\s\-\_\/]+/', $prefix); // split prefix by camelcase or spaces
            $prefixWords = array_map('strtolower', array_filter($prefixWords));
            
            $colorWords = preg_split('/[\s\-\_\/]+/', strtolower(trim($color)));
            // Add common color terms to filter
            $colorTerms = array_merge($colorWords, ['hitam', 'black', 'abu', 'grey', 'gray', 'army', 'merah', 'red', 'putih', 'white', 'biru', 'blue', 'coklat', 'cokelat', 'brown', 'kuning', 'yellow', 'pink', 'orange', 'purple', 'kasual', 'casual']);

            $nameWords = preg_split('/[\s\-\_\/]+/', trim($productName));
            $filteredWords = [];

            foreach ($nameWords as $word) {
                $wordLower = strtolower(trim($word));
                // Skip size number
                if (is_numeric($wordLower)) continue;
                // Skip color terms
                if (in_array($wordLower, $colorTerms)) continue;
                // Skip category and prefix words
                if (in_array($wordLower, $categoryWords) || in_array($wordLower, $prefixWords)) continue;
                
                $filteredWords[] = $word;
            }

            if (!empty($filteredWords)) {
                $model = str_replace(' ', '', ucwords(strtolower(implode('', $filteredWords))));
            } else {
                // Fallback: take original product name words excluding color/size
                $fallbackWords = [];
                foreach ($nameWords as $word) {
                    $wordLower = strtolower(trim($word));
                    if (is_numeric($wordLower)) continue;
                    if (in_array($wordLower, $colorTerms)) continue;
                    $fallbackWords[] = $word;
                }
                if (!empty($fallbackWords)) {
                    $model = str_replace(' ', '', ucwords(strtolower(implode('', $fallbackWords))));
                } else {
                    $model = 'General';
                }
            }

            if (strtolower($prefix) === strtolower($model)) {
                $baseSku = "{$prefix}-{$cleanColor}-{$cleanSize}";
            } else {
                $baseSku = "{$prefix}-{$model}-{$cleanColor}-{$cleanSize}";
            }

            // Deduplicate: check if exists in DB
            $existing = Produk::where('kode_barang', $baseSku)->exists();
            if ($existing) {
                // Find next increment
                $i = 1;
                while (Produk::where('kode_barang', "{$baseSku}-{$i}")->exists()) {
                    $i++;
                }
                return "{$baseSku}-{$i}";
            }

            return $baseSku;
        }

        // --- Standard INCREMENT format logic ---
        $prefix = $rule ? $rule->prefix : 'LAF';
        $includeCat = $rule ? $rule->include_category_prefix : true;
        $includeInitials = $rule ? $rule->include_product_initials : true;
        $includeVariant = $rule ? $rule->include_variant_code : true;
        $digits = $rule ? $rule->increment_digits : 3;

        // 2. Build the token array
        $tokens = [];

        // Add Prefix (e.g. LAF)
        if (!empty($prefix)) {
            $tokens[] = strtoupper(trim($prefix));
        }

        // Add Category Prefix (e.g. SANDAL -> SAN)
        if ($includeCat && !empty($category)) {
            $catClean = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $category));
            $tokens[] = substr($catClean, 0, 3);
        }

        // Add Product Initials (e.g. Sandal Keren -> SK)
        if ($includeInitials && !empty($productName)) {
            $words = preg_split('/[\s\-\_\/]+/', trim($productName));
            $initials = '';
            
            if (count($words) === 1) {
                $wordClean = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $words[0]));
                $initials = substr($wordClean, 0, 3);
            } else {
                foreach ($words as $word) {
                    $char = substr(preg_replace('/[^A-Za-z0-9]/', '', $word), 0, 1);
                    if (!empty($char)) {
                        $initials .= $char;
                    }
                }
                $initials = substr($initials, 0, 3);
            }
            
            if (!empty($initials)) {
                $tokens[] = strtoupper($initials);
            }
        }

        // Add Variant Code (e.g. Hitam / 40 -> H40)
        if ($includeVariant && !empty($variant)) {
            $parts = preg_split('/[\s\-\_\/]+/', trim($variant));
            $codeParts = [];
            
            foreach ($parts as $part) {
                $part = trim($part);
                if (is_numeric($part)) {
                    $codeParts[] = $part;
                } else {
                    $cleaned = preg_replace('/[^A-Za-z0-9]/', '', $part);
                    if (!empty($cleaned)) {
                        $codeParts[] = substr($cleaned, 0, 1); // first char
                    }
                }
            }
            
            $variantCode = strtoupper(implode('', $codeParts));
            $variantCode = substr($variantCode, 0, 6); // Keep it reasonably short
            
            if (!empty($variantCode)) {
                $tokens[] = $variantCode;
            }
        }

        // Combine tokens to form the base pattern
        $basePattern = implode('-', $tokens);
        $basePatternWithDash = $basePattern . '-';

        // 3. Query existing codes to find the next unique numeric increment
        $existingCodes = Produk::whereRaw('kode_barang LIKE ?', [$basePatternWithDash . '%'])
            ->pluck('kode_barang')
            ->toArray();

        $maxNum = 0;
        foreach ($existingCodes as $code) {
            $suffix = substr($code, strlen($basePatternWithDash));
            if (preg_match('/^\d+$/', $suffix)) {
                $maxNum = max($maxNum, (int)$suffix);
            }
        }

        $nextNum = $maxNum + 1;
        $incrementStr = str_pad((string)$nextNum, $digits, '0', STR_PAD_LEFT);

        return $basePatternWithDash . $incrementStr;
    }
}
