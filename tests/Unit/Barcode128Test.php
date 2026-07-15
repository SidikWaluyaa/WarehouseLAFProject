<?php

namespace Tests\Unit;

use App\Helpers\Barcode128;
use PHPUnit\Framework\TestCase;

class Barcode128Test extends TestCase
{
    public function test_tag_svg_is_generated_correctly(): void
    {
        $sku = 'SHORT';
        $svg = Barcode128::getTagSvg($sku, 'Test Name', 'Black / 40');

        $this->assertStringContainsString('<svg', $svg);
        $this->assertStringContainsString('SHORT', $svg);
        $this->assertStringContainsString('Test Name', $svg);
        $this->assertStringContainsString('Varian: Black / 40', $svg);
    }

    public function test_barcode_does_not_overflow_card_width_on_long_sku(): void
    {
        // A very long SKU that would normally overflow with fixed 1.8 bar width
        $sku = 'SandalSlide-Gorka-Grey-39-Extremely-Long-SKU-To-Trigger-Scaling';
        $svg = Barcode128::getTagSvg($sku, 'Test Name', 'Grey / 39');

        // Parse the generated SVG to check that elements are scaled
        // The cardWidth is 320, padding is 20px on each side, so max barcode width is 280.
        // We've calculated the barcode width and translated it to center it.
        // Let's assert that the generated barcode has a valid structure and width.
        $this->assertStringContainsString('<svg', $svg);
        $this->assertStringContainsString($sku, $svg);
        
        // Use regex to find the translate position of the barcode group
        // transform="translate(barcodeX, 90)"
        // barcodeX should be >= 20px to fit within the card (since max barcode width is 280,
        // (320 - barcodeWidth) / 2 where barcodeWidth <= 280, so barcodeX >= 20).
        if (preg_match('/transform="translate\(([^,]+),\s*90\)"/', $svg, $matches)) {
            $barcodeX = floatval($matches[1]);
            $this->assertGreaterThanOrEqual(20, $barcodeX, "Barcode starting position should be centered with at least 20px margin.");
        } else {
            $this->fail("Barcode group transform translates incorrectly or is missing.");
        }
    }
}
