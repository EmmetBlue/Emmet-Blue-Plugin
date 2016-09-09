<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Bardeson Lucky <flashup4all@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Patients\Patient;

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
 * class Patient.
 *
 * Patient Controller
 *
 * @author Samuel Adeshina <Samueladeshina73@gmail.com>
 * @since v0.0.1 26/08/2016 12:33
 */
class Patient
{
    CONST PATIENT_ARCHIVE_DIR = "bin\\data\\records\\archives\\patient\\";

    protected static $patientFolders = [];
    /**
     * creats new patient id and generates a unique user id (UUID)
     *
     * @param array $data
     */
    protected static function createPatientFolders(string $patientUuid){
        /**
         * Create 'profile' and 'repositories' folders inside a folder named
         * '$patientUuid' which will also be created inside the PATIENT_ARCHIVE_DIR
         * directory.
         */
        $patientDir = self::PATIENT_ARCHIVE_DIR.$patientUuid;
        $profileDir = $patientDir.DIRECTORY_SEPARATOR.'profile';
        $repoDir = $patientDir.DIRECTORY_SEPARATOR.'repositories';
        if (!mkdir($patientDir)){
            return false;
        }
        if (!mkdir($profileDir) || !mkdir($repoDir)){
            unlink($patientDir);
            return false;
        }

        self::$patientFolders = [
            "patient" => $patientDir,
            "profile" => $profileDir,
            "repo" => $repoDir
        ];

        return true;
    }

    protected static function uploadPhotoAndDocuments($passport, $documents){
        if (!isset(self::$patientFolders["profile"]) || is_null(self::$patientFolders["profile"])){
            return false;
        }

        $handler = fopen(self::$patientFolders["profile"].DIRECTORY_SEPARATOR."photo.img", "w");
        fwrite($handler, (!is_null($passport)) ? $passport : "");
        fclose($handler);
        $handler = fopen(self::$patientFolders["profile"].DIRECTORY_SEPARATOR."documents.img", "w");
        fwrite($handler, (!is_null($documents)) ? $documents : "");
        fclose($handler);

        return true;
    }

    public static function create(array $data)
    {
        if (isset($data["patientName"])){
            $fullName = $data["patientName"];
            $type = $data["patientType"] ?? null;
            $passport = $data["patientPassport"] ?? null;
            $documents = $data["documents"] ?? null;

            $hospitalHistory = $data["hospitalHistory"] ?? [];
            $diagnosis = $data["diagnosis"] ?? [];
            $operation = $data["operation"] ?? [];
            
            $patientUuid = substr(str_shuffle(MD5(microtime())), 0, 20);

            unset(
                $data["patientPassport"],
                $data["documents"],
                $data["hospitalHistory"],
                $data["diagnosis"],
                $data["operation"],
                $data["patientType"],
                $data["patientName"]
            );
            
            try
            {
                $result = DBQueryFactory::insert('Patients.Patient', [
                    'PatientFullName'=>(is_null($fullName)) ? 'NULL' : QB::wrapString((string)$fullName, "'"),
                    'PatientPicture'=> QB::wrapString(self::$patientFolders["profile"].DIRECTORY_SEPARATOR."photo.img", "'"),
                    'PatientType'=>(is_null($type)) ? 'NULL' : QB::wrapString((string)$type, "'"),
                    'PatientIdentificationDocument'=> QB::wrapString(self::$patientFolders["profile"].DIRECTORY_SEPARATOR."documents.img", "'"),
                    'PatientUUID'=>QB::wrapString((string)$patientUuid, "'")
                ]);

                if ($result){
                    $id = $result['lastInsertId'];

                    $values = [];
                    foreach ($data as $key=>$value){
                        $values[] = "($id, ".QB::wrapString((string)ucfirst($key), "'").", ".QB::wrapString((string)$value, "'").")";
                    }


                    $query = "INSERT INTO Patients.PatientRecordsFieldValue (PatientId, FieldTitle, FieldValue) VALUES ".implode(", ", $values);

                    $queryResult = (
                        DBConnectionFactory::getConnection()
                        ->exec($query)
                    );

                    if ($queryResult){
                        if (!HospitalHistory::new((int)$id, $hospitalHistory) || !Diagnosis::new((int)$id, $diagnosis)){
                            self::delete((int)$id);
                        }
                        else{
                            //upload documents now
                            if(!self::createPatientFolders($patientUuid)){
                                self::delete((int)$id);
                            }
                            else {
                                if (!self::uploadPhotoAndDocuments($passport, $documents)){
                                    self::delete((int)$id);
                                }
                            }
                        }
                    }
                    else {
                        self::delete((int)$id);
                    }

                    DatabaseLog::log(
                        Session::get('USER_ID'),
                        Constant::EVENT_INSERT,
                        'Patients',
                        'PatientRecordsFieldValue',
                        $query
                    );                
                }

                DatabaseLog::log(
                    Session::get('USER_ID'),
                    Constant::EVENT_INSERT,
                    'Patients',
                    'Patient',
                    (string)(serialize($result))
                );
                
                
                return $result;
            }
            catch (\PDOException $e)
            {
                self::delete((int)$id);
                throw new SQLException(sprintf(
                    "Unable to process request (patient not created), %s",
                    $e->getMessage()
                ), Constant::UNDEFINED);
            }
        }

        throw new \Exception("Required data not set");
    }

