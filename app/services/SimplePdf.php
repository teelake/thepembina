<?php

namespace App\Services;

class SimplePdf
{
    private $elements = [];
    private $cursorY;
    private $pageWidth = 612;  // 8.5in
    private $pageHeight = 792; // 11in
    private $images = [];

    public function __construct()
    {
        $this->cursorY = $this->pageHeight - 40;
    }

    public function addLine(string $text, int $fontSize = 12, int $x = 40): void
    {
        $this->elements[] = [
            'type' => 'text',
            'text' => $text,
            'font' => $fontSize,
            'x' => $x,
            'y' => $this->cursorY
        ];
        $this->cursorY -= $fontSize + 6;
    }

    public function addSpacing(int $amount = 10): void
    {
        $this->cursorY -= $amount;
    }

    /**
     * Draw a horizontal divider line.
     */
    public function addHorizontalRule(int $x = 40, int $width = 520, float $thickness = 0.5): void
    {
        $this->elements[] = [
            'type' => 'rule',
            'x' => $x,
            'y' => $this->cursorY,
            'width' => $width,
            'thickness' => $thickness
        ];
        $this->cursorY -= 8;
    }

    /**
     * Add table style row with multiple columns aligned on the same Y position.
     *
     * @param array $columns   Text for each column
     * @param array $positions X coordinate for each column
     * @param int   $fontSize  Font size to use
     */
    public function addTableRow(array $columns, array $positions, int $fontSize = 11): void
    {
        $y = $this->cursorY;
        foreach ($columns as $index => $text) {
            $x = $positions[$index] ?? ($positions[0] ?? 40);
            $this->elements[] = [
                'type' => 'text',
                'text' => $text,
                'font' => $fontSize,
                'x' => $x,
                'y' => $y
            ];
        }
        $this->cursorY = $y - ($fontSize + 6);
    }

    public function setCursor(int $y): void
    {
        $this->cursorY = $y;
    }

    public function getCursor(): int
    {
        return $this->cursorY;
    }

    public function addImage(string $path, int $x = 40, int $y = null, int $displayWidth = 120): void
    {
        if (!file_exists($path) || !is_readable($path)) {
            return;
        }

        $info = @getimagesize($path);
        if (!$info) {
            return;
        }
        [$widthPx, $heightPx, $type] = $info;
        $data = @file_get_contents($path);
        if ($data === false) {
            return;
        }

        $jpegData = $this->convertToJpeg($data, $type);
        if (!$jpegData) {
            return;
        }

        $ratio = $heightPx / $widthPx;
        $displayHeight = $displayWidth * $ratio;
        $imageName = 'Im' . (count($this->images) + 1);

        $this->images[] = [
            'name' => $imageName,
            'data' => $jpegData,
            'width_px' => $widthPx,
            'height_px' => $heightPx
        ];

        $this->elements[] = [
            'type' => 'image',
            'name' => $imageName,
            'x' => $x,
            'y' => $y === null ? $this->cursorY : $y,
            'width' => $displayWidth,
            'height' => $displayHeight
        ];

        if ($y === null) {
            $this->cursorY -= $displayHeight + 10;
        }
    }

    private function convertToJpeg(string $data, int $type): ?string
    {
        if (!function_exists('imagecreatefromstring')) {
            return null;
        }

        $image = @imagecreatefromstring($data);
        if (!$image) {
            return null;
        }

        ob_start();
        imagejpeg($image, null, 90);
        $jpegData = ob_get_clean();
        imagedestroy($image);

        return $jpegData;
    }

    private function escape(string $text): string
    {
        return str_replace(
            ['\\', '(', ')'],
            ['\\\\', '\\(', '\\)'],
            $text
        );
    }

    private function buildContentStream(): string
    {
        $stream = '';
        foreach ($this->elements as $element) {
            if ($element['type'] === 'text') {
                $stream .= sprintf(
                    "BT /F1 %d Tf 1 0 0 1 %d %d Tm (%s) Tj ET\n",
                    $element['font'],
                    $element['x'],
                    $element['y'],
                    $this->escape($element['text'])
                );
            } elseif ($element['type'] === 'rule') {
                $stream .= sprintf(
                    "%.2F w %d %d m %d %d l S\n",
                    $element['thickness'],
                    $element['x'],
                    $element['y'],
                    $element['x'] + $element['width'],
                    $element['y']
                );
            } elseif ($element['type'] === 'image') {
                $drawY = $element['y'] - $element['height'];
                $stream .= sprintf(
                    "q %.2F 0 0 %.2F %.2F %.2F cm /%s Do Q\n",
                    $element['width'],
                    $element['height'],
                    $element['x'],
                    $drawY,
                    $element['name']
                );
            }
        }
        return $stream;
    }

    public function output(): string
    {
        $contentStream = $this->buildContentStream();
        $objects = [];

        $objects[] = '<< /Type /Catalog /Pages 2 0 R >>';
        $objects[] = '<< /Type /Pages /Count 1 /Kids [3 0 R] >>';

        $resource = '<< /Font << /F1 4 0 R >>';
        if (!empty($this->images)) {
            $resource .= ' /XObject << ';
            foreach ($this->images as $index => $image) {
                $resource .= '/' . $image['name'] . ' ' . (5 + $index) . ' 0 R ';
            }
            $resource .= '>>';
        }
        $resource .= ' >>';

        $contentObjectNumber = 5 + count($this->images);

        $objects[] = sprintf(
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 %d %d] /Resources %s /Contents %d 0 R >>',
            $this->pageWidth,
            $this->pageHeight,
            $resource,
            $contentObjectNumber
        );

        $objects[] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>';

        foreach ($this->images as $image) {
            $objects[] = sprintf(
                "<< /Type /XObject /Subtype /Image /Width %d /Height %d /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length %d >>\nstream\n%s\nendstream",
                $image['width_px'],
                $image['height_px'],
                strlen($image['data']),
                $image['data']
            );
        }

        $objects[] = '<< /Length ' . strlen($contentStream) . " >>\nstream\n" . $contentStream . "endstream";

        $pdf = "%PDF-1.4\n";
        $offsets = [];
        foreach ($objects as $index => $object) {
            $number = $index + 1;
            $offsets[$number] = strlen($pdf);
            $pdf .= $number . " 0 obj\n" . $object . "\nendobj\n";
        }

        $xrefPosition = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";
        foreach ($objects as $index => $object) {
            $number = $index + 1;
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$number]);
        }

        $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n" . $xrefPosition . "\n%%EOF";

        return $pdf;
    }
}


