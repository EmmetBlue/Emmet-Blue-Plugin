<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\AccountsBiller;

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
 * class AccountsBillingType.
 *
 * AccountsBillingType Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class BillingTypeItemsPrices
{
	public static function newBillingTypeItemsPrices(array $data)
	{
		return AccountsBillingTypeItemsPrices\NewAccountsBillingTypeItemsPrices::default($data);
	}

	public static function viewBillingTypeItemsPrices(int $accountsBillingTypeItemPriceId)
	{
		return AccountsBillingTypeItemsPrices\ViewAccountsBillingTypeItemsPrices::viewAccountsBillingTypeItemsPrices($accountsBillingTypeItemPriceId);
	}

	public static function deleteBillingTypeItemsPrices(int $accountsBillingTypeItemPriceId)
	{
		return AccountsBillingTypeItemsPrices\DeleteAccountsBillingTypeItemsPrices::delete($accountsBillingTypeItemPriceId);
	}
}