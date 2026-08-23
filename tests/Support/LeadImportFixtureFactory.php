<?php

namespace Tests\Support;

use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class LeadImportFixtureFactory
{
    public static function webLeadsSpreadsheetMl(string $path): void
    {
        $xml = <<<'XML'
<?xml version="1.0"?>
<?mso-application progid="Excel.Sheet"?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">
 <Worksheet ss:Name="Web Leads">
  <Table>
   <Row>
    <Cell ss:Index="1"><Data ss:Type="String">form_name</Data></Cell>
    <Cell ss:Index="2"><Data ss:Type="String">what_is_your_estimated_project_budget?</Data></Cell>
    <Cell ss:Index="3"><Data ss:Type="String">what_type_of_website_are_you_looking_for?</Data></Cell>
    <Cell ss:Index="4"><Data ss:Type="String">email</Data></Cell>
    <Cell ss:Index="5"><Data ss:Type="String">full_name</Data></Cell>
    <Cell ss:Index="6"><Data ss:Type="String">phone</Data></Cell>
    <Cell ss:Index="7"><Data ss:Type="String">company_name</Data></Cell>
    <Cell ss:Index="8"><Data ss:Type="String">city</Data></Cell>
   </Row>
   <Row>
    <Cell ss:Index="1"><Data ss:Type="String">Contact</Data></Cell>
    <Cell ss:Index="2"><Data ss:Type="String">pkr_25,000_–_75,000</Data></Cell>
    <Cell ss:Index="3"><Data ss:Type="String">brochure site</Data></Cell>
    <Cell ss:Index="4"><Data ss:Type="String">Ada.Web@example.test</Data></Cell>
    <Cell ss:Index="5"><Data ss:Type="String">𝐀𝐝𝐚 Khan</Data></Cell>
    <Cell ss:Index="6"><Data ss:Type="String">+92 300 1112233</Data></Cell>
    <Cell ss:Index="7"><Data ss:Type="String">_</Data></Cell>
    <Cell ss:Index="8"><Data ss:Type="String">Lahore</Data></Cell>
   </Row>
   <Row>
    <Cell ss:Index="1"><Data ss:Type="String">Contact</Data></Cell>
    <Cell ss:Index="2"><Data ss:Type="String">i’m_not_sure_yet</Data></Cell>
    <Cell ss:Index="3"><Data ss:Type="String">custom portal</Data></Cell>
    <Cell ss:Index="4"><Data ss:Type="String">uk.lead@example.test</Data></Cell>
    <Cell ss:Index="5"><Data ss:Type="String">Jamie Cole</Data></Cell>
    <Cell ss:Index="6"><Data ss:Type="String">+44 7700 900123</Data></Cell>
    <Cell ss:Index="7"><Data ss:Type="String">Cole Ltd</Data></Cell>
    <Cell ss:Index="8"><Data ss:Type="String">London</Data></Cell>
   </Row>
   <Row>
    <Cell ss:Index="2"><Data ss:Type="String">pkr_75,000_–_200,000</Data></Cell>
    <Cell ss:Index="3"><Data ss:Type="String">no contact details</Data></Cell>
   </Row>
  </Table>
 </Worksheet>
</Workbook>
XML;
        file_put_contents($path, $xml);
    }

    public static function metaLeadsXlsx(string $path, bool $withAgent = true): void
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Meta Leads');
        $headers = [
            'Date', 'Time', 'Parent / Lead Name', 'Phone', 'Email', 'Student Year Group', 'Platform',
            'Source Ad', 'Ad Set', 'Campaign', 'Lead Status', 'Notes', 'Agent Name',
            'Follow Up', '2nd attempt', '3rd attempt', 'Follow up',
        ];
        $sheet->fromArray($headers, null, 'A1');

        $serial = ExcelDate::PHPToExcel(Carbon::parse('2026-08-18')->toDateTime());
        $sheet->setCellValue('A2', $serial);
        $sheet->setCellValue('B2', 0.5);
        $sheet->setCellValue('C2', 'محمد علي');
        $sheet->setCellValueExplicit('D2', 447700900999, DataType::TYPE_NUMERIC);
        $sheet->setCellValue('E2', 'meta.one@example.test');
        $sheet->setCellValue('F2', 'Year 7');
        $sheet->setCellValue('G2', 'fb');
        $sheet->setCellValue('H2', 'Spring Creative');
        $sheet->setCellValue('I2', 'UK Parents');
        $sheet->setCellValue('J2', 'Admissions 2026');
        $sheet->setCellValue('K2', 'New');
        $sheet->setCellValue('L2', 'Called once, interested.');
        $sheet->setCellValue('M2', $withAgent ? 'Foysol' : 'Unknown Agent');
        $sheet->setCellValue('N2', '2026-08-20');
        $sheet->setCellValue('O2', 'no answer');
        $sheet->setCellValue('P2', '');
        $sheet->setCellValue('Q2', 'try Friday');

        $sheet->setCellValue('A3', $serial);
        $sheet->setCellValue('C3', 'Ivy Green');
        $sheet->setCellValue('D3', '+1 202 555 0188');
        $sheet->setCellValue('E3', 'ivy.green@example.test');
        $sheet->setCellValue('F3', 'Year 9');
        $sheet->setCellValue('G3', 'ig');
        $sheet->setCellValue('H3', 'Story Ad');
        $sheet->setCellValue('I3', 'IG Warm');
        $sheet->setCellValue('J3', 'Admissions 2026');
        $sheet->setCellValue('K3', 'New');

        $sheet->setCellValue('A4', $serial);
        $sheet->setCellValue('C4', 'Ivy Duplicate');
        $sheet->setCellValue('E4', 'ivy.green@example.test');
        $sheet->setCellValue('G4', 'fb');
        $sheet->setCellValue('J4', 'Admissions 2026');

        $writer = new Xlsx($spreadsheet);
        $writer->save($path);
        $spreadsheet->disconnectWorksheets();
    }

    public static function genericCsv(string $path): void
    {
        $csv = "Name,Mobile Number,Contact Email,Ad Campaign,Business,Town\n".
            "Sam Rivera,+92 321 5556677,sam.rivera@example.test,Brand Burst,Rivera Co,Islamabad\n".
            "No Contact,,,,Only Biz,Townsville\n";
        file_put_contents($path, "\xEF\xBB\xBF".$csv);
    }

    public static function duplicatePhoneXlsx(string $path): void
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray(['full_name', 'phone', 'email'], null, 'A1');
        $sheet->fromArray(['One', '+923001111111', 'one-phone@example.test'], null, 'A2');
        $sheet->fromArray(['Two', '+92 300 1111111', 'two-phone@example.test'], null, 'A3');
        (new Xlsx($spreadsheet))->save($path);
        $spreadsheet->disconnectWorksheets();
    }
}
