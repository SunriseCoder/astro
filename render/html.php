<?php
    class TableModel {
        public $title;
        public $header = [];
        public $data = [];
    }

    class HTMLRender {
        public static function renderTable($tableModel, $class = NULL) {
            $content = '<table'.(empty($class) ? '' : ' class="'.$class.'"').'>';

            if (isset($tableModel->title)) {
                $content .= '<caption>'.$tableModel->title.'</caption>';
            }

            if (isset($tableModel->header) && count($tableModel->header) > 0) {
                $content .= '<tr>';
                foreach ($tableModel->header as $header) {
                    $content .= '<th>'.$header.'</th>';
                }
                $content .= '</tr>';
            }

            if (isset($tableModel->data) && count($tableModel->data)) {
                foreach ($tableModel->data as $row) {
                    $content .= '<tr>';
                    foreach ($row as $cell) {
                        $content .= '<td>'.$cell.'</td>';
                    }
                    $content .= '</tr>';
                }
            }

            $content .= '</table>';

            return $content;
        }
    }
