<?php

namespace App\Helpers;

class Barcode128
{
    /**
     * Code 128 widths patterns for values 0 to 106.
     * Each string represents the widths of alternating bars and spaces.
     */
    private static array $patterns = [
        '212222', '222122', '222221', '121223', '121322', '131222', '122213', '122312', '132212', '221213', // 0-9
        '221312', '231212', '112232', '122132', '122231', '113222', '123122', '123221', '223211', '221132', // 10-19
        '221231', '213212', '223112', '312131', '311222', '321122', '321221', '312212', '322112', '322211', // 20-29
        '212123', '212321', '232121', '111323', '131123', '131321', '112313', '132113', '132311', '211313', // 30-39
        '231113', '231311', '112133', '112331', '132131', '113123', '113321', '133121', '313121', '211331', // 40-49
        '231131', '213113', '213311', '213131', '311123', '311321', '331121', '312113', '312311', '332111', // 50-59
        '314111', '221411', '431111', '111224', '111422', '121124', '121421', '141122', '141221', '112214', // 60-69
        '112412', '122114', '122411', '142112', '142211', '241211', '221114', '413111', '241112', '134111', // 70-79
        '111242', '121142', '121241', '114212', '124112', '124211', '411212', '421112', '421211', '212141', // 80-89
        '214121', '412121', '111143', '111341', '131141', '114113', '114311', '411113', '411311', '113141', // 90-99
        '114131', '311141', '411131', '211412', '211214', '211232', '2331112' // 100-106
    ];

    /**
     * Generate Code 128 B SVG representation.
     */
    public static function getSvg(string $text, int $height = 60, int $barWidth = 2): string
    {
        $text = trim($text);
        if (empty($text)) {
            $text = ' ';
        }

        // Start character Code 128 B is 104
        $checksum = 104;
        $encoded = self::$patterns[104];
        
        $len = strlen($text);
        for ($i = 0; $i < $len; $i++) {
            $char = $text[$i];
            $value = ord($char) - 32; // Map printable ASCII to 0-102
            if ($value < 0 || $value > 102) {
                $value = 0; // Fallback to space
            }
            $encoded .= self::$patterns[$value];
            $checksum += $value * ($i + 1);
        }
        
        $checkDigit = $checksum % 103;
        $encoded .= self::$patterns[$checkDigit];
        $encoded .= self::$patterns[106]; // Stop character (value 106)
        
        // Count total modules to set proper SVG size
        $totalModules = 0;
        $encLen = strlen($encoded);
        for ($i = 0; $i < $encLen; $i++) {
            $totalModules += intval($encoded[$i]);
        }
        
        $svgWidth = $totalModules * $barWidth;
        $svgHeight = $height;
        
        $svg = "<svg width=\"{$svgWidth}\" height=\"{$svgHeight}\" viewBox=\"0 0 {$svgWidth} {$svgHeight}\" xmlns=\"http://www.w3.org/2000/svg\">\n";
        $svg .= "  <g fill=\"#000000\">\n";
        
        $x = 0;
        $isBar = true; // Start with bar, then space, alternating
        for ($i = 0; $i < $encLen; $i++) {
            $w = intval($encoded[$i]) * $barWidth;
            if ($isBar) {
                $svg .= "    <rect x=\"{$x}\" y=\"0\" width=\"{$w}\" height=\"{$svgHeight}\" />\n";
            }
            $x += $w;
            $isBar = !$isBar;
        }
        
        $svg .= "  </g>\n";
        $svg .= "</svg>";
        
        return $svg;
    }

