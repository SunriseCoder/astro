<?php
    class TableModel {
        public $title;
        public $header = [];
        public $data = [];
    }

    class HTMLRender {
        public static function renderTable($tableModel, $class = NULL) {
            $rowCount = count($tableModel->header) + count($tableModel->data);
            $rowIndex = 0;
            $content = '<table'.(empty($class) ? '' : ' class="'.$class.'"').'>';

            // Rendering Table Caption
            if (isset($tableModel->title)) {
                $content .= '<caption>'.$tableModel->title.'</caption>';
            }

            // Rendering Table Header
            if (isset($tableModel->header) && is_array($tableModel->header) && count($tableModel->header) > 0) {
                // Header can consist of multiple rows
                foreach ($tableModel->header as $headerRow) {
                    $content .= '<tr>';
                    $columnIndex = 0;
                    $columnCount = count($headerRow);
                    foreach ($headerRow as $headerCell) {
                        $content .= self::renderCell('th', $headerCell, $rowIndex, $rowCount, $columnIndex, $columnCount);
                        $columnIndex++;
                    }
                    $content .= '</tr>';
                    $rowIndex++;
                }
            }

            // Rendering Table Content
            if (isset($tableModel->data) && count($tableModel->data)) {
                foreach ($tableModel->data as $dataRow) {
                    $content .= '<tr>';
                    $columnIndex = 0;
                    $columnCount = count($dataRow);
                    foreach ($dataRow as $dataCell) {
                        $content .= self::renderCell('td', $dataCell, $rowIndex, $rowCount, $columnIndex, $columnCount);
                        $columnIndex++;
                    }
                    $content .= '</tr>';
                    $rowIndex++;
                }
            }

            $content .= '</table>';

            return $content;
        }

        private static function renderCell($tag, $cellData, $rowIndex, $rowCount, $columnIndex, $columnCount) {
            // Cell could be an associative array with attributes for the Tag TH and the 'value' or just a string value
            $content = '';

            // Cell Attributes
            $thAttributes = '';

            // Align
            if (is_array($cellData) && isset($cellData['align'])) {
                $thAttributes .= (empty($thAttributes) ? '' : ' ').'align="'.$cellData['align'].'"';
            }

            // Attribute Colspan
            if (is_array($cellData) && isset($cellData['colspan'])) {
                $thAttributes .= (empty($thAttributes) ? '' : ' ').'colspan="'.$cellData['colspan'].'"';
            }

            // Attribute Rowspan
            if (is_array($cellData) && isset($cellData['rowspan'])) {
                $thAttributes .= (empty($thAttributes) ? '' : ' ').'rowspan="'.$cellData['rowspan'].'"';
            }

            // Attribute Tooltip
            if (is_array($cellData) && isset($cellData['tooltip'])) {
                $thAttributes .= (empty($thAttributes) ? '' : ' ').'title="'.$cellData['tooltip'].'"';
            }

            // Attribute Class
            $cellClassName = 'table-';
            // Vertical position
            if ($rowCount == 1) {
                $cellClassName .= 'single-';
            } else if ($rowIndex == 0) {
                $cellClassName .= 'top-';
            } else if ($rowIndex < $rowCount - 1) {
                $cellClassName .= 'middle-';
            } else {
                $cellClassName .= 'bottom-';
            }

            // Horizontal position
            if ($columnCount == 1) {
                $cellClassName .= 'single';
            } else if ($columnIndex == 0) {
                $cellClassName .= 'first';
            } else if ($columnIndex < $columnCount - 1) {
                $cellClassName .= 'middle';
            } else {
                $cellClassName .= 'last';
            }
            $thAttributes .= (empty($thAttributes) ? '' : ' ').'class="'.$cellClassName.'"';

            // Cell Value
            $value = is_array($cellData) ? (empty($cellData['value']) ? '' : $cellData['value']) : $cellData;
            $content .= '<'.$tag.' '.$thAttributes.'>'.$value.'</'.$tag.'>';
            return $content;
        }
    }
?>
