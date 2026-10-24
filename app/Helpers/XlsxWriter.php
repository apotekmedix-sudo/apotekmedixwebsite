<?php

namespace App\Helpers;

/**
 * XlsxWriter — Generate XLSX file murni (ZIP + XML) tanpa library eksternal.
 * Terbuka langsung rapi di Excel tanpa warning apapun.
 */
class XlsxWriter
{
    /**
     * Generate XLSX dan kembalikan sebagai HTTP response download.
     *
     * @param  string  $filename   Nama file tanpa path, misal "template_produk.xlsx"
     * @param  array   $headers    Array string kolom header baris pertama
     * @param  array   $rows       Array of array data baris
     * @param  array   $widths     Optional lebar kolom dalam karakter (default 20)
     * @return \Illuminate\Http\Response
     */
    public static function download(
        string $filename,
        array  $headers,
        array  $rows,
        array  $widths = [],
        ?array $numericColumns = null
    ) {
        $xlsx = self::build($headers, $rows, $widths, $numericColumns);

        return response($xlsx, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Content-Length'      => strlen($xlsx),
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ]);
    }

    /**
     * Build XLSX binary string.
     */
    public static function build(array $headers, array $rows, array $widths = [], ?array $numericColumns = null): string
    {
        // ── 1. Build sheet XML ────────────────────────────────────────────────
        $sharedStrings = [];
        $siIndex       = [];

        $getSi = function (string $val) use (&$sharedStrings, &$siIndex): int {
            if (!isset($siIndex[$val])) {
                $siIndex[$val]   = count($sharedStrings);
                $sharedStrings[] = $val;
            }
            return $siIndex[$val];
        };

        // Style indexes (see cellXfs below):
        // 0 = default
        // 1 = header  (bold white on blue, center, border)
        // 2 = number  (right-aligned, border, white bg)
        // 3 = text    (left-aligned, border, white bg)
        // 4 = number-alt (right-aligned, border, zebra bg)
        // 5 = text-alt   (left-aligned, border, zebra bg)
        // 6 = currency   (number format #,##0, white bg)
        // 7 = currency-alt (number format #,##0, zebra bg)

        $sheetRows = '';

        // Header row
        $sheetRows .= '<row r="1" ht="20" customHeight="1">';
        foreach ($headers as $ci => $h) {
            $col  = self::colLetter($ci);
            $cell = $col . '1';
            $si   = $getSi((string) $h);
            $sheetRows .= '<c r="' . $cell . '" t="s" s="1"><v>' . $si . '</v></c>';
        }
        $sheetRows .= '</row>';

        // Data rows
        foreach ($rows as $ri => $row) {
            $rowNum   = $ri + 2;
            $isOdd    = ($ri % 2 === 0); // ri=0 → first data row → white
            $isTotal  = (isset($row[0]) && $row[0] === '__TOTAL__');
            $isEmpty  = ($isTotal === false && implode('', array_map('strval', $row)) === '');

            $sheetRows .= '<row r="' . $rowNum . '" ht="' . ($isTotal ? 22 : 16) . '" customHeight="1">';

            foreach ($row as $ci => $val) {
                $col  = self::colLetter($ci);
                $cell = $col . $rowNum;
                $v    = ($ci === 0 && $isTotal) ? '' : (string) $val; // strip marker

                if ($isEmpty) {
                    // Empty separator row — just write blank text cells with no-border style
                    $si = $getSi('');
                    $sheetRows .= '<c r="' . $cell . '" t="s" s="0"><v>' . $si . '</v></c>';
                    continue;
                }

                $isNumeric = $numericColumns === null
                    ? ($v !== '' && is_numeric($v) && !preg_match('/^0\d/', $v))
                    : ($v !== '' && in_array($ci, $numericColumns, true) && is_numeric($v));

                if ($isTotal) {
                    // Total row styles: 8=total-text, 9=total-currency
                    if ($isNumeric && $ci >= 11) {
                        $style = 9; // total currency (bold green bg, #,##0)
                    } else {
                        $style = 8; // total text (bold green bg)
                    }

                    if ($isNumeric) {
                        $sheetRows .= '<c r="' . $cell . '" s="' . $style . '"><v>' . floatval($v) . '</v></c>';
                    } else {
                        $si = $getSi($v);
                        $sheetRows .= '<c r="' . $cell . '" t="s" s="' . $style . '"><v>' . $si . '</v></c>';
                    }
                } elseif ($isNumeric) {
                    $style = ($ci >= 11) ? ($isOdd ? 6 : 7) : ($isOdd ? 2 : 4);
                    $sheetRows .= '<c r="' . $cell . '" s="' . $style . '"><v>' . floatval($v) . '</v></c>';
                } else {
                    $si    = $getSi($v);
                    $style = $isOdd ? 3 : 5;
                    $sheetRows .= '<c r="' . $cell . '" t="s" s="' . $style . '"><v>' . $si . '</v></c>';
                }
            }
            $sheetRows .= '</row>';
        }

        // Col widths
        $colDefs = '';
        foreach ($headers as $ci => $h) {
            $w = $widths[$ci] ?? 20;
            $n = $ci + 1;
            $colDefs .= '<col min="' . $n . '" max="' . $n . '" width="' . $w . '" customWidth="1"/>';
        }

        $lastCol  = self::colLetter(count($headers) - 1);
        $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
            . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheetViews><sheetView workbookViewId="0" showGridLines="1"><selection activeCell="A2" sqref="A2"/></sheetView></sheetViews>'
            . '<sheetFormatPr defaultRowHeight="15"/>'
            . '<cols>' . $colDefs . '</cols>'
            . '<sheetData>' . $sheetRows . '</sheetData>'
            . '<autoFilter ref="A1:' . $lastCol . '1"/>'
            . '</worksheet>';

        // ── 2. Shared strings XML ─────────────────────────────────────────────
        $ssItems = '';
        foreach ($sharedStrings as $s) {
            $ssItems .= '<si><t xml:space="preserve">' . htmlspecialchars($s, ENT_XML1 | ENT_QUOTES) . '</t></si>';
        }
        $ssCount = count($sharedStrings);
        $ssXml   = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
            . ' count="' . $ssCount . '" uniqueCount="' . $ssCount . '">'
            . $ssItems . '</sst>';

        // ── 3. Styles XML ─────────────────────────────────────────────────────
        // numFmtId=3 is built-in Excel format: #,##0
        $stylesXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            // fonts: 0=normal, 1=bold-white (header), 2=bold-dark (total)
            . '<fonts count="3">'
            . '<font><sz val="10"/><name val="Calibri"/></font>'
            . '<font><sz val="10"/><b/><name val="Calibri"/><color rgb="FFFFFFFF"/></font>'
            . '<font><sz val="10"/><b/><name val="Calibri"/><color rgb="FF1B5E20"/></font>'
            . '</fonts>'
            // fills: 0=none, 1=gray125 (required by spec), 2=blue (header), 3=zebra light-blue, 4=green (total)
            . '<fills count="5">'
            . '<fill><patternFill patternType="none"/></fill>'
            . '<fill><patternFill patternType="gray125"/></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FF1565C0"/></patternFill></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FFF0F4FA"/></patternFill></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FFE8F5E9"/></patternFill></fill>'
            . '</fills>'
            // borders: 0=none, 1=thin-gray
            . '<borders count="2">'
            . '<border><left/><right/><top/><bottom/><diagonal/></border>'
            . '<border>'
            . '<left style="thin"><color rgb="FFCFD8E3"/></left>'
            . '<right style="thin"><color rgb="FFCFD8E3"/></right>'
            . '<top style="thin"><color rgb="FFCFD8E3"/></top>'
            . '<bottom style="thin"><color rgb="FFCFD8E3"/></bottom>'
            . '<diagonal/>'
            . '</border>'
            . '</borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            // cellXfs — 8 styles
            . '<cellXfs>'
            // 0: default
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            // 1: header — bold white on blue, center, border
            . '<xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
            // 2: number, white bg, right-aligned
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>'
            // 3: text, white bg
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1"><alignment vertical="center"/></xf>'
            // 4: number, zebra bg, right-aligned
            . '<xf numFmtId="0" fontId="0" fillId="3" borderId="1" xfId="0" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>'
            // 5: text, zebra bg
            . '<xf numFmtId="0" fontId="0" fillId="3" borderId="1" xfId="0" applyFill="1" applyBorder="1" applyAlignment="1"><alignment vertical="center"/></xf>'
            // 6: currency (#,##0), white bg
            . '<xf numFmtId="3" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>'
            // 7: currency (#,##0), zebra bg
            . '<xf numFmtId="3" fontId="0" fillId="3" borderId="1" xfId="0" applyNumberFormat="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>'
            // 8: total text — bold dark-green font, green bg, border
            . '<xf numFmtId="0" fontId="2" fillId="4" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment vertical="center"/></xf>'
            // 9: total currency — bold dark-green font, green bg, #,##0, right-aligned
            . '<xf numFmtId="3" fontId="2" fillId="4" borderId="1" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>'
            . '</cellXfs>'
            . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            . '</styleSheet>';

        // ── 4. Pack ke ZIP (XLSX = ZIP) ───────────────────────────────────────
        $files = [
            '[Content_Types].xml'        => self::contentTypes(),
            '_rels/.rels'                => self::rootRels(),
            'xl/workbook.xml'            => self::workbook(),
            'xl/_rels/workbook.xml.rels' => self::workbookRels(),
            'xl/worksheets/sheet1.xml'   => $sheetXml,
            'xl/sharedStrings.xml'       => $ssXml,
            'xl/styles.xml'              => $stylesXml,
        ];

        return self::buildZip($files);
    }

    public static function downloadWorkbook(string $filename, array $sheets)
    {
        $xlsx = self::buildWorkbook($sheets);

        return response($xlsx, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Content-Length' => strlen($xlsx),
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ]);
    }

    public static function buildWorkbook(array $sheets): string
    {
        if (!$sheets) {
            throw new \InvalidArgumentException('Workbook must contain at least one worksheet.');
        }

        $files = [
            '[Content_Types].xml' => '',
            '_rels/.rels' => self::rootRels(),
            'xl/workbook.xml' => '',
            'xl/_rels/workbook.xml.rels' => '',
            'xl/styles.xml' => self::workbookStyles(),
        ];
        $sheetDefinitions = [];
        $sheetRelationships = [];
        $contentTypeOverrides = [];
        $usedNames = [];

        foreach (array_values($sheets) as $index => $sheet) {
            $sheetNumber = $index + 1;
            $name = self::uniqueSheetName((string) ($sheet['name'] ?? 'Sheet ' . $sheetNumber), $usedNames);
            $sheetId = 'rId' . $sheetNumber;
            $sheetFile = 'xl/worksheets/sheet' . $sheetNumber . '.xml';
            $headers = array_values($sheet['headers'] ?? []);
            $rows = array_values($sheet['rows'] ?? []);

            if (!$headers) {
                throw new \InvalidArgumentException("Worksheet '{$name}' must have at least one column.");
            }

            $files[$sheetFile] = self::workbookSheet(
                $headers,
                $rows,
                $sheet['widths'] ?? [],
                $sheet['numeric_columns'] ?? []
            );
            $sheetDefinitions[] = '<sheet name="' . htmlspecialchars($name, ENT_XML1 | ENT_QUOTES) . '" sheetId="' . $sheetNumber . '" r:id="' . $sheetId . '"/>';
            $sheetRelationships[] = '<Relationship Id="' . $sheetId . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet' . $sheetNumber . '.xml"/>';
            $contentTypeOverrides[] = '<Override PartName="/xl/worksheets/sheet' . $sheetNumber . '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
        }

        $sheetCount = count($sheetDefinitions);
        $styleRelationshipId = 'rId' . ($sheetCount + 1);
        $sheetRelationships[] = '<Relationship Id="' . $styleRelationshipId . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>';

        $files['xl/workbook.xml'] = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets>' . implode('', $sheetDefinitions) . '</sheets></workbook>';
        $files['xl/_rels/workbook.xml.rels'] = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . implode('', $sheetRelationships) . '</Relationships>';
        $files['[Content_Types].xml'] = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . implode('', $contentTypeOverrides)
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '</Types>';

        return self::buildZip($files);
    }

    private static function workbookSheet(array $headers, array $rows, array $widths, array $numericColumns): string
    {
        $columnCount = count($headers);
        $rowCount = count($rows) + 1;
        $sheetRows = '<row r="1" ht="20" customHeight="1">';

        foreach ($headers as $column => $header) {
            $sheetRows .= self::inlineStringCell(self::colLetter($column) . '1', (string) $header, 1);
        }
        $sheetRows .= '</row>';

        foreach ($rows as $rowIndex => $row) {
            $rowNumber = $rowIndex + 2;
            $style = $rowIndex % 2 === 0 ? 3 : 4;
            $sheetRows .= '<row r="' . $rowNumber . '">';

            for ($column = 0; $column < $columnCount; $column++) {
                $value = $row[$column] ?? '';
                $cell = self::colLetter($column) . $rowNumber;
                $stringValue = is_scalar($value) || $value === null
                    ? (string) ($value ?? '')
                    : json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

                if ($stringValue === '') {
                    $sheetRows .= '<c r="' . $cell . '" s="' . $style . '"/>';
                } elseif ((in_array($column, $numericColumns, true) || is_int($value) || is_float($value)) && is_numeric($stringValue)) {
                    $sheetRows .= '<c r="' . $cell . '" s="2"><v>' . htmlspecialchars($stringValue, ENT_XML1 | ENT_QUOTES) . '</v></c>';
                } else {
                    $sheetRows .= self::inlineStringCell($cell, $stringValue, $style);
                }
            }
            $sheetRows .= '</row>';
        }

        $columnDefinitions = '';
        foreach ($headers as $column => $header) {
            $width = max(8, min(60, (float) ($widths[$column] ?? 20)));
            $columnNumber = $column + 1;
            $columnDefinitions .= '<col min="' . $columnNumber . '" max="' . $columnNumber . '" width="' . $width . '" customWidth="1"/>';
        }

        $lastColumn = self::colLetter($columnCount - 1);

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<sheetViews><sheetView workbookViewId="0" showGridLines="1"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>'
            . '<sheetFormatPr defaultRowHeight="15"/><cols>' . $columnDefinitions . '</cols>'
            . '<sheetData>' . $sheetRows . '</sheetData>'
            . '<autoFilter ref="A1:' . $lastColumn . $rowCount . '"/>'
            . '</worksheet>';
    }

    private static function inlineStringCell(string $reference, string $value, int $style): string
    {
        return '<c r="' . $reference . '" s="' . $style . '" t="inlineStr"><is><t xml:space="preserve">'
            . htmlspecialchars($value, ENT_XML1 | ENT_QUOTES)
            . '</t></is></c>';
    }

    private static function uniqueSheetName(string $name, array &$usedNames): string
    {
        $name = preg_replace('/[\\\\\/?*\[\]:]/', ' ', trim($name)) ?: 'Sheet';
        $name = mb_substr($name, 0, 31);
        $base = $name;
        $suffix = 1;

        while (in_array(mb_strtolower($name), $usedNames, true)) {
            $ending = ' ' . $suffix++;
            $name = mb_substr($base, 0, 31 - mb_strlen($ending)) . $ending;
        }

        $usedNames[] = mb_strtolower($name);

        return $name;
    }

    private static function workbookStyles(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="2"><font><sz val="10"/><name val="Calibri"/></font>'
            . '<font><sz val="10"/><b/><name val="Calibri"/><color rgb="FFFFFFFF"/></font></fonts>'
            . '<fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FF1565C0"/></patternFill></fill></fills>'
            . '<borders count="2"><border><left/><right/><top/><bottom/><diagonal/></border><border>'
            . '<left style="thin"><color rgb="FFCFD8E3"/></left><right style="thin"><color rgb="FFCFD8E3"/></right>'
            . '<top style="thin"><color rgb="FFCFD8E3"/></top><bottom style="thin"><color rgb="FFCFD8E3"/></bottom>'
            . '<diagonal/></border></borders><cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="5"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            . '<xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
            . '<xf numFmtId="3" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1"><alignment vertical="center"/></xf>'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1"><alignment vertical="center"/></xf>'
            . '</cellXfs><cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles></styleSheet>';
    }

    // ── Helper: column letter (0→A, 25→Z, 26→AA ...) ─────────────────────────
    private static function colLetter(int $n): string
    {
        $letter = '';
        $n++;
        while ($n > 0) {
            $n--;
            $letter = chr(65 + ($n % 26)) . $letter;
            $n      = (int) ($n / 26);
        }
        return $letter;
    }

    // ── Static XML strings ────────────────────────────────────────────────────
    private static function contentTypes(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml"  ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '</Types>';
    }

    private static function rootRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';
    }

    private static function workbook(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
            . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="Data" sheetId="1" r:id="rId1"/></sheets>'
            . '</workbook>';
    }

    private static function workbookRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>'
            . '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '</Relationships>';
    }

    // ── Minimal ZIP builder (tanpa ext-zip) ───────────────────────────────────
    private static function buildZip(array $files): string
    {
        $centralDir  = '';
        $localFiles  = '';
        $offset      = 0;
        $entries     = 0;

        foreach ($files as $name => $content) {
            $nameBytes  = $name;
            $nameLen    = strlen($nameBytes);
            $data       = $content;
            $dataLen    = strlen($data);
            $crc        = crc32($data);
            $dosTime    = self::dosTime();

            // Local file header
            $local  = "\x50\x4b\x03\x04"; // signature
            $local .= "\x14\x00";          // version needed
            $local .= "\x00\x00";          // flags
            $local .= "\x00\x00";          // compression (stored)
            $local .= $dosTime;
            $local .= pack('V', $crc);
            $local .= pack('V', $dataLen);
            $local .= pack('V', $dataLen);
            $local .= pack('v', $nameLen);
            $local .= "\x00\x00";          // extra len
            $local .= $nameBytes;
            $local .= $data;

            // Central dir entry
            $central  = "\x50\x4b\x01\x02"; // signature
            $central .= "\x14\x00";          // version made by
            $central .= "\x14\x00";          // version needed
            $central .= "\x00\x00";          // flags
            $central .= "\x00\x00";          // compression
            $central .= $dosTime;
            $central .= pack('V', $crc);
            $central .= pack('V', $dataLen);
            $central .= pack('V', $dataLen);
            $central .= pack('v', $nameLen);
            $central .= "\x00\x00";          // extra len
            $central .= "\x00\x00";          // comment len
            $central .= "\x00\x00";          // disk start
            $central .= "\x00\x00";          // internal attr
            $central .= "\x00\x00\x00\x00"; // external attr
            $central .= pack('V', $offset);
            $central .= $nameBytes;

            $localFiles .= $local;
            $centralDir .= $central;
            $offset += strlen($local);
            $entries++;
        }

        $cdLen    = strlen($centralDir);
        $cdOffset = $offset;

        $eocd  = "\x50\x4b\x05\x06"; // end of central dir signature
        $eocd .= "\x00\x00";          // disk number
        $eocd .= "\x00\x00";          // disk with central dir
        $eocd .= pack('v', $entries);
        $eocd .= pack('v', $entries);
        $eocd .= pack('V', $cdLen);
        $eocd .= pack('V', $cdOffset);
        $eocd .= "\x00\x00";          // comment length

        return $localFiles . $centralDir . $eocd;
    }

    private static function dosTime(): string
    {
        $t = getdate();
        $dosDate = (($t['year'] - 1980) << 9) | ($t['mon'] << 5) | $t['mday'];
        $dosTime = ($t['hours'] << 11) | ($t['minutes'] << 5) | (int)($t['seconds'] / 2);
        return pack('v', $dosTime) . pack('v', $dosDate);
    }
}