    /**
     * Generate a premium product tag containing product details and Code 128 B barcode.
     */
    public static function getTagSvg(string $sku, string $name, string $variation): string
    {
        $sku = trim($sku);
        if (empty($sku)) {
            $sku = ' ';
        }

        // Generate barcode encoding sequence
        $checksum = 104;
        $encoded = self::$patterns[104];
        
        $len = strlen($sku);
        for ($i = 0; $i < $len; $i++) {
            $char = $sku[$i];
            $value = ord($char) - 32;
            if ($value < 0 || $value > 102) {
                $value = 0;
            }
            $encoded .= self::$patterns[$value];
            $checksum += $value * ($i + 1);
        }
        
        $checkDigit = $checksum % 103;
        $encoded .= self::$patterns[$checkDigit];
        $encoded .= self::$patterns[106];
        
        // Count total modules
        $totalModules = 0;
        $encLen = strlen($encoded);
        for ($i = 0; $i < $encLen; $i++) {
            $totalModules += intval($encoded[$i]);
        }
        
        // Card Dimensions
        $barWidth = 1.5; // Enforce a readable minimum bar width for clean rendering/scanning
        $barcodeWidth = $totalModules * $barWidth;
        $cardWidth = max(320, (int)ceil($barcodeWidth + 60)); // Centered with 30px padding on each side
        $cardHeight = 190;

        $barcodeHeight = 50;
        
        // Centering calculation
        $barcodeX = ($cardWidth - $barcodeWidth) / 2;
        
        // Escape content
        $escSku = htmlspecialchars($sku, ENT_QUOTES, 'UTF-8');
        $escName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $escVariation = htmlspecialchars($variation, ENT_QUOTES, 'UTF-8');
        
        // Build beautiful SVG
        $svg = "<svg width=\"{$cardWidth}\" height=\"{$cardHeight}\" viewBox=\"0 0 {$cardWidth} {$cardHeight}\" xmlns=\"http://www.w3.org/2000/svg\">\n";
        
        // Premium Card styling
        // White background with terracotta border
        $svg .= "  <rect x=\"5\" y=\"5\" width=\"" . ($cardWidth - 10) . "\" height=\"" . ($cardHeight - 10) . "\" rx=\"12\" ry=\"12\" fill=\"#ffffff\" stroke=\"#854836\" stroke-width=\"2.5\" />\n";
        
        // Amber line accent under header
        $svg .= "  <line x1=\"15\" y1=\"35\" x2=\"" . ($cardWidth - 15) . "\" y2=\"35\" stroke=\"#FFB22C\" stroke-width=\"2\" stroke-linecap=\"round\" />\n";
        
        // Title text / Brand
        $svg .= "  <text x=\"15\" y=\"26\" font-family=\"system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif\" font-size=\"10\" font-weight=\"900\" fill=\"#854836\" letter-spacing=\"1.5\">WMS LAF FOOTWEAR</text>\n";
        
        // Product Name (Truncate if too long)
        $displayName = strlen($escName) > 28 ? substr($escName, 0, 25) . '...' : $escName;
        $svg .= "  <text x=\"15\" y=\"56\" font-family=\"system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif\" font-size=\"13\" font-weight=\"bold\" fill=\"#111827\">{$displayName}</text>\n";
        
        // Variation badge/text
        $svg .= "  <text x=\"15\" y=\"74\" font-family=\"system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif\" font-size=\"10\" font-weight=\"600\" fill=\"#6b7280\">Varian: {$escVariation}</text>\n";
        
        // Barcode bars group
        $svg .= "  <g transform=\"translate({$barcodeX}, 90)\" fill=\"#000000\">\n";
        
        $x = 0;
        $isBar = true;
        for ($i = 0; $i < $encLen; $i++) {
            $w = intval($encoded[$i]) * $barWidth;
            if ($isBar) {
                // Round values for high-precision rendering
                $roundedX = round($x, 2);
                $roundedW = round($w, 2);
                $svg .= "    <rect x=\"{$roundedX}\" y=\"0\" width=\"{$roundedW}\" height=\"{$barcodeHeight}\" />\n";
            }
            $x += $w;
            $isBar = !$isBar;
        }
        
        $svg .= "  </g>\n";
        
        // Monospaced text below barcode
        $svg .= "  <text x=\"" . ($cardWidth / 2) . "\" y=\"162\" font-family=\"'Courier New', Courier, monospace\" font-size=\"11\" font-weight=\"bold\" fill=\"#111827\" text-anchor=\"middle\" letter-spacing=\"3\">{$escSku}</text>\n";
        
        $svg .= "</svg>";
        
        return $svg;
    }
}
