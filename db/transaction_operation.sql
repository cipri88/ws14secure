select * from transaction_codes
where tc_code LIKE "%alice%";

select * from accounts
where a_number = 1;

SET @validTransactionsCounter := 0

-- check sufficient sum
 SET @senderAccountId := ( select a_id from accounts join transaction_codes
on accounts.a_id = transaction_codes.tc_account
where a_number = 1
 and a_user = 1
 and a_balance > 100
 and tc_code = "alice0000000003"
 AND tc_account = 1
 AND tc_active = 1
);
select @senderAccountId;

SET @receiverAccountId := (select a_id from accounts where a_number = 2);

SET @sufficientBalance := IF(@senderAccountId = NULL , 0, 1);
select @sufficientBalance;

-- update valid transactions counter
SET @validTransactionsCounter = @validTransactionsCounter + @sufficientBalance;

-- set flag for processed transaction
SET @transactionValid = 0;
-- insert transaction for sender
INSERT INTO transactions (@senderAccountId, t_amount, t_type, t_code, t_description, @receiverAccountId, t_confirmed) 
VALUES (  1 , -100 , 0 , "alice0000000003", "code2", 2 , 1 )
;
 
SET @transactionValid = (select LAST_INSERT_ID());
select @transactionValid;

DELETE FROM transactions
where t_id = @transactionValid
and @sufficientBalance = 0; 

SET @transactionValid = 0;
-- insert transaction for receiver
INSERT INTO transactions (@senderAccountId, t_amount, t_type, t_code, t_description, @receiverAccountId, t_confirmed) 
VALUES (  2 , 100 , 0 , "alice0000000003", "code2", 1 , 1 )
;
 
SET @transactionValid = (select LAST_INSERT_ID());
select @transactionValid;

DELETE FROM transactions
where t_id = @transactionValid
and @sufficientBalance = 0; 

-- decrease sum for sender
UPDATE accounts
set a_balance = a_balance - 100
where a_number = 1
and a_user = 1
and @sufficientBalance <> 0;

-- increase sum for receiver
UPDATE accounts
set a_balance = a_balance + 100
where a_number = 2
and @sufficientBalance <> 0;

-- disable transaction code

UPDATE transaction_codes
SET tc_active = 0 where tc_code = "alice0000000003"
 AND tc_account = @senderAccountId
 AND tc_active = 1
 and @sufficientBalance <> 0
;

SET @codesDisabled = (select ROW_COUNT());
select @codesDisabled;

SET @validTransactionsCounter := @validTransactionsCounter + @codesDisabled;

-- reset variables
SET @transactionValid = -1;
SET @balanceUpdateValid = -1;

select * from transaction_codes
where tc_code LIKE "%alice%";

select * from accounts
where a_number = 1;