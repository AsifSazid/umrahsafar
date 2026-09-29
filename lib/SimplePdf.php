<?php
/**
 * FILE PATH: /lib/SimplePdf.php
 *
 * A minimal, dependency-free PDF generator. No Composer, no external
 * libraries — writes raw PDF 1.4 syntax directly. Supports A4 pages,
 * text (Helvetica core font, several sizes/weights), straight lines,
 * and filled rectangles — everything needed for an invoice/itinerary
 * style document. Not a general HTML-to-PDF converter by design: it
 * gives full control over a fixed A4 layout without needing a
 * rendering engine.
 *
 * Usage:
 *   $pdf = new SimplePdf();
 *   $pdf->addPage();
 *   $pdf->setFont('B', 18);
 *   $pdf->text(20, 30, 'Hello');
 *   $pdf->line(20, 35, 190, 35);
 *   $pdf->rect(20, 40, 170, 10, [80, 188, 129]); // filled RGB rect
 *   $pdf->save('/path/to/out.pdf');
 *
 * Coordinate system: millimeters from the top-left of an A4 page
 * (210 x 297mm), matching how print-oriented designers usually think —
 * internally converted to PDF's bottom-left-origin point system.
 */
class SimplePdf
{
    private array $objects = [];
    private int $objCount = 0;
    private array $pageObjIds = [];
    private array $pageContent = [];
    private int $currentPage = -1;
    private string $fontFamily = 'Helvetica';
    private string $fontStyle = '';
    private float $fontSize = 11;
    private array $fillColor = [0, 0, 0];

    const PAGE_W_MM = 210.0;
    const PAGE_H_MM = 297.0;
    const MM_TO_PT = 2.834645669; // 1mm = 2.834645669pt

    private function mm($v): float { return $v * self::MM_TO_PT; }

    public function addPage(): void
    {
        $this->pageContent[] = '';
        $this->currentPage = count($this->pageContent) - 1;
    }

    public function setFont(string $style = '', float $size = 11): void
    {
        // style: '' regular, 'B' bold, 'I' italic, 'BI' bold-italic
        $this->fontStyle = strtoupper($style);
        $this->fontSize = $size;
    }

    public function setColor(int $r, int $g, int $b): void
    {
        $this->fillColor = [$r, $g, $b];
    }

    private function pdfFontName(): string
    {
        return match ($this->fontStyle) {
            'B' => 'F2', 'I' => 'F3', 'BI' => 'F4', default => 'F1',
        };
    }

