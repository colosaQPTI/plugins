<?php

/**
 * class.phpExcelLibraryProject.pmFunctions.php
 *
 * ProcessMaker Open Source Edition
 * Copyright (C) 2004 - 2008 Colosa Inc.
 * *
 */
////////////////////////////////////////////////////
// phpExcelLibraryProject PM Functions
//
// Copyright (C) 2007 COLOSA
//
// License: LGPL, see LICENSE
////////////////////////////////////////////////////

/** Include PHPExcel */
require_once ('Classes/PHPExcel.php');

function phpExcelLibraryProject_getMyCurrentDate() {
    return G::CurDate('Y-m-d');
}

function phpExcelLibraryProject_getMyCurrentTime() {
    return G::CurDate('H:i:s');
}
function exportXls($title = 'Sample', $data = array(), $subTitle = array(), $path = '/var/tmp/sample', $ext = 'xls', $phpOutput = 1) {
// Create new PHPExcel object
    $objPHPExcel = new PHPExcel();
// Set document properties
    $objPHPExcel->getProperties()->setCreator("convergence")
            ->setLastModifiedBy("Convergence")
            ->setTitle("Export_" . $title);

    $styleTitle = array(
        'font' => array(
            'bold' => true,
            'size' => 18
        ),
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        )
    );
    $styleHeader = array(
        'font' => array(
            'bold' => true
        ),
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        ),
        'allborders' => array(
            'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
            'color' => array(
                'rgb' => 'FFFFFF'
            )
        ),
        'fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array(
                'rgb' => 'FFBF00'
            )
        )
    );

    $styleDatas = array(
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        ),
        'allborders' => array(
            'style' => PHPExcel_Style_Border::BORDER_DASHED,
            'color' => array(
                'rgb' => 'FF0000'
            )
        )
    );


    $row = 1;
    $nbCol = 0;
    $objPHPExcel->setActiveSheetIndex(0);
    $worksheet = $objPHPExcel->getActiveSheet();

    if ($title != 'Sample' && $ext != 'csv')
    {
        if (count($data))
        {
            $nbCol = count($data[1]) - 1;
            $rowMax = count($data);
        }
        else if (count($subTitle))
        {
            $nbCol = count($subTitle[1]) - 1;
        }
        $coord = PHPExcel_Cell::stringFromColumnIndex($nbCol) . (1);
        $objPHPExcel->getActiveSheet()->mergeCells('A1:' . $coord);
        $coord = PHPExcel_Cell::stringFromColumnIndex(0) . ($row);
        $worksheet->setCellValue($coord, $title);
        $worksheet->getStyle($coord)->applyFromArray($styleTitle);
        $row = $row + 2;
    }
    if (count($subTitle))
    {
        $col = 0;
        $startHeader = PHPExcel_Cell::stringFromColumnIndex($col) . $row;
        foreach ($subTitle as $k => $value)
        {
            $coord = PHPExcel_Cell::stringFromColumnIndex($col) . ($row);
            $worksheet->setCellValue($coord, $value);
            $col++;
        }
        $rowHeader = $startHeader . ':' . PHPExcel_Cell::stringFromColumnIndex($col - 1) . ($row);
        $worksheet->getStyle($rowHeader)->applyFromArray($styleHeader);
        $row++;
    }
    if (count($data))
    {
        $nbCol = count($data[1]) - 1;
        $rowMax = count($data);
        $currentData = 1;
        $startDatas = PHPExcel_Cell::stringFromColumnIndex(0) . $row;
        foreach ($data as $k => $line)
        {
            $col = 0;

            foreach ($line as $field => $value)
            {
                $coord = PHPExcel_Cell::stringFromColumnIndex($col) . ($row);
                $worksheet->setCellValueExplicit($coord, $value, PHPExcel_Cell_DataType::TYPE_STRING);
                if ($currentData == $rowMax)
                {
                    $colName = PHPExcel_Cell::stringFromColumnIndex($col);
                    $worksheet->getColumnDimension($colName)->setAutoSize(true);
                }
                $col++;
            }
            $currentData++;
            $row++;
        }
        $rowDatas = $startDatas . ':' . PHPExcel_Cell::stringFromColumnIndex($col - 1) . ($row - 1);
        $worksheet->getStyle($rowDatas)->applyFromArray($styleDatas);
    }

