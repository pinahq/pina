<?php

namespace Pina\Modules\Language;

use Pina\TableDataGateway;

class StringGateway extends TableDataGateway
{

    var $table = 'cody_string';
    var $fields = array(
        'language_code' => "varchar(2) NOT NULL DEFAULT ''",
        'string_key' => "varchar(255) NOT NULL DEFAULT ''",
        'string_value' => "text NOT NULL",
        'module_key' => "varchar(32) NOT NULL DEFAULT ''",
    );
    var $indexes = array(
        'PRIMARY KEY' => array('language_code', 'string_key'),
        'KEY language_code' => array('language_code', 'module_key')
    );

    function removeByKeyAndLanguageCode($string_key, $language_code)
    {
        $string_key = $this->db->escape($string_key);
        $language_code = $this->db->escape($language_code);
        $this->db->query("DELETE FROM " . $this->table . " WHERE string_key = '" . $string_key . "' AND language_code = '" . $language_code . "'");
    }

    function editValuesByKeys($items, $language_code)
    {
        if (!is_array($items) || count($items) == 0)
            return;

        $sql = "REPLACE INTO " . $this->table . " (language_code, string_key, string_value) VALUES ";
        $f = false;
        foreach ($items as $k => $v) {
            if ($f)
                $sql .= ",";
            $sql .= "('" . $this->db->escape($language_code) . "', '" . $this->db->escape($k) . "', '" . $this->db->escape($v) . "')";
            $f = true;
        }

        $this->db->query($sql);
    }

    function reportExistsByKeyAndLanguageCode($string_key, $language_code)
    {
        return $this->db->one("SELECT count(*) FROM `" . $this->table . "` WHERE string_key = '" . $this->db->escape($string_key) . "' AND language_code = '" . $this->db->escape($language_code) . "' LIMIT 1");
    }

}