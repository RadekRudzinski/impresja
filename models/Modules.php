<?php

namespace impresja\impresja\models;

use impresja\impresja\Application;

class Modules
{
    public static function get()
    {
        $query = "SELECT imp_modules.*, imp_modules_sections.title as sectionTitle, imp_modules_sections.icon as sectionIcon, imp_modules_sections.`order` as sectionOrder FROM imp_modules
        LEFT JOIN imp_modules_sections ON imp_modules_sections.id = imp_modules.id_section
        ORDER BY imp_modules_sections.`order`, imp_modules.order";

        $statment = Application::$app->db->fetchQuery($query);
        foreach ($statment as $m) {
            $return[$m['sectionTitle']]['items'][] = $m;
            $return[$m['sectionTitle']]['icon'] = $m['sectionIcon'];
        }
        return $return;
    }
}
