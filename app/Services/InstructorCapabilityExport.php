<?php

namespace App\Services;

use Illuminate\Support\Collection;
use RuntimeException;
use ZipArchive;

class InstructorCapabilityExport
{
    /** @param Collection<int, \App\Models\InstructorCapability> $capabilities */
    public function build(Collection $capabilities): string
    {
        $path = tempnam(sys_get_temp_dir(), 'region7-export-');
        if ($path === false) {
            throw new RuntimeException('Unable to create the export file.');
        }

        $zip = new ZipArchive;
        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Unable to create the Excel workbook.');
        }

        $headers = ['Instructor', 'County', 'Agency', 'Instructor Email', 'Instructor Phone', 'Course Code', 'Course Title', 'FLEX Status', 'FLEX Expiration', 'Delivery Role', 'Delivery Mode', 'Travel', 'Availability', 'Times Course Delivered', 'Last Taught', 'Regional Priority', 'Review Status', 'Review Notes', 'Submitted By', 'Submitter Email', 'Submitted At'];
        $rows = [$headers];

        foreach ($capabilities as $capability) {
            $rows[] = [
                $capability->profile->instructor_name,
                $capability->profile->county,
                $capability->profile->agency,
                $capability->profile->instructor_email,
                $capability->profile->instructor_phone,
                $capability->course_code,
                $capability->course_title,
                $capability->flex_status,
                $capability->flex_expiration_date?->format('Y-m-d'),
                $capability->delivery_role,
                $capability->delivery_mode,
                $capability->willing_to_travel,
                $capability->availability,
                $capability->prior_deliveries,
                $capability->last_taught_at?->format('Y-m-d'),
                $capability->regional_priority,
                $capability->review_status,
                $capability->review_notes,
                $capability->profile->submitted_by_name,
                $capability->profile->submitted_by_email,
                $capability->profile->created_at?->format('Y-m-d H:i:s'),
            ];
        }

        $zip->addFromString('[Content_Types].xml', $this->contentTypes());
        $zip->addFromString('_rels/.rels', $this->relationships());
        $zip->addFromString('xl/workbook.xml', $this->workbook());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRelationships());
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->worksheet($rows));
        $zip->close();

        return $path;
    }

    private function worksheet(array $rows): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews><sheetData>';
        foreach ($rows as $rowIndex => $row) {
            $xml .= '<row r="'.($rowIndex + 1).'">';
            foreach (array_values($row) as $columnIndex => $value) {
                $reference = $this->columnName($columnIndex + 1).($rowIndex + 1);
                $safe = htmlspecialchars((string) ($value ?? ''), ENT_XML1 | ENT_QUOTES, 'UTF-8');
                $xml .= '<c r="'.$reference.'" t="inlineStr"><is><t xml:space="preserve">'.$safe.'</t></is></c>';
            }
            $xml .= '</row>';
        }

        $lastColumn = $this->columnName(count($rows[0] ?? []));

        return $xml.'</sheetData><autoFilter ref="A1:'.$lastColumn.count($rows).'"/></worksheet>';
    }

    private function columnName(int $number): string
    {
        $name = '';
        while ($number > 0) {
            $number--;
            $name = chr(65 + ($number % 26)).$name;
            $number = intdiv($number, 26);
        }

        return $name;
    }

    private function contentTypes(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/></Types>';
    }

    private function relationships(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>';
    }

    private function workbook(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Capability Matrix" sheetId="1" r:id="rId1"/></sheets></workbook>';
    }

    private function workbookRelationships(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/></Relationships>';
    }
}