    /**
     * Modifies the content of a field title type
     */
    public static function edit(int $resourceId, array $data)
    {
        $updateBuilder = (new Builder("QueryBuilder", "Update"))->getBuilder();

        try
        {
            if (isset($data['FullName'])){
                $data['PatientFullName'] = QB::wrapString((string)$data['FullName'], "'");
            }
            if (isset($data['PatientPhoneNumber'])){
                $data['PatientPhoneNumber'] = QB::wrapString((string)$data['PatientPhoneNumber'], "'");
            }

            $updateBuilder->table("Patients.Patient");
            $updateBuilder->set($data);
            $updateBuilder->where("TypeID = $resourceId");

            $result = (
                    DBConnectionFactory::getConnection()
                    ->exec((string)$updateBuilder)
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
     * Modifies the content of a field title type
     */
    public static function editPatientRecordsFieldValue(int $resourceId, array $data)
    {
        $updateBuilder = (new Builder("QueryBuilder", "Update"))->getBuilder();

        try
        {
            if (isset($data['FieldTitle'])){
                $data['FieldTitle'] = QB::wrapString((string)$data['FieldTitle'], "'");
            }
            if (isset($data['FieldValue'])){
                $data['FieldValue'] = QB::wrapString((string)$data['FieldValue'], "'");
            }

            $updateBuilder->table("Patients.PatientRecordsFieldValue");
            $updateBuilder->set($data);
            $updateBuilder->where("TypeID = $resourceId");

            $result = (
                    DBConnectionFactory::getConnection()
                    ->exec((string)$updateBuilder)
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
     * view patients UUID
     */
    public static function view(int $resourceId)
    {
        $selectBuilder = (new Builder('QueryBuilder','Select'))->getBuilder();
        $selectBuilder
            ->columns('*')
            ->from('Patients.Patient');
        if ($resourceId != 0){
            $selectBuilder->where('PatientID ='.$resourceId);
        }
        try
        {
            $viewPatients = (DBConnectionFactory::getConnection()->query((string)$selectBuilder))->fetchAll(\PDO::FETCH_ASSOC);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Patients',
                'Patient',
                (string)serialize($selectBuilder)
            );

            if ($viewPatients){
                $query = "SELECT * FROM Patients.PatientRecordsFieldValue WHERE PatientID = $resourceId";

                $viewPatientsRecords = (
                    DBConnectionFactory::getConnection()
                    ->query($query)
                )->fetchAll(\PDO::FETCH_ASSOC);

                $fields = [];
                foreach ($viewPatientsRecords as $field){
                    $fields[$field["FieldTitle"]] = $field["FieldValue"];
                }


                return array_merge($viewPatients, $fields);
            }

            return $viewPatients;    
        } 
        catch (\PDOException $e) 
        {
            throw new SQLException(
                sprintf(
                    "Error processing request"
                ),
                Constant::UNDEFINED
            );
            
        }
    }
    /**
     * delete patient
     */
    public static function delete(int $resourceId)
    {
        $query = "SELECT PatientUUID FROM Patients.Patient WHERE PatientID = $resourceId";
        $uuid = ((DBConnectionFactory::getConnection()->query($query))->fetchAll())[0]["PatientUUID"];
        $deleteBuilder = (new Builder("QueryBuilder", "delete"))->getBuilder();

        if (is_dir(self::PATIENT_ARCHIVE_DIR.DIRECTORY_SEPARATOR.$uuid)){
            unlink(self::PATIENT_ARCHIVE_DIR.DIRECTORY_SEPARATOR.$uuid);
        }

        try
        {
            $deleteBuilder
                ->from("Patients.Patient")
                ->where("PatientID = $resourceId");
            
            $result = (
                    DBConnectionFactory::getConnection()
                    ->exec((string)$deleteBuilder)
                );

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Patients',
                'Patient',
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
}