    private function esc(string $s): string
    {
        // Latin-1 fallback for the core Helvetica font (no embedded unicode font here).
        if (function_exists('iconv')) {
            $converted = @iconv('UTF-8', 'CP1252//TRANSLIT//IGNORE', $s);
            if ($converted !== false) $s = $converted;
        }
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $s);
    }

    /** Write text; x/y in mm from top-left, y is the text baseline. */
    public function text(float $x, float $y, string $s): void
    {
        [$r, $g, $b] = $this->fillColor;
        $yPt = $this->mm(self::PAGE_H_MM - $y);
        $xPt = $this->mm($x);
        $colText = ($r === 0 && $g === 0 && $b === 0) ? '' : sprintf('%.3F %.3F %.3F rg ', $r/255, $g/255, $b/255);
        $this->pageContent[$this->currentPage] .= sprintf(
            "BT %s%.3F %.3F Td /%s %.2F Tf (%s) Tj ET\n",
            $colText, $xPt, $yPt, $this->pdfFontName(), $this->fontSize, $this->esc($s)
        );
    }

    /** Right-aligned text at x (mm), approximated using Helvetica average glyph widths. */
    public function textRight(float $xRight, float $y, string $s): void
    {
        $w = $this->approxTextWidth($s);
        $this->text($xRight - $w, $y, $s);
    }

    /** Centered text between x1 and x2 (mm). */
    public function textCenter(float $x1, float $x2, float $y, string $s): void
    {
        $w = $this->approxTextWidth($s);
        $cx = $x1 + (($x2 - $x1) - $w) / 2;
        $this->text(max($x1, $cx), $y, $s);
    }

    /** Rough Helvetica width estimate (mm) — good enough for right/center alignment on labels. */
    public function approxTextWidth(string $s): float
    {
        $avgCharPt = $this->fontSize * ($this->fontStyle === 'B' ? 0.56 : 0.5);
        $lenPt = mb_strlen($s) * $avgCharPt;
        return $lenPt / self::MM_TO_PT;
    }

    /** A straight line from (x1,y1) to (x2,y2), mm, with optional stroke width (mm) and RGB color. */
    public function line(float $x1, float $y1, float $x2, float $y2, float $widthMm = 0.2, array $rgb = [200,200,200]): void
    {
        [$r, $g, $b] = $rgb;
        $x1p = $this->mm($x1); $y1p = $this->mm(self::PAGE_H_MM - $y1);
        $x2p = $this->mm($x2); $y2p = $this->mm(self::PAGE_H_MM - $y2);
        $wPt = $this->mm($widthMm);
        $this->pageContent[$this->currentPage] .= sprintf(
            "%.3F %.3F %.3F RG %.3F w %.3F %.3F m %.3F %.3F l S\n",
            $r/255, $g/255, $b/255, $wPt, $x1p, $y1p, $x2p, $y2p
        );
    }

    /** A filled rectangle at (x,y) top-left, mm width/height, RGB fill color. */
    public function rect(float $x, float $y, float $w, float $h, array $rgb): void
    {
        [$r, $g, $b] = $rgb;
        $xp = $this->mm($x);
        $yp = $this->mm(self::PAGE_H_MM - $y - $h);
        $wp = $this->mm($w);
        $hp = $this->mm($h);
        $this->pageContent[$this->currentPage] .= sprintf(
            "%.3F %.3F %.3F rg %.3F %.3F %.3F %.3F re f\n",
            $r/255, $g/255, $b/255, $xp, $yp, $wp, $hp
        );
    }

    /** Wrap a long string into lines that roughly fit maxWidthMm, and draw them starting at (x,y) with lineHeightMm spacing. Returns the y after the last line. */
    public function multiText(float $x, float $y, float $maxWidthMm, string $s, float $lineHeightMm = 5.5): float
    {
        $words = preg_split('/\s+/', trim($s));
        $line = '';
        $curY = $y;
        foreach ($words as $word) {
            $test = $line === '' ? $word : $line . ' ' . $word;
            if ($this->approxTextWidth($test) > $maxWidthMm && $line !== '') {
                $this->text($x, $curY, $line);
                $curY += $lineHeightMm;
                $line = $word;
            } else {
                $line = $test;
            }
        }
        if ($line !== '') { $this->text($x, $curY, $line); $curY += $lineHeightMm; }
        return $curY;
    }

    /** Serialize and return the PDF as a raw binary string. */
    public function output(): string
    {
        $this->objects = [];
        $this->objCount = 0;

        // Catalog + Pages placeholders reserved as obj 1 and 2.
        $catalogId = $this->nextObj();
        $pagesId = $this->nextObj();

        // Standard 14 fonts: Helvetica, Helvetica-Bold, Helvetica-Oblique, Helvetica-BoldOblique
        $fontIds = [];
        foreach ([
            'F1' => 'Helvetica', 'F2' => 'Helvetica-Bold',
            'F3' => 'Helvetica-Oblique', 'F4' => 'Helvetica-BoldOblique',
        ] as $key => $base) {
            $id = $this->nextObj();
            $this->objects[$id] = "<< /Type /Font /Subtype /Type1 /BaseFont /{$base} /Encoding /WinAnsiEncoding >>";
            $fontIds[$key] = $id;
        }

        $kids = [];
        foreach ($this->pageContent as $content) {
            $contentId = $this->nextObj();
            $stream = $content;
            $this->objects[$contentId] = "<< /Length " . strlen($stream) . " >>\nstream\n{$stream}endstream";

            $pageId = $this->nextObj();
            $fontRefs = [];
            foreach ($fontIds as $key => $id) $fontRefs[] = "/{$key} {$id} 0 R";
            $wPt = $this->mm(self::PAGE_W_MM);
            $hPt = $this->mm(self::PAGE_H_MM);
            $this->objects[$pageId] = "<< /Type /Page /Parent {$pagesId} 0 R /MediaBox [0 0 {$wPt} {$hPt}] "
                . "/Resources << /Font << " . implode(' ', $fontRefs) . " >> >> /Contents {$contentId} 0 R >>";
            $kids[] = "{$pageId} 0 R";
        }

        $this->objects[$pagesId] = "<< /Type /Pages /Kids [" . implode(' ', $kids) . "] /Count " . count($kids) . " >>";
        $this->objects[$catalogId] = "<< /Type /Catalog /Pages {$pagesId} 0 R >>";

        // Serialize
        $out = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n"; // binary marker comment — signals a binary (not plain-text) file to readers
        $offsets = [0 => 0];
        ksort($this->objects);
        foreach ($this->objects as $id => $body) {
            $offsets[$id] = strlen($out);
            $out .= "{$id} 0 obj\n{$body}\nendobj\n";
        }
        $xrefStart = strlen($out);
        $count = $this->objCount + 1;
        $out .= "xref\n0 {$count}\n";
        $out .= "0000000000 65535 f \n";
        for ($i = 1; $i <= $this->objCount; $i++) {
            $off = $offsets[$i] ?? 0;
            $out .= sprintf("%010d 00000 n \n", $off);
        }
        $out .= "trailer\n<< /Size {$count} /Root {$catalogId} 0 R >>\nstartxref\n{$xrefStart}\n%%EOF";
        return $out;
    }

    public function save(string $path): bool
    {
        $dir = dirname($path);
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
        return file_put_contents($path, $this->output()) !== false;
    }

    private function nextObj(): int
    {
        $this->objCount++;
        return $this->objCount;
    }
}
