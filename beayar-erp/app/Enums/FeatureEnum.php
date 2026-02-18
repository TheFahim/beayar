<?php

namespace App\Enums;

enum FeatureEnum: string
{
    // Modules (Yes/No)
    case MODULE_INVENTORY = 'module_inventory';
    case MODULE_ACCOUNTS = 'module_accounts';
    case MODULE_CRM = 'module_crm';
    case MODULE_HRM = 'module_hrm';
    case MODULE_POS = 'module_pos';
    case MODULE_IMPORT_QUOTATION = 'module_import_quotation';

    // Limits (Numeric)
    case LIMIT_USERS = 'limit_users';
    case LIMIT_INVOICES = 'limit_invoices';
    case LIMIT_PRODUCTS = 'limit_products';
    case LIMIT_CUSTOMERS = 'limit_customers';
    case LIMIT_VENDORS = 'limit_vendors';
}
