<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Bardeson Lucky <flashup4all@gmail.com> <Ahead!!>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\AccountsBiller\AccountsBillingTypeItems;

use EmmetBlue\Core\Builder\BuilderFactory as Builder;
use EmmetBlue\Core\Builder\QueryBuilder\DeleteQueryBuilder;
use EmmetBlue\Core\Factory\DatabaseConnectionFactory as DBConnectionFactory;
use EmmetBlue\Core\Factory\DatabaseQueryFactory as DatabaseQueryFactory;
use EmmetBlue\Core\Builder\QueryBuilder\QueryBuilder as QB;
use EmmetBlue\Core\Exception\SQLException;
use EmmetBlue\Core\Exception\UndefinedValueException;
use EmmetBlue\Core\Session\Session;
use EmmetBlue\Core\Logger\DatabaseLog;
use EmmetBlue\Core\Logger\ErrorLog;
use EmmetBlue\Core\Constant;

/**
 * class DeleteAccountBillingTypeItems.
 *
 * DeleteAccountBillingTypeItems Controller
 *
 * @author Bardeson Lucky <flashup4all@gmail.com>
 * @since v0.0.1 15/06/2016 14:20
 */
class DeleteAccountsBillingTypeItems
{
	/**
	 * delete method
	 * @author Bardeson Lucky
	 * @param int $accountBillingTypeId
	 */
	public static function delete(int $accountBillingTypeItemId)
	{
		$deleteQuery = "UPDATE Accounts.BillingTypeItems SET DeleteStatus = 1, BillingTypeItemName = CONCAT(BillingTypeItemName, ' DELETED') WHERE BillingTypeItemID = $accountBillingTypeItemId;";

		try
		{
			$deleteOperation = (
				DBConnectionFactory::getConnection()
				->exec((string)$deleteQuery)
			);

			// DatabaseLog::log(Session::get('USER_ID'), Constant::EVENT_DELETE,'Accounts', 'BillingTypeItems', (string)$deleteOperation);

			return $deleteOperation;

			throw new UndefinedValueException(
				sprintf(
					"A Database error has occurred."
				),
				(int)Session::get('USER_ID')
			);
		}
		catch(\PDOException $e)
		{
			throw new SQLException(
				sprintf(
					"Error Processing Request"
				),
				Constant::UNDEFINED
			);
			
		}
	}
}