// Rename worksheet
   $objPHPExcel->getActiveSheet()->setTitle($title);
// Set active sheet index to the first sheet, so Excel opens this as the first sheet
    $objPHPExcel->setActiveSheetIndex(0);
// Save Excel 2007 file
    //$infoArray = array();
    //$callStartTime = microtime(true);
    $ext = strtolower($ext);
    if ($phpOutput == 1)
    {
        switch ($ext)
        {
            case 'xls':
                header("Pragma: public");
                header("Expires: 0");
                header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                header("Content-Type: application/force-download");
                header("Content-Type: application/octet-stream");
                header("Content-Type: application/download");
                header("Content-Disposition: attachment; filename=" . $path . ".xls");
                header("Content-Transfer-Encoding: binary");
                $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
                //$objWriter->save($path . '.xls');
                break;

            case 'xlsx':
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="' . $path . '.xlsx"');
                header('Cache-Control: max-age=0');
                $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
                break;
            case 'csv':
                header("Pragma: public");
                header("Expires: 0");
                header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                header("Cache-Control: private", false);
                header('Content-Disposition: attachment; filename="' . $path . '.csv";');
                header("Content-Transfer-Encoding: binary");
                $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV')->setDelimiter(';')
                        ->setEnclosure('"')
                        ->setLineEnding("\r\n")
                        ->setSheetIndex(0);
                break;

            default:
                header("Pragma: public");
                header("Expires: 0");
                header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                header("Content-Type: application/force-download");
                header("Content-Type: application/octet-stream");
                header("Content-Type: application/download");
                header("Content-Disposition: attachment; filename=" . $path . ".xls");
                header("Content-Transfer-Encoding: binary");
                $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
                // $objWriter->save($path . '.xls');
                break;
        }
        $objWriter->save("php://output");
    }
    else
    {
        switch ($ext)
        {
            case 'xls':
                $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
                $objWriter->save($path . '.xls');
                break;

            case 'xlsx':
                $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
                $objWriter->save($path . '.xlsx');
                break;
            case 'csv':
                $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV')->setDelimiter(';')
                        ->setEnclosure('"')
                        ->setLineEnding("\r\n")
                        ->setSheetIndex(0);
                $objWriter->save($path . '.csv');
                break;

            default:
                $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
                $objWriter->save($path . '.xls');
                break;
        }
    }
    //die();
    if ($phpOutput == 1)
        exit;
}

