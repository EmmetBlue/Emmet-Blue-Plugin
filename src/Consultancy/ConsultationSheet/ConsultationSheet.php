<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Bardeson Lucky <flashup4all@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Consultancy\ConsultationSheet;

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
 * class ConsultancySheet.
 *
 * ConsultancySheet Controller
 *
 * @author Bardeson Lucky <flashup4all@gmail.com>
 * @since v0.0.1 19/08/2016 13:35
 */
class ConsultationSheet
{
    /**
     * creats new ConsultancySheet
     *
     * @param array $data
     */
    public static function create(array $data)
    {
        
        $consultantId = $data['consultantId'] ?? null;
        $note = $data['note'];
        $meta = $data['meta'];

        try
        {
            $result = DBQueryFactory::insert('Consultancy.ConsultationSheet', [
                'ConsultantID'=>$consultantId,
                'Note'=>$note,
                'Meta'=>$meta
            ]);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Consultancy',
                'ConsultationSheet',
                (string)(serialize($result))
            );
            
            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process request (Consultation note not created), %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }
    /**
     * view consultation data
     */
    public static function view(int $resourceId)
    {
        $selectBuilder = (new Builder('QueryBuilder','Select'))->getBuilder();
        $selectBuilder
            ->columns('*')
            ->from('Consultancy.ConsultationSheet');
        if ($resourceId != 0){
            $selectBuilder->where('consultationSheetID ='.$resourceId);
        }
        try
        {
            $viewOperation = (DBConnectionFactory::getConnection()->query((string)$selectBuilder))->fetchAll(\PDO::FETCH_ASSOC);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Consultancy',
                'ConsultationSheet',
                (string)$selectBuilder
            );

            if(count($viewOperation) > 0)
            {
                return $viewOperation;
            }
            else
            {
                return null;
            }           
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
     * Modifies the content of a consultation note
     */
    public static function edit(int $resourceId, array $data)
    {
        $updateBuilder = (new Builder("QueryBuilder", "Update"))->getBuilder();

        try
        {
            $updateBuilder->table("Consultancy.ConsultationSheet");
            $updateBuilder->set($data);
            $updateBuilder->where("consultationSheetID = $resourceId");

            $result = (
                    DBConnectionFactory::getConnection()
                    ->query((string)$updateBuilder)
                );
            //logging
            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Consultancy',
                'ConsultationSheet',
                (string)(serialize($result))
            );

            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process update, %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }
    /**
     * delete consultancy sheet
     */
    public static function delete(int $resourceId)
    {
        $deleteBuilder = (new Builder("QueryBuilder", "Delete"))->getBuilder();

        try
        {
            $deleteBuilder
                ->from("Consultancy.ConsultationSheet")
                ->where("consultationSheetID = $resourceId");
            
            $result = (
                    DBConnectionFactory::getConnection()
                    ->exec((string)$deleteBuilder)
                );

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Consultancy',
                'ConsultationSheet',
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
    /**
     * Assigning consultation sheet operation to patient
     */
    public static function assignToPatient(array $data)
    {
        $patientId = $data['patientId'] ?? null;
        $link = $data['link'];
        $meta = $data['meta'];
        try
        {
            $result = DBQueryFactory::insert('Patients.PatientTransaction', [
                'PatientID'=>$consultantId,
                'Link'=>$note,
                'Meta'=>$meta
            ]);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Patients',
                'PatientTransaction',
                (string)(serialize($result))
            );
            
            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process request %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }
}