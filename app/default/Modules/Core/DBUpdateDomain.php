<?php

namespace Pina\Modules\Core;

use Pina\DB;
use Pina\Core;

class DBUpdateDomain
{
    public function __construct()
    {
        $this->init();

        $this->db = DB::get();
    }

    private function init()
    {
        $this->tableGatewayList = $this->findGatewayList();
    }
    
    private function findGateways($module, $dir)
    {
        if (!file_exists($dir) || !is_dir($dir)) {
            return false;
        }
        
        $gateways = array();
            
        $dh  = opendir($dir);
        while (false !== ($filename = readdir($dh))) {
            $info = pathinfo($filename);
            if ($filename == '.' ||
                $filename == '..' ||
                !isset($info["extension"]) ||
                $info["extension"] != "php" ||
                strpos($filename, "Gateway") === false
            ) {
                continue;
            }
            
            $name = $module."::".str_replace(array('.php', 'Gateway'), '', $filename);
            $gw = Core::table($name);
            $gateways[$gw->table] = $name;
        }
        
        return $gateways;
    }

    private function findGatewayList()
    {
        $gateways = array();
        
        $pinaDir = __DIR__.'/../';
        $dh  = opendir($pinaDir);
        while (false !== ($filename = readdir($dh))) {
            if (($filename == '.') || ($filename == '..')) {
                continue;
            }
            
            $dir = $pinaDir . $filename;
            
            $findGateways = $this->findGateways($filename, $dir);
            if (is_array($findGateways)) {
                $gateways = array_merge($gateways, $findGateways);
            }
        }
        return $gateways;
    }

    public function findAddTables()
    {
        $list = array();
        foreach ($this->tableGatewayList as $v) {
            $gw = Core::table($v);
            
            if (!$this->db->query("SELECT * FROM ".$gw->table." LIMIT 1", true)) {
                $list[] = $gw->table;
            }
        }
        
        return $list;
    }

    public function findEditTables()
    {
        $tables = array();
        foreach ($this->tableGatewayList as $v) {
            $gw = Core::table($v);
            if (!$this->db->query("SELECT * FROM ".$gw->table." LIMIT 1", true)) {
                continue;
            }
            
            $tableUpdateDomain = Core::domain("Core::TableUpdate");
            $tableUpdateDomain->init($gw);
            $diff = $tableUpdateDomain->diff();
            if (is_array($diff) && count($diff)) {
                $tables[$gw->table] = $diff;
            }
        }

        return $tables;
    }

    public function update()
    {
        $addTables = $this->findAddTables();
        if (is_array($addTables) && count($addTables) > 0) {
            foreach ($addTables as $table) {
                $tableUpdateDomain = Core::domain("Core::TableUpdate");
                $tableUpdateDomain->init(
                    Core::table($this->tableGatewayList[$table])
                );
                $tableUpdateDomain->createTable();
            }
        }

        $editTables = $this->findEditTables();
        foreach ($editTables as $table => $value) {
            $tableUpdateDomain = Core::domain("Core::TableUpdate");
            $tableUpdateDomain->init(
                Core::table($this->tableGatewayList[$table])
            );

            $tableUpdateDomain->editTable();
        }
    }
}