function phpExcelLibraryProject_exportCompta($header = array( ), $datas = array( ), $footer = array( ), $path = '/var/tmp/sample', $ext = 'xls') {

    // INIT
// Create new PHPExcel object
    $objPHPExcel = new PHPExcel();
// Set document properties
    $objPHPExcel->getProperties()->setCreator("convergence")
            ->setLastModifiedBy("Convergence")
            ->setTitle($header['title']);

    $styleTitle = array(
        'font' => array(
            'bold' => true,
            'size' => 18
        ),
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        )
    );
    $styleHeader = array(
        'font' => array(
            'bold' => true
        ),
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        ),
        'allborders' => array(
            'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
            'color' => array(
                'rgb' => 'FFFFFF'
            )
        ),
        'fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array(
                'rgb' => 'FFBF00'
            )
        )
    );
    $styleDatas = array(
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        ),
        'allborders' => array(
            'style' => PHPExcel_Style_Border::BORDER_DASHED,
            'color' => array(
                'rgb' => 'FF0000'
            )
        )
    );
    $styleFooter = array(
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        ),
        'allborders' => array(
            'style' => PHPExcel_Style_Border::BORDER_DASHED,
            'color' => array(
                'rgb' => 'FF0000'
            )
        ),
        'fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array(
                'rgb' => '9E9E9E'
            )
        )
    );


    $row = 1;
    $nbCol = 0;
    $objPHPExcel->setActiveSheetIndex(0);
    $worksheet = $objPHPExcel->getActiveSheet();

    if ( $ext != 'csv' )
    {
        if ( !empty($datas) )
        {
            $nbCol = count($datas[0]) - 1;
            //$nbCol = count($datas[0]); // TODO : vérifier le -1, surement à cause de PM qui commence à 1
            $rowMax = count($datas);
        }
        else if ( !empty($header['colTitle']) )
        {
            $nbCol = count($header['colTitle']) - 1;
            //$nbCol = count($header['colTitle']); // TODE : idem
        }
        $coord = PHPExcel_Cell::stringFromColumnIndex($nbCol) . ($row);
        $objPHPExcel->getActiveSheet()->mergeCells('A1:' . $coord);
        $coord = PHPExcel_Cell::stringFromColumnIndex(0) . ($row);
        $worksheet->setCellValue($coord, $header['title']);
        $worksheet->getStyle($coord)->applyFromArray($styleTitle);
        $row++;
        if ( !empty($header['subTitle']) )
        {
            $coord = PHPExcel_Cell::stringFromColumnIndex(0) . ($row);
            $worksheet->setCellValue($coord, $header['subTitle']);
            $row++;
        }
    }
    if ( !empty($header['colTitle']) )
    {
        $col = 0;
        $startHeader = PHPExcel_Cell::stringFromColumnIndex($col) . $row;
        foreach ( $header['colTitle'] as $k => $value )
        {
            $coord = PHPExcel_Cell::stringFromColumnIndex($col) . ($row);
            $worksheet->setCellValue($coord, $value);
            $col++;
        }
        $rowHeader = $startHeader . ':' . PHPExcel_Cell::stringFromColumnIndex($col - 1) . ($row);
        $worksheet->getStyle($rowHeader)->applyFromArray($styleHeader);
        $row++;
    }
    if ( !empty($datas) )
    {
        $rowMax = count($datas);
        $currentData = 1;
        $startDatas = PHPExcel_Cell::stringFromColumnIndex(0) . $row;
        $startDatasRow = $row;
        foreach ( $datas as $line )
        {
            $col = 0;
            foreach ( $line as $field => $value )
            {
                $coord = PHPExcel_Cell::stringFromColumnIndex($col) . ($row);
                $worksheet->setCellValueExplicit($coord, $value, PHPExcel_Cell_DataType::TYPE_STRING);
                if ( $currentData == $rowMax )
                {
                    $colName = PHPExcel_Cell::stringFromColumnIndex($col);
                    $worksheet->getColumnDimension($colName)->setAutoSize(true);
                }
                $col++;
            }
            $currentData++;
            $row++;
        }
        $rowDatas = $startDatas . ':' . PHPExcel_Cell::stringFromColumnIndex($col - 1) . ($row - 1);
        $worksheet->getStyle($rowDatas)->applyFromArray($styleDatas);
    }
    if ( !empty($footer) )
    {        
        foreach ( $footer as $footerRow )
        {
            $row++;
            $nbColRight = 0;
            if ( !empty($footerRow['nbColRight']) )
            {
                $nbColRight = intval($footerRow['nbColRight']);
                unset($footerRow['nbColRight']);
            }
            $col = $nbCol - (count($footerRow) - 1) - $nbColRight;
            $startFooter = PHPExcel_Cell::stringFromColumnIndex($col) . $row;
            foreach ( $footerRow as $value )
            {
                $coord = PHPExcel_Cell::stringFromColumnIndex($col) . ($row);
                $worksheet->setCellValueExplicit($coord, $value, PHPExcel_Cell_DataType::TYPE_STRING);
                $colName = PHPExcel_Cell::stringFromColumnIndex($col);
                $worksheet->getColumnDimension($colName)->setAutoSize(true);
                $col++;
            }
            $rowFooter = $startFooter . ':' . PHPExcel_Cell::stringFromColumnIndex($col - 1) . ($row);
            $worksheet->getStyle($rowFooter)->applyFromArray($styleFooter);
        }                       
    }

// Rename worksheet
    $objPHPExcel->getActiveSheet()->setTitle('Transactions');
// Set active sheet index to the first sheet, so Excel opens this as the first sheet
    $objPHPExcel->setActiveSheetIndex(0);
    $ext = strtolower($ext);
    switch ( $ext )
    {
        case 'xls':
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save($path . '.xls');
            break;
        case 'xlsx':
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
            $objWriter->save($path . '.xlsx');
            break;
        case 'csv':
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV')->setDelimiter(';')
                    ->setEnclosure('"')
                    ->setLineEnding("\r\n")
                    ->setSheetIndex(0);
            $objWriter->save($path . '.csv');
            break;
        default:
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save($path . '.xls');
            break;
    }
    unset($objPHPExcel);
    unset($objWriter);
    return $path . '.' . $ext;
}