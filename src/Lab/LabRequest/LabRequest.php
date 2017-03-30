<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Lab\LabRequest;

use EmmetBlue\Core\Builder\BuilderFactory as Builder;
use EmmetBlue\Core\Factory\DatabaseConnectionFactory as DBConnectionFactory;
use EmmetBlue\Core\Factory\DatabaseQueryFactory as DBQueryFactory;
use EmmetBlue\Core\Builder\QueryBuilder\QueryBuilder as QB;
use EmmetBlue\Core\Exception\SQLException;
use EmmetBlue\Core\Session\Session;
use EmmetBlue\Core\Logger\DatabaseLog;
use EmmetBlue\Core\Logger\ErrorLog;
use EmmetBlue\Core\Constant;

use EmmetBlue\Plugins\Permission\Permission as Permission;

/**
 * class LabRequest.
 *
 * LabRequest Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 01/01/2016 04:21pm
 */
class LabRequest
{
    /**
     * creates new lab resources
     *
     * @param array $data
     */
    public static function create(array $data)
    {
        $patientID = $data['patientID'] ?? 'null';
        $clinicalDiagnosis = $data['clinicalDiagnosis'] ?? null;
        $investigationRequired = $data['investigationRequired'] ?? null;
        $requestedBy = $data['requestedBy'] ?? null;
        $investigationType = $data['investigationType'] ?? 'null';
        $requestNote = $data['requestNote'] ?? null;

        echo base64_decode($clinicalDiagnosis);
        die();

        try
        {
            $result = DBQueryFactory::insert('Lab.LabRequests', [
                'PatientID'=>$patientID,
                'ClinicalDiagnosis'=>QB::wrapString((string)$clinicalDiagnosis, "'"),
                // 'InvestigationRequired'=>QB::wrapString((string)$investigationRequired, "'"),
                'RequestedBy'=>QB::wrapString((string)$requestedBy, "'"),
                'InvestigationType'=>$investigationType,
                'RequestNote'=>QB::wrapString((string)$requestNote, "'")
            ]);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Lab',
                'LabRequests',
                (string)(serialize($result))
            );
            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process request (LabRequest not created), %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }

    /**
     * view Wards data
     */
    public static function view(int $resourceId)
    {
        $selectBuilder = "SELECT f.PatientFullName, f.PatientUUID, g.PatientTypeName, g.CategoryName, e.* FROM Patients.Patient f INNER JOIN (SELECT * FROM Lab.LabRequests a INNER JOIN (SELECT * FROM Lab.InvestigationTypes b INNER JOIN Lab.Labs c ON b.InvestigationTypeLab = c.LabID) d ON a.InvestigationType = d.InvestigationTypeID) e ON f.PatientID = e.PatientID INNER JOIN Patients.PatientType g ON f.PatientType = g.PatientTypeID WHERE e.LabID = $resourceId AND e.RequestAcknowledged = 0";

        try
        {
            $viewOperation = (DBConnectionFactory::getConnection()->query((string)$selectBuilder))->fetchAll(\PDO::FETCH_ASSOC);
            
            foreach ($viewOperation as $key=>$result){
                $viewOperation[$key]["RequestedByFullName"] = \EmmetBlue\Plugins\HumanResources\StaffProfile\StaffProfile::viewStaffFullName((int) $result["RequestedBy"])["StaffFullName"];
            }

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Lab',
                'LabRequests',
                (string)$selectBuilder
            );

            return $viewOperation;        
        } 
        catch (\PDOException $e) 
        {
            throw new SQLException(
                sprintf(
                    "Error procesing request"
                ),
                Constant::UNDEFINED
            );
            
        }
    }

    public static function viewByPatient(int $resourceId, array $data)
    {
        $resourceId = QB::wrapString($data["patient"], "'");
        $selectBuilder = "SELECT f.PatientFullName, f.PatientUUID, e.* FROM Patients.Patient f INNER JOIN (SELECT * FROM Lab.LabRequests a INNER JOIN (SELECT * FROM Lab.InvestigationTypes b INNER JOIN Lab.Labs c ON b.InvestigationTypeLab = c.LabID) d ON a.InvestigationType = d.InvestigationTypeID) e ON f.PatientID = e.PatientID WHERE f.PatientUUID = $resourceId AND e.RequestAcknowledged = 0";
        try
        {
            $viewOperation = (DBConnectionFactory::getConnection()->query((string)$selectBuilder))->fetchAll(\PDO::FETCH_ASSOC);
            
            foreach ($viewOperation as $key=>$result){
                $viewOperation[$key]["RequestedByFullName"] = \EmmetBlue\Plugins\HumanResources\StaffProfile\StaffProfile::viewStaffFullName((int) $result["RequestedBy"])["StaffFullName"];
            }

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Lab',
                'LabRequests',
                (string)$selectBuilder
            );

            return $viewOperation;        
        } 
        catch (\PDOException $e) 
        {
            throw new SQLException(
                sprintf(
                    "Error procesing request"
                ),
                Constant::UNDEFINED
            );
            
        }
    }

    /**
     * Modifies a Ward resource
     */
    public static function edit(int $resourceId, array $data)
    {
        // $updateBuilder = (new Builder("QueryBuilder", "Update"))->getBuilder();

        // try
        // {
        //     if (isset($data['FullName'])){
        //         $data['FullName'] = QB::wrapString($data['FullName'], "'");
        //     }
        //     $updateBuilder->table("Lab.LabRequests");
        //     $updateBuilder->set($data);
        //     $updateBuilder->where("LabRequestLabNumber = $resourceId");

        //     $result = (
        //             DBConnectionFactory::getConnection()
        //             ->query((string)$updateBuilder)
        //         );

        //     return $result;
        // }
        // catch (\PDOException $e)
        // {
        //     throw new SQLException(sprintf(
        //         "Unable to process update, %s",
        //         $e->getMessage()
        //     ), Constant::UNDEFINED);
        // }
    }

    /**
     * delete
     */
    public static function delete(int $resourceId)
    {
        $deleteBuilder = (new Builder("QueryBuilder", "Delete"))->getBuilder();

        try
        {
            $deleteBuilder
                ->from("Lab.LabRequests")
                ->where("RequestID = $resourceId");
            
            $result = (
                    DBConnectionFactory::getConnection()
                    ->exec((string)$deleteBuilder)
                );

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Lab',
                'LabRequests',
                (string)$deleteBuilder
            );

            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process delete request, %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }

    public static function closeRequest(array $data){
        $id = $data["request"] ?? null;
        $staff = $data["staff"] ?? null;
        $query = "UPDATE Lab.LabRequests SET RequestAcknowledged = 1, RequestAcknowledgedBy = $staff WHERE RequestID = $id";

        // die($query);

        $result = DBConnectionFactory::getConnection()->exec($query);
        return $result;
    }
}