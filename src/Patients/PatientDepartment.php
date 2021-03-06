<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Bardeson Lucky <flashup4all@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Patients;

use EmmetBlue\Core\Builder\BuilderFactory as Builder;
use EmmetBlue\Core\Factory\DatabaseConnectionFactory as DBConnectionFactory;
use EmmetBlue\Core\Builder\QueryBuilder\QueryBuilder as QB;
use EmmetBlue\Core\Exception\SQLException;
use EmmetBlue\Core\Exception\UndefinedValueException;
use EmmetBlue\Core\Session\Session;
use EmmetBlue\Core\Logger\DatabaseLog;
use EmmetBlue\Core\Logger\ErrorLog;
use EmmetBlue\Core\Constant;

/**
 * class PatientDepartment.
 *
 * PatientDepartment Controller
 *
 * @author Bardeson Lucky <flashup4all@gmail.com>
 * @since v0.0.1 19/08/2016 14:20
 */
class PatientDepartment
{
	/**
	 * Creates a new Patient department request id
	 *
	 * @param $_POST
	 */
    public static function newPatientDepartment(array $data)
    {
        $result = PatientDepartment\PatientDepartment::create($data);

        return $result;
    }

    /**
     * Selects Patient department request id
     */
    public static function viewPatientDepartment(int $resourceId=0)
    {
        $result = PatientDepartment\PatientDepartment::view($resourceId);

        return $result;
    }

    /**
     * Deletes a Patient department request id
     */
    public static function deletePatientDepartment(int $resourceId)
    {
    	$result = PatientDepartment\PatientDepartment::delete($resourceId);

    	return $result;
    }
}