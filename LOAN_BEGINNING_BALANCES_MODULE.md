# Loan Beginning Balances Module

## Overview
This module allows users to setup beginning balances for existing loans in the system. It is integrated into the Loan Management menu and provides full CRUD (Create, Read, Update, Delete) operations along with posting to the General Ledger.

## Features
- **Create** new loan beginning balances for existing loans
- **Edit** loan beginning balances (before posting)
- **Delete** loan beginning balances (before posting)
- **Post** loan beginning balances to the General Ledger
- Filter by fiscal year
- Track principal, interest, and penalty balances separately
- Automatic total calculation
- Prevent duplicate entries for same fiscal year, member, and loan product
- Prevent editing/deletion after posting to GL

## Database Setup

### Table Creation
Run the table creation script to create the `loan_beginning_balances` table:
```
http://your-domain.com/create_loan_beginning_balances_table.php
```

Or manually execute this SQL:
```sql
CREATE TABLE IF NOT EXISTS `loan_beginning_balances` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fiscal_year_id` int(11) NOT NULL COMMENT 'Reference to fiscal_year table',
  `loan_id` varchar(50) DEFAULT NULL COMMENT 'Loan ID/Number',
  `member_id` varchar(50) NOT NULL COMMENT 'Member ID',
  `loan_product_id` int(11) NOT NULL COMMENT 'Reference to loan_product table',
  `principal_balance` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Beginning principal balance',
  `interest_balance` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Beginning interest balance',
  `penalty_balance` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Beginning penalty balance',
  `total_balance` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Total beginning balance',
  `disbursement_date` date DEFAULT NULL COMMENT 'Original loan disbursement date',
  `description` text COMMENT 'Description/notes for the beginning balance',
  `posted` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1=Posted to General Ledger, 0=Not Posted',
  `posted_date` datetime DEFAULT NULL COMMENT 'Date when posted to General Ledger',
  `posted_by` int(11) DEFAULT NULL COMMENT 'User ID who posted the balance',
  `created_by` int(11) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `PIN` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_pin` (`PIN`),
  KEY `idx_fiscal_year` (`fiscal_year_id`),
  KEY `idx_member` (`member_id`),
  KEY `idx_loan_product` (`loan_product_id`),
  KEY `idx_posted` (`posted`),
  UNIQUE KEY `unique_fiscal_member_product` (`PIN`, `fiscal_year_id`, `member_id`, `loan_product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Beginning balances for Existing Loans by Fiscal Year';
```

## Files Modified/Created

### Models
- **application/models/loan_model.php** - Added loan beginning balance methods:
  - `loan_beginning_balance_list($fiscal_year_id, $id)`
  - `loan_beginning_balance_create($data)`
  - `loan_beginning_balance_update($data, $id)`
  - `loan_beginning_balance_delete($id)`
  - `loan_beginning_balance_post_to_ledger($id)`
  - `check_loan_beginning_balance_exists($fiscal_year_id, $member_id, $loan_product_id)`

### Controllers
- **application/controllers/loan.php** - Added controller methods:
  - `loan_beginning_balance_list()` - Display list of loan beginning balances
  - `loan_beginning_balance_create($id)` - Create or edit form
  - `loan_beginning_balance_delete($id)` - Delete a balance
  - `loan_beginning_balance_post($id)` - Post to General Ledger

### Views
- **application/views/loan/loan_beginning_balance_list.php** - List view with fiscal year filter
- **application/views/loan/loan_beginning_balance_form.php** - Create/edit form

### Language Files
- **application/language/english/loan_lang.php** - Added language strings for loan beginning balances

### Menu
- **application/views/menu.php** - Added menu item under Loan Management

### Database Scripts
- **create_loan_beginning_balances_table.php** - Database table creation script

## Usage

### Accessing the Module
1. Navigate to **Loan Management** menu
2. Click on **Loan Beginning Balances**

### Creating a Beginning Balance
1. Click **Create Loan Beginning Balance** button
2. Select the fiscal year
3. Enter member ID
4. Select loan product
5. Enter balances (principal, interest, penalty)
6. Optionally enter loan ID and disbursement date
7. Add description if needed
8. Click **Create Beginning Balance**

### Posting to General Ledger
1. Select a fiscal year to view balances
2. Click **Post to General Ledger** for the balance you want to post
3. Confirm the action
4. The balance will be posted with entries created for:
   - Principal balance (if > 0)
   - Interest balance (if > 0)
   - Penalty balance (if > 0)

### Editing a Balance
- Only unposted balances can be edited
- Click the **Edit** button
- Make changes and save

### Deleting a Balance
- Only unposted balances can be deleted
- Click the **Delete** button
- Confirm the deletion

## General Ledger Posting Details

When a loan beginning balance is posted to the General Ledger:
- A general ledger entry is created with the fiscal year start date
- Separate ledger entries are created for principal, interest, and penalty (if > 0)
- The accounts used are from the loan product configuration:
  - `loan_principle_account` for principal
  - `loan_interest_account` for interest
  - `loan_penalt_account` for penalty
- Journal ID 8 is used (Beginning Balance journal)
- The balance is marked as posted and cannot be edited or deleted

## Permission Required
Users need the **Loan_beginning_balances** role permission under module 5 (Loan Management) to access this feature.

## Notes
- Each combination of fiscal year, member ID, and loan product can only have one beginning balance record
- At least one balance amount (principal, interest, or penalty) must be greater than zero
- Once posted to General Ledger, balances cannot be edited or deleted
- The total balance is automatically calculated as the sum of principal, interest, and penalty balances
