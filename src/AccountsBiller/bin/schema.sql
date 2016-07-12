CREATE TABLE Accounts.BillingType (
	BillingTypeID INTEGER PRIMARY KEY IDENTITY NOT NULL,
	BillingTypeName VARCHAR (50) UNIQUE,
	BillingTypeDescription VARCHAR (100)
);
GO
CREATE TABLE Accounts.BillingTypeItems (
	BillingTypeItemID INTEGER PRIMARY KEY IDENTITY NOT NULL,
	BillingType INTEGER,
	BillingTypeItemName VARCHAR (100) UNIQUE
	FOREIGN KEY (BillingType) REFERENCES [Accounts].[BillingType] (BillingTypeID) ON UPDATE CASCADE ON DELETE CASCADE
);
GO
CREATE TABLE Accounts.BillingTypeItemsPrices (
	BillingTypeItemsPriceID INTEGER PRIMARY KEY IDENTITY NOT NULL,
	BillingTypeItemID INTEGER,
	BillingTypeItemsPrice MONEY,
	FOREIGN KEY (BillingTypeItemID) REFERENCES [Accounts].[BillingTypeItems] (BillingTypeItemID) ON UPDATE CASCADE ON DELETE CASCADE
);
GO