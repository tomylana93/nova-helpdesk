<?php

namespace App\Enums;

use App\Concerns\HasOptions;

enum UserRole: string
{
    use HasOptions;

    case SuperAdmin = 'super_admin';
    case Admin = 'admin';
    case OperationsManager = 'operations_manager';
    case BranchManager = 'branch_manager';
    case Dispatcher = 'dispatcher';
    case WarehouseSupervisor = 'warehouse_supervisor';
    case FleetCoordinator = 'fleet_coordinator';
    case ProcurementOfficer = 'procurement_officer';
    case SalesExecutive = 'sales_executive';
    case CustomerService = 'customer_service';
    case FinanceOfficer = 'finance_officer';
    case ComplianceOfficer = 'compliance_officer';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => __('user.role.super_admin'),
            self::Admin => __('user.role.admin'),
            self::OperationsManager => __('user.role.operations_manager'),
            self::BranchManager => __('user.role.branch_manager'),
            self::Dispatcher => __('user.role.dispatcher'),
            self::WarehouseSupervisor => __('user.role.warehouse_supervisor'),
            self::FleetCoordinator => __('user.role.fleet_coordinator'),
            self::ProcurementOfficer => __('user.role.procurement_officer'),
            self::SalesExecutive => __('user.role.sales_executive'),
            self::CustomerService => __('user.role.customer_service'),
            self::FinanceOfficer => __('user.role.finance_officer'),
            self::ComplianceOfficer => __('user.role.compliance_officer'),
        };
    }
}
