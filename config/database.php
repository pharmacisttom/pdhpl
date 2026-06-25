<?php
// /config/database.php

class Database {
    // --- APP DB (KPI Config & Snapshots) ---
    private $app_host = "192.168.111.240";
    private $app_db   = "kpi";
    private $app_user = "tomwebdbnavicat";
    private $app_pass = "@TOM\$NavicatDB10832";
    
    // --- HIS DB (Himpro) ---
    private $his_host = "192.168.111.251";
    private $his_db   = "hos";
    private $his_user = "web_ptom";
    private $his_pass = "@TOM\$HimproDataBase10832";
    
    public $app_conn;
    public $his_conn;

    // เชื่อมต่อฐานข้อมูล KPI (APP DB)
    public function getAppConnection() {
        $this->app_conn = null;
        try {
            $this->app_conn = new PDO("mysql:host=" . $this->app_host . ";dbname=" . $this->app_db . ";charset=utf8mb4", $this->app_user, $this->app_pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            $this->app_conn->exec("SET time_zone = '+07:00'");
        } catch(PDOException $exception) {
            echo json_encode(["status" => "error", "message" => "APP DB Connection error: " . $exception->getMessage()]);
            exit();
        }
        return $this->app_conn;
    }

    // เชื่อมต่อฐานข้อมูลระบบโรงพยาบาล (HIS DB / Himpro)
    public function getHisConnection() {
        $this->his_conn = null;
        try {
            $this->his_conn = new PDO("mysql:host=" . $this->his_host . ";dbname=" . $this->his_db . ";charset=tis620", $this->his_user, $this->his_pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            $this->his_conn->exec("SET NAMES tis620");
            $this->his_conn->exec("SET time_zone = '+07:00'");
        } catch(PDOException $exception) {
            echo json_encode(["status" => "error", "message" => "HIS DB Connection error: " . $exception->getMessage()]);
            exit();
        }
        return $this->his_conn;
    }
}
?>