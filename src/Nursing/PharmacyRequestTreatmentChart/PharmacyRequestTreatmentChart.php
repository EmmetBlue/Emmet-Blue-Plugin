<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Nursing\PharmacyRequestTreatmentChart;

use EmmetBlue\Core\Builder\BuilderFactory as Builder;
use EmmetBlue\Core\Factory\DatabaseConnectionFactory as DBConnectionFactory;
use EmmetBlue\Core\Factory\DatabaseQueryFactory as DBQueryFactory;
use EmmetBlue\Core\Builder\QueryBuilder\QueryBuilder as QB;
use EmmetBlue\Core\Exception\SQLException;
use EmmetBlue\Core\Session\Session;
use EmmetBlue\Core\Logger\DatabaseLog;
use EmmetBlue\Core\Logger\ErrorLog;
use EmmetBlue\Core\Constant;
/**
 * class PharmacyRequestTreatmentChart.
 *
 * PharmacyRequestTreatmentChart Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 19/08/2016 13:35
 */
class PharmacyRequestTreatmentChart
{
    /**
     * creates new PharmacyRequestTreatmentChart
     *
     * @param array $data
     */
    public static function create(array $data)
    {
        $patientId = $data['admissionId'] ?? 'NULL';
        $drug = $data['drug'] ?? null;
        $nurse = $data["nurse"] ?? 'NULL';
        $dose = $data["dose"] ?? null;
        $route = $data["route"] ?? null;
        $note = $data["note"] ?? null;
        $time = date("H:i:s");

        try
        {
            $result = DBQueryFactory::insert('Nursing.PharmacyRequestsTreatmentCharts', [
                'PatientID'=>$patientId,
                'Drug'=>QB::wrapString($drug, "'"),
                'Nurse'=>$nurse,
                'Dose'=>QB::wrapString($dose, "'"),
                'Route'=>QB::wrapString($route, "'"),
                'Note'=>QB::wrapString((string)$note, "'"),
                'Time'=>QB::wrapString($time, "'")
            ]);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Nursing',
                'PharmacyRequestsTreatmentChart',
                (string)serialize($result)
            );

            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process request (chart not saved), %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }

    /**
     * view allergies
     */
    public static function view(int $resourceId)
    {
        $selectBuilder = (new Builder('QueryBuilder','Select'))->getBuilder();
        $selectBuilder
            ->columns('*')
            ->from('Nursing.PharmacyRequestsTreatmentCharts')
            ->where('PatientID = '.$resourceId);

        try
        {
            $result = (DBConnectionFactory::getConnection()->query((string)$selectBuilder))->fetchAll(\PDO::FETCH_ASSOC);
            
            foreach ($result as $i=>$j){
                $result[$i]["NurseFullName"] = \EmmetBlue\Plugins\HumanResources\StaffProfile\StaffProfile::viewStaffFullName((int) $j["Nurse"])["StaffFullName"];
            }

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Nursing',
                'PharmacyRequestsTreatmentChart',
                (string)$selectBuilder
            );

            return $result;
        } 
        catch (\PDOException $e) 
        {
            throw new SQLException(
                sprintf(
                    "Error procesing request %s",
                    $e->getMessage()
                ),
                Constant::UNDEFINED
            );
            
        }
    }

    
    public static function editPharmacyRequestTreatmentChart(int $resourceId, array $data)
    {

    }    
}