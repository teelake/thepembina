<?php

namespace App\Services;

class SimplePdf
{
    private $lines = [];
    private $cursorY;
    private $pageWidth = 612;  // 8.5in
    private $pageHeight = 792; // 11in

    public function __construct()
    {
        $this->cursorY = $this->pageHeight - 40;
    }

    public function addLine(string $text, int $fontSize = 12, int $x = 40): void
    {
        $this->lines[] = [
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
        foreach ($this->lines as $line) {
            $stream .= sprintf(
                "BT /F1 %d Tf 1 0 0 1 %d %d Tm (%s) Tj ET\n",
                $line['font'],
                $line['x'],
                $line['y'],
                $this->escape($line['text'])
            );
        }
        return $stream;
    }

    public function output(): string
    {
        $objects = [];
        $contentStream = $this->buildContentStream();
        $objects[] = '<< /Type /Catalog /Pages 2 0 R >>';
        $objects[] = '<< /Type /Pages /Count 1 /Kids [3 0 R] >>';
        $objects[] = sprintf(
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 %d %d] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>',
            $this->pageWidth,
            $this->pageHeight
        );
        $objects[] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>';
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


