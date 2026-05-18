<?php

namespace App\Services;

class StyledXlsxBuilder
{
    /**
     * @param  list<array{name: string, title: string, rows: list<list<string|int|float>>, widths: list<int|float>}>  $sheets
     */
    public function build(array $sheets): string
    {
        $esc = fn ($value) => htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $colName = function (int $index): string {
            $name = '';
            while ($index >= 0) {
                $name = chr($index % 26 + 65).$name;
                $index = intdiv($index, 26) - 1;
            }

            return $name;
        };
        $inlineCell = fn (int $col, int $row, string $value, int $style = 0) => '<c r="'.$colName($col).$row.'" t="inlineStr"'.($style ? ' s="'.$style.'"' : '').'><is><t xml:space="preserve">'.$esc($value).'</t></is></c>';

        $sheetXmls = [];
        foreach ($sheets as $sheetIndex => $sheet) {
            $rows = $sheet['rows'];
            $widths = $sheet['widths'];
            $lastCol = $colName(count($widths) - 1);

            $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
            $xml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';
            $xml .= '<sheetViews><sheetView workbookViewId="0"><pane ySplit="2" topLeftCell="A3" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>';
            $xml .= '<cols>';
            foreach ($widths as $i => $width) {
                $xml .= '<col min="'.($i + 1).'" max="'.($i + 1).'" width="'.$width.'" customWidth="1"/>';
            }
            $xml .= '</cols><sheetData>';
            $xml .= '<row r="1" ht="28" customHeight="1">'.$inlineCell(0, 1, $sheet['title'], 1).'</row>';
            $xml .= '<row r="2">';
            foreach ($rows[0] as $col => $header) {
                $xml .= $inlineCell($col, 2, (string) $header, 2);
            }
            $xml .= '</row>';

            foreach (array_slice($rows, 1) as $rowIndex => $row) {
                $rowNum = $rowIndex + 3;
                $xml .= '<row r="'.$rowNum.'">';
                foreach (array_values($row) as $col => $value) {
                    $xml .= $inlineCell($col, $rowNum, (string) $value, 3);
                }
                $xml .= '</row>';
            }

            $xml .= '</sheetData><autoFilter ref="A2:'.$lastCol.max(2, count($rows) + 1).'"/>';
            $xml .= '<mergeCells count="1"><mergeCell ref="A1:'.$lastCol.'1"/></mergeCells>';
            $xml .= '</worksheet>';
            $sheetXmls[$sheetIndex + 1] = $xml;
        }

        $stylesXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $stylesXml .= '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';
        $stylesXml .= '<fonts count="3"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="16"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font><font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font></fonts>';
        $stylesXml .= '<fills count="5"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF0F4C81"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FF1F6E8C"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FFF8FAFC"/></patternFill></fill></fills>';
        $stylesXml .= '<borders count="2"><border/><border><left style="thin"><color rgb="FFE5E7EB"/></left><right style="thin"><color rgb="FFE5E7EB"/></right><top style="thin"><color rgb="FFE5E7EB"/></top><bottom style="thin"><color rgb="FFE5E7EB"/></bottom></border></borders>';
        $stylesXml .= '<cellStyleXfs count="1"><xf/></cellStyleXfs>';
        $stylesXml .= '<cellXfs count="4"><xf/><xf fontId="1" fillId="2" borderId="0" applyFont="1" applyFill="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf><xf fontId="2" fillId="3" borderId="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf><xf fontId="0" fillId="4" borderId="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment vertical="top" wrapText="1"/></xf></cellXfs>';
        $stylesXml .= '</styleSheet>';

        $workbookXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets>';
        $workbookRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';
        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>';
        foreach ($sheets as $i => $sheet) {
            $id = $i + 1;
            $workbookXml .= '<sheet name="'.$esc($sheet['name']).'" sheetId="'.$id.'" r:id="rId'.$id.'"/>';
            $workbookRels .= '<Relationship Id="rId'.$id.'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet'.$id.'.xml"/>';
            $contentTypes .= '<Override PartName="/xl/worksheets/sheet'.$id.'.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
        }
        $stylesRelId = count($sheets) + 1;
        $workbookXml .= '</sheets></workbook>';
        $workbookRels .= '<Relationship Id="rId'.$stylesRelId.'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>';
        $contentTypes .= '</Types>';
        $rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>';

        $tmpFile = tempnam(sys_get_temp_dir(), 'xlsx');
        $zip = new \ZipArchive();
        $zip->open($tmpFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', $contentTypes);
        $zip->addFromString('_rels/.rels', $rels);
        $zip->addFromString('xl/_rels/workbook.xml.rels', $workbookRels);
        $zip->addFromString('xl/workbook.xml', $workbookXml);
        $zip->addFromString('xl/styles.xml', $stylesXml);
        foreach ($sheetXmls as $id => $xml) {
            $zip->addFromString('xl/worksheets/sheet'.$id.'.xml', $xml);
        }
        $zip->close();

        $content = file_get_contents($tmpFile);
        unlink($tmpFile);

        return $content;
    }
